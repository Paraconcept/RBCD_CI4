<?php
/*
 * Quilles dessinées en SVG (fond transparent).
 * $variant = 'icon'  : 3 quilles d'une seule couleur (currentColor), à la taille d'une icône
 * $variant = 'color' : les 5 quilles du jeu, 4 blanches et la rouge au centre (écran TV)
 * $uid : préfixe des identifiants SVG, unique dans la page
 */
$variant ??= 'icon';
$uid     ??= 'sk';
$class   ??= '';
$pin = 'M20 2C27 2 29 9 28 16C27.5 20 25.5 23 25 26C26 31 34 40 35 58C36 76 30 92 27 98L13 98C10 92 4 76 5 58C6 40 14 31 15 26C14.5 23 12.5 20 12 16C11 9 13 2 20 2Z';
// [centre x, échelle, couleur] : les quilles du fond d'abord, la plus grande devant
$pins = $variant === 'color'
    ? [[30, .78, 'w'], [170, .78, 'w'], [63, .9, 'w'], [137, .9, 'w'], [100, 1, 'r']]
    : [[28, .8, 'c'], [72, .8, 'c'], [50, .95, 'c']];
$viewBox = $variant === 'color' ? '0 0 200 110' : '0 0 100 104';
$floor   = $variant === 'color' ? 106 : 100;
$fills   = ['w' => "url(#{$uid}-white)", 'r' => "url(#{$uid}-red)", 'c' => 'currentColor'];
$bands   = ['w' => '#b8343c', 'r' => '#fff', 'c' => 'var(--sb-panel, #16213d)'];
$stroke  = $variant === 'color' ? 'rgba(0,0,0,.55)' : 'var(--sb-panel, #16213d)';
?>
<svg class="<?= esc($class) ?>" viewBox="<?= $viewBox ?>" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="5 quilles" preserveAspectRatio="xMidYMid meet">
    <defs>
        <clipPath id="<?= $uid ?>-clip"><path d="<?= $pin ?>"/></clipPath>
        <?php if ($variant === 'color'): ?>
        <linearGradient id="<?= $uid ?>-white" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#ffffff"/><stop offset=".55" stop-color="#eef1f6"/><stop offset="1" stop-color="#aeb7c6"/>
        </linearGradient>
        <linearGradient id="<?= $uid ?>-red" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#f87171"/><stop offset=".5" stop-color="#dc2626"/><stop offset="1" stop-color="#7f1d1d"/>
        </linearGradient>
        <?php endif; ?>
    </defs>
    <?php foreach ($pins as [$cx, $s, $c]): ?>
    <g transform="translate(<?= round($cx - 20 * $s, 2) ?> <?= round($floor - 98 * $s, 2) ?>) scale(<?= $s ?>)">
        <path d="<?= $pin ?>" fill="<?= $fills[$c] ?>" stroke="<?= $stroke ?>" stroke-width="<?= $variant === 'color' ? 1.5 : 2.5 ?>" stroke-linejoin="round" paint-order="stroke"/>
        <g clip-path="url(#<?= $uid ?>-clip)" fill="<?= $bands[$c] ?>">
            <rect x="0" y="28" width="40" height="3.2"/>
            <rect x="0" y="34" width="40" height="3.2"/>
        </g>
        <?php if ($variant === 'color'): ?>
        <path d="M12.5 10C12.8 6.5 15 5 17 5" fill="none" stroke="#fff" stroke-opacity=".8" stroke-width="2" stroke-linecap="round"/>
        <path d="M11 52C10.5 62 11.5 74 14 84" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="2.4" stroke-linecap="round"/>
        <?php endif; ?>
    </g>
    <?php endforeach; ?>
</svg>
