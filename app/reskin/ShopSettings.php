<?php

declare(strict_types=1);

namespace App\Reskin;

use App\Models\Setting;
use App\Models\SiteContent;

/**
 * Store identity, owner-only.
 *
 * This is intentionally inside the RESKIN module: the client receives the
 * catalogue admin (products/categories), while branding, WhatsApp, delivery,
 * promo end date and social links stay behind the RESKIN password.
 */
final class ShopSettings
{
    /**
     * @return array<string, string>
     */
    public static function values(): array
    {
        return [
            'store_name'         => (string) (Setting::get('store_name') ?? config('app_name')),
            'whatsapp_number'    => (string) (Setting::get('whatsapp_number') ?? config('whatsapp_number')),
            'delivery_fee'       => (string) (Setting::get('delivery_fee') ?? Setting::get('delivery_fee_economy') ?? '25'),
            'announcement_text'  => SiteContent::firstAnnouncement(),
            'flash_sale_end'     => (string) (Setting::get('flash_sale_end') ?? ''),
            'instagram_url'      => (string) (Setting::raw('instagram_url', config('instagram_url') ?? '')),
            'footer_description' => (string) (SiteContent::section('footer')['description'] ?? ''),
            'logo'               => (string) (Setting::get('site_logo') ?? ''),
        ];
    }

    /**
     * Validate and persist the form. Returns the values to redisplay.
     *
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     * @return array{values: array<string, string>, errors: array<string, string>, saved: bool}
     */
    public static function save(array $post, array $files): array
    {
        $values = [
            'store_name'         => mb_substr(trim((string) ($post['store_name'] ?? '')), 0, 60),
            'whatsapp_number'    => preg_replace('/[^0-9]/', '', (string) ($post['whatsapp_number'] ?? '')) ?? '',
            'delivery_fee'       => trim((string) ($post['delivery_fee'] ?? '0')),
            'announcement_text'  => mb_substr(trim((string) ($post['announcement_text'] ?? '')), 0, 140),
            'flash_sale_end'     => trim((string) ($post['flash_sale_end'] ?? '')),
            'instagram_url'      => trim((string) ($post['instagram_url'] ?? '')),
            'footer_description' => mb_substr(trim((string) ($post['footer_description'] ?? '')), 0, 300),
            'logo'               => (string) setting('site_logo', ''),
        ];

        $errors = [];

        if (mb_strlen($values['store_name']) < 2) {
            $errors['store_name'] = 'Nom de boutique trop court.';
        }

        if (strlen($values['whatsapp_number']) < 8 || strlen($values['whatsapp_number']) > 15) {
            $errors['whatsapp_number'] = 'Numéro WhatsApp invalide (indicatif inclus, sans +).';
        }

        if (!is_numeric($values['delivery_fee']) || (float) $values['delivery_fee'] < 0) {
            $errors['delivery_fee'] = 'Frais de livraison invalides.';
        }

        if ($values['flash_sale_end'] !== '' && strtotime($values['flash_sale_end']) === false) {
            $errors['flash_sale_end'] = 'Date de fin invalide.';
        }

        if ($values['instagram_url'] !== '' && (
            filter_var($values['instagram_url'], FILTER_VALIDATE_URL) === false
            || preg_match('#^https?://#i', $values['instagram_url']) !== 1
        )) {
            $errors['instagram_url'] = 'Adresse Instagram invalide (elle doit commencer par https://).';
        }

        [$logoPath, $logoError] = self::uploadLogo($files['logo'] ?? null);

        if ($logoError !== null) {
            $errors['logo'] = $logoError;
        }

        if ($logoPath !== null) {
            $values['logo'] = $logoPath;
        } elseif (isset($post['remove_logo'])) {
            $values['logo'] = '';
        }

        if ($errors !== []) {
            if ($logoPath !== null) {
                self::deleteLogo($logoPath);
            }

            $values['logo'] = (string) setting('site_logo', '');

            return ['values' => $values, 'errors' => $errors, 'saved' => false];
        }

        $oldLogo = (string) setting('site_logo', '');

        Setting::put('store_name', $values['store_name']);
        Setting::put('whatsapp_number', $values['whatsapp_number']);
        Setting::put('delivery_fee', number_format((float) $values['delivery_fee'], 2, '.', ''));
        Setting::put('announcement_text', $values['announcement_text']);
        Setting::put('instagram_url', $values['instagram_url']);
        Setting::put('site_logo', $values['logo']);
        Setting::put(
            'flash_sale_end',
            $values['flash_sale_end'] !== '' ? date('Y-m-d H:i:s', (int) strtotime($values['flash_sale_end'])) : ''
        );

        SiteContent::applyFlat([
            'announcement.messages' => implode("\n", array_slice(
                SiteContent::section('announcement')['messages'] ?? [],
                1
            )),
            'footer.description'    => $values['footer_description'],
        ]);

        SiteContent::setFirstAnnouncement($values['announcement_text']);

        if ($oldLogo !== '' && $oldLogo !== $values['logo']) {
            self::deleteLogo($oldLogo);
        }

        return ['values' => $values, 'errors' => [], 'saved' => true];
    }

    /**
     * @param array<string, mixed>|null $file
     * @return array{0: string|null, 1: string|null}
     */
    private static function uploadLogo(?array $file): array
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [null, 'Le téléversement du logo a échoué, réessayez.'];
        }

        if ((int) ($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return [null, 'Logo trop lourd (2 Mo maximum).'];
        }

        $info = @getimagesize((string) ($file['tmp_name'] ?? ''));

        $extensions = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_WEBP => 'webp',
        ];

        if ($info === false || !isset($extensions[$info[2]])) {
            return [null, 'Format accepté : JPG, PNG ou WebP.'];
        }

        $directory = PUBLIC_PATH . '/uploads/brand';

        if (!is_dir($directory) && !@mkdir($directory, 0775, true)) {
            return [null, 'Dossier d’images inaccessible.'];
        }

        $filename = 'logo-' . bin2hex(random_bytes(8)) . '.' . $extensions[$info[2]];

        if (!@move_uploaded_file((string) $file['tmp_name'], $directory . '/' . $filename)) {
            return [null, 'Enregistrement du logo impossible.'];
        }

        return ['uploads/brand/' . $filename, null];
    }

    public static function deleteLogo(string $path): void
    {
        if (!str_starts_with($path, 'uploads/brand/')) {
            return;
        }

        $file = PUBLIC_PATH . '/' . $path;

        if (is_file($file)) {
            @unlink($file);
        }
    }
}
