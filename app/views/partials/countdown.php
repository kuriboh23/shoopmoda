<?php

declare(strict_types=1);

/**
 * Countdown timer with days, hours, minutes and seconds.
 *
 * The values are rendered server-side as placeholders so the block keeps
 * its shapes before JavaScript runs; app.js fills them from `data-end`.
 *
 * @var string $endAt  MySQL DATETIME
 * @var string $label  Short sentence shown above the units
 */
?>
<div class="countdown-card" role="timer">
    <span class="countdown-label"><?= e($label ?? 'L’offre se termine dans') ?></span>
    <span class="countdown-timer" id="countdownTimer" data-end="<?= e($endAt) ?>">
        <span class="count-unit">
            <span class="count-value" data-count="days">--</span>
            <span class="count-label">Jours</span>
        </span>
        <span class="count-sep" aria-hidden="true">:</span>
        <span class="count-unit">
            <span class="count-value" data-count="hours">--</span>
            <span class="count-label">Heures</span>
        </span>
        <span class="count-sep" aria-hidden="true">:</span>
        <span class="count-unit">
            <span class="count-value" data-count="minutes">--</span>
            <span class="count-label">Min</span>
        </span>
        <span class="count-sep" aria-hidden="true">:</span>
        <span class="count-unit">
            <span class="count-value" data-count="seconds">--</span>
            <span class="count-label">Sec</span>
        </span>
    </span>
</div>
