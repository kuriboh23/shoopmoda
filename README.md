# Shoop Moda Style

Boutique e-commerce **PHP 8 + MySQL** pour une marque marocaine de mode turque
(femme & enfant). Panier, favoris et recherches récentes en session, commande
finale via **WhatsApp** — sans compte client ni paiement en ligne.

---

## Stack

| Couche | Choix |
|---|---|
| Backend | PHP 8.1+ vanilla, PDO, MVC léger maison |
| Base | MySQL 8 / MariaDB (utf8mb4) |
| Front | HTML5 sémantique, CSS moderne (Flexbox/Grid), JS vanilla |
| Assets | Polices natives, icônes SVG inline, aucun CDN |
| Hébergement cible | Apache mutualisé (InfinityFree, Namecheap, …) |

Aucune dépendance Composer. Aucun framework JS. Aucune étape de build.

---

## Un seul fichier à éditer : `config.php`

Le nom de la boutique, le numéro WhatsApp, la devise, l'accès admin, les textes
de la page d'accueil **et la connexion MySQL** sont dans `config.php`, à la
racine du projet. On l'ouvre, on change le texte entre les guillemets, on
enregistre : c'est tout.

Il n'y a **aucun autre fichier de configuration** :

- pas de `.env` (les hébergeurs masquent les fichiers commençant par un point) ;
- pas d'URL de site à renseigner : les liens sont calculés à partir de la
  requête, donc le site fonctionne à la racine du domaine comme dans un
  sous-dossier, sans rien configurer.

### Comment les liens sont construits

Les pages ont des URLs propres : `/produits`, `/panier`,
`/produit/robe-longue-sultan`. C'est le fichier `.htaccess` livré à côté de
`index.php` qui s'en charge. Il ne contient que des règles de réécriture :
`RewriteEngine`, deux `RewriteCond`, un `RewriteRule`, et le blocage des
dossiers de code. Pas de `Options`, pas de `Require`, pas de `ExpiresByType` :
ce sont ces directives-là qui faisaient répondre 500 chez l'hébergeur.

Les fichiers réels (CSS, images, favicon, uploads) sont toujours liés
directement (`/assets/...`) et ne passent jamais par le routeur.

**Si `.htaccess` manque, rien ne casse.** Plusieurs clients FTP cachent les
fichiers qui commencent par un point, et certains hébergeurs le refusent :
dans ce cas les liens deviennent automatiquement `/?p=/panier`, qui marche
partout sans configuration serveur (voir `pretty_urls()` dans
`app/helpers/functions.php`). Pour forcer ce mode plus simple, mettez
`'pretty_urls' => false` dans `config.php`.

---

## Structure

```
/                        ← à déposer tel quel dans htdocs/ (ou public_html/)
├── index.php            Point d'entrée unique
├── .htaccess            URLs propres (réécriture uniquement, facultatif)
├── config.php           ⚙️  TOUS les réglages du site
├── assets/              css / js / images / fonts
├── uploads/             Images produits envoyées depuis l'admin
├── favicon.svg
├── app/
│   ├── bootstrap.php    Chemins, autoloader, erreurs, en-têtes
│   ├── routes.php       Table des URLs (une ligne par page)
│   ├── core/            Router, View, Database, Session
│   ├── controllers/
│   ├── models/
│   ├── helpers/         functions.php, Helpers, Icons
│   └── views/           layouts, partials, pages, errors
├── storage/             cache + logs
├── database/            shoopmoda.sql (structure + données)
└── tools/               scripts de développement
```

---

## Installation locale (XAMPP)

1. Importer la base :

   ```bash
   mysql -u root -e "CREATE DATABASE shoopmoda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root shoopmoda < database/shoopmoda.sql
   ```

2. Ouvrir `config.php` et vérifier la section **C. DATABASE** : sous XAMPP,
   `db_host` = `127.0.0.1`, `db_user` = `root`, `db_pass` = `''` (vide).

3. Ouvrir `http://localhost/shoopmoda/` (ou `http://localhost/` si le dossier
   est déposé directement dans `htdocs/`). Aucune autre configuration.

Pendant la mise au point, passer `'app_debug' => true` dans `config.php` pour
voir les erreurs à l'écran.

### Serveur intégré (sans Apache)

```bash
php -S 127.0.0.1:8080 tools/dev-router.php
```

Puis ouvrir `http://127.0.0.1:8080/`.

---

## Déploiement (hébergement mutualisé)

Voir [`DEPLOY.md`](DEPLOY.md) : création de la base, import du dump, envoi des
fichiers dans `htdocs/` et réglages dans `config.php`.

---

## Ajouter une page

1. Une ligne dans `app/routes.php` :

   ```php
   $router->get('/contact', [HomeController::class, 'contact']);
   ```

   `{slug}` dans l'URL devient un argument passé à la méthode, dans l'ordre.

2. Une méthode dans le contrôleur qui appelle `View::render('contact', [...])`.

Aucune autre modification : le routeur compare les segments de l'URL, il ne
comprend que la syntaxe `{nom}` (jamais d'expression régulière).

Si la nouvelle page contient un formulaire `method="GET"`, ajoutez
`<?= route_field('/contact') ?>` dans le formulaire : sans ce champ, la
soumission remplacerait la page en cours.

---

## Conventions

- **Sécurité** : PDO préparé, échappement systématique via `e()`, cookies
  `HttpOnly; Secure; SameSite=Lax`, CSRF sur tout POST, en-têtes CSP/HSTS,
  uploads validés (`getimagesize`) et renommés aléatoirement.
- **Prix** : jamais stockés côté client. Le panier ne contient que
  `slug / taille / couleur / quantité` ; les prix sont relus en base.
- **Navigation** : header + menu latéral gauche. Pas de barre basse.
- **Langue** : toute l'interface et les messages WhatsApp sont en français.
  Les commentaires du code sont en anglais.
- **Devise** : DH, formatée par `format_price()`.

---

## Journal

`storage/logs/app-AAAA-MM-JJ.log` (erreurs applicatives) et
`storage/logs/php-AAAA-MM-JJ.log` (avertissements PHP).
