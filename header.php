<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/theme_global.php';

$sdmsGlobalTheme = sdms_get_global_theme($pdo);

$themeCssVars = implode(';', [
    '--theme-primary:' . $sdmsGlobalTheme['primary_color'],
    '--theme-button:' . $sdmsGlobalTheme['button_color'],
    '--theme-header:' . $sdmsGlobalTheme['header_color'],
    '--theme-card:' . $sdmsGlobalTheme['card_color'],
    '--theme-background:' . $sdmsGlobalTheme['background_color'],
    '--theme-text:' . $sdmsGlobalTheme['text_color'],
    '--theme-font:' . $sdmsGlobalTheme['font_color'],
    '--theme-icon:' . $sdmsGlobalTheme['icon_color'],
    '--theme-border:' . $sdmsGlobalTheme['border_color'],
    '--theme-primary-hover:' . $sdmsGlobalTheme['primary_color'],
    '--purple:' . $sdmsGlobalTheme['button_color'],
    '--accent:' . $sdmsGlobalTheme['primary_color'],
    '--panel:' . $sdmsGlobalTheme['card_color'],
    '--navy:' . $sdmsGlobalTheme['background_color'],
    '--text:' . $sdmsGlobalTheme['font_color'],
    '--line:' . $sdmsGlobalTheme['border_color']
]) . ';';

/*
 * Text color for anything drawn on top of the primary color (the active nav pill).
 * White on dark primaries, dark on light primaries, so it stays readable
 * whatever theme is chosen in Theme Settings.
 */
$sdmsPrimaryHex = ltrim((string)$sdmsGlobalTheme['primary_color'], '#');
if (strlen($sdmsPrimaryHex) === 3) {
    $sdmsPrimaryHex = preg_replace('/(.)/', '$1$1', $sdmsPrimaryHex);
}
$sdmsOnPrimary = '#ffffff';
if (preg_match('/^[0-9a-fA-F]{6}$/', $sdmsPrimaryHex)) {
    $sdmsLuma = (
        0.299 * hexdec(substr($sdmsPrimaryHex, 0, 2)) +
        0.587 * hexdec(substr($sdmsPrimaryHex, 2, 2)) +
        0.114 * hexdec(substr($sdmsPrimaryHex, 4, 2))
    ) / 255;
    $sdmsOnPrimary = $sdmsLuma > 0.6 ? '#111827' : '#ffffff';
}
$themeCssVars .= '--theme-on-primary:' . $sdmsOnPrimary . ';';

$userRole = trim((string)($_SESSION['role'] ?? ''));

$isAdmin = strcasecmp($userRole, 'Administrator') === 0;
$isStaff = strcasecmp($userRole, 'Staff') === 0;
$isTeacher = strcasecmp($userRole, 'Teacher') === 0;

/*
 * Navigation, grouped by category.
 * - A top-level item with 'href' is a single pill link.
 * - A top-level item with 'children' becomes a dropdown pill.
 * Each child is [label, href, icon, visible?]. Hidden items are skipped,
 * and a category with no visible items is not shown at all.
 */
$canUseBoards = $isAdmin || $isStaff || $isTeacher;

$navItems = [
    [
        'label' => 'Dashboard',
        'icon'  => 'bi-speedometer2',
        'href'  => 'index.php',
        'show'  => true,
    ],
    [
        'label'    => 'People',
        'icon'     => 'bi-people',
        'show'     => true,
        'children' => [
            ['Students', 'students.php', 'bi-mortarboard',    true],
            ['Teachers', 'teachers.php', 'bi-person-workspace', $isAdmin || $isStaff],
            ['Staff',    'staff.php',    'bi-person-badge',   $isAdmin],
        ],
    ],
    [
        'label'    => 'Boards',
        'icon'     => 'bi-pin-angle',
        'show'     => $canUseBoards,
        'children' => [
            ['Information',    'information.php',     'bi-info-circle',    true],
            ['Transparency',   'transparency.php',    'bi-megaphone',      true],
            ['Bulletin Board', 'bulletin_board.php',  'bi-pin-angle',      true],
            ['Calendar',       'calendar.php',        'bi-calendar-event', true],
        ],
    ],
    [
        'label' => 'Theme Settings',
        'icon'  => 'bi-palette',
        'href'  => 'theme_editor.php',
        'show'  => $isAdmin,
    ],
];

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($pageTitle ?? 'School Data Management System') ?></title>

    <link rel="icon" type="image/png" href="assets/logo/logo2.png">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <!-- Shared styles -->
    <link href="assets/style.css" rel="stylesheet">
    <link href="assets/navigation.css" rel="stylesheet">
    <link href="assets/css/theme.css" rel="stylesheet">

    <!-- Floating pill navigation + minimal footer (loads after theme.css) -->
    <link href="assets/nav-pill.css" rel="stylesheet">

    <!-- Page-specific styles -->
    <?php if (!empty($pageStylesheets) && is_array($pageStylesheets)): ?>
        <?php foreach ($pageStylesheets as $stylesheet): ?>
            <link
                href="<?= e($stylesheet) ?>"
                rel="stylesheet"
            >
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Dynamic global theme values -->
    <style>
        :root {
            <?= e($themeCssVars) ?>
        }
    </style>
</head>

<body class="bg-light">

<?php if (!empty($_SESSION['user_id'])): ?>

<div class="sdms-nav-wrap">
    <nav class="navbar navbar-expand-lg sdms-nav" aria-label="Main">

        <a class="navbar-brand sdms-brand" href="index.php">
            <i class="bi bi-building me-1"></i>
            School Data Management
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNav"
            aria-controls="mainNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">

            <ul class="navbar-nav mx-lg-auto mb-2 mb-lg-0 gap-lg-1">

                <?php foreach ($navItems as $item): ?>
                    <?php if (empty($item['show'])) { continue; } ?>

                    <?php if (isset($item['href'])): ?>

                        <li class="nav-item">
                            <a
                                class="nav-link<?= $currentPage === $item['href'] ? ' active' : '' ?>"
                                href="<?= e($item['href']) ?>"
                                <?= $currentPage === $item['href'] ? 'aria-current="page"' : '' ?>
                            >
                                <i class="bi <?= e($item['icon']) ?> me-1"></i>
                                <?= e($item['label']) ?>
                            </a>
                        </li>

                    <?php else: ?>
                        <?php
                        $visibleChildren = array_filter($item['children'], fn($c) => !empty($c[3]));
                        if (!$visibleChildren) { continue; }
                        $groupActive = in_array($currentPage, array_column($visibleChildren, 1), true);
                        ?>

                        <li class="nav-item dropdown">
                            <a
                                class="nav-link dropdown-toggle<?= $groupActive ? ' active' : '' ?>"
                                href="#"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <i class="bi <?= e($item['icon']) ?> me-1"></i>
                                <?= e($item['label']) ?>
                            </a>

                            <ul class="dropdown-menu sdms-dropdown">
                                <?php foreach ($visibleChildren as $child): ?>
                                    <li>
                                        <a
                                            class="dropdown-item<?= $currentPage === $child[1] ? ' active' : '' ?>"
                                            href="<?= e($child[1]) ?>"
                                        >
                                            <i class="bi <?= e($child[2]) ?> me-2"></i>
                                            <?= e($child[0]) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>

                    <?php endif; ?>
                <?php endforeach; ?>

            </ul>

            <div class="d-flex align-items-center gap-2 flex-wrap">

                <a href="account.php" class="sdms-user">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= e($_SESSION['full_name'] ?? 'User') ?>
                </a>

                <a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="logout.php">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Logout
                </a>

            </div>

        </div>
    </nav>
</div>

<?php endif; ?>

<main class="container-fluid px-4 py-4">