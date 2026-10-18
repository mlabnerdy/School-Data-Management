<?php
require_once __DIR__ . '/config.php';
require_login();

$userRole = trim((string)($_SESSION['role'] ?? ''));
if (strcasecmp($userRole, 'Administrator') !== 0) {
    http_response_code(403);
    exit('Access denied. Only Administrators can change the system-wide theme.');
}

require_once __DIR__ . '/theme_global.php';
$pageTitle = 'System Theme Customizer';

$defaults = [
    'primary_color' => '#0d6efd',
    'button_color' => '#0d6efd',
    'header_color' => '#ffffff',
    'card_color' => '#ffffff',
    'background_color' => '#f5f8fc',
    'text_color' => '#172b4d',
    'font_color' => '#172b4d',
    'icon_color' => '#0d6efd',
    'border_color' => '#e5eaf0',
];

$theme = sdms_get_global_theme($pdo);

if (empty($_SESSION['theme_csrf'])) {
    $_SESSION['theme_csrf'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals((string)$_SESSION['theme_csrf'], $token)) {
        $error = 'Your session validation failed. Refresh the page and try again.';
    } else {
        $action = (string)($_POST['action'] ?? 'save');
        if ($action === 'reset') {
            $theme = $defaults;
        } elseif ($action === 'save') {
            foreach ($defaults as $key => $default) {
                $value = (string)($_POST[$key] ?? $default);
                if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
                    $error = 'One or more colors are invalid. Please choose valid colors.';
                    break;
                }
                $theme[$key] = strtolower($value);
            }
        } else {
            $error = 'Invalid action.';
        }

        if ($error === '') {
            try {
                $sql = 'INSERT INTO system_theme_settings 
                        (id, primary_color, button_color, header_color, card_color, background_color, text_color, font_color, icon_color, border_color, updated_by) 
                        VALUES (1, :primary_color, :button_color, :header_color, :card_color, :background_color, :text_color, :font_color, :icon_color, :border_color, :updated_by) 
                        ON DUPLICATE KEY UPDATE 
                        primary_color = VALUES(primary_color), 
                        button_color = VALUES(button_color), 
                        header_color = VALUES(header_color), 
                        card_color = VALUES(card_color), 
                        background_color = VALUES(background_color), 
                        text_color = VALUES(text_color), 
                        font_color = VALUES(font_color), 
                        icon_color = VALUES(icon_color), 
                        border_color = VALUES(border_color), 
                        updated_by = VALUES(updated_by)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($theme + ['updated_by' => (int)($_SESSION['user_id'] ?? 0)]);
                $message = $action === 'reset' ? 'Original system theme restored for all users.' : 'Theme saved and applied system-wide.';
            } catch (PDOException $e) {
                $error = 'Could not save the theme. Make sure theme_settings.sql has been imported.';
            }
        }
    }
}

// Refresh settings state for output rendering
$theme = sdms_get_global_theme($pdo);
$themeVars = implode(';', [
    '--theme-primary:' . $theme['primary_color'],
    '--theme-button:' . $theme['button_color'],
    '--theme-header:' . $theme['header_color'],
    '--theme-card:' . $theme['card_color'],
    '--theme-background:' . $theme['background_color'],
    '--theme-text:' . $theme['text_color'],
    '--theme-font:' . $theme['font_color'],
    '--theme-icon:' . $theme['icon_color'],
    '--theme-border:' . $theme['border_color']
]) . ';';

require_once __DIR__ . '/header.php';
?>
<link rel="stylesheet" href="assets/css/theme_editor.css">

<section class="sdms-theme-editor" style="<?= e($themeVars) ?>">
    <div class="sdms-theme-heading">
        <div>
            <p class="sdms-theme-eyebrow"><i class="bi bi-palette me-1"></i> Administrator settings</p>
            <h1>System Theme Customizer</h1>
            <p>Changes are saved to the database and apply to all users and devices.</p>
        </div>
        <form method="post" onsubmit="return confirm('Restore the original blue-and-white theme for everyone?');">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['theme_csrf']) ?>">
            <input type="hidden" name="action" value="reset">
            <button class="sdms-theme-reset" type="submit"><i class="bi bi-arrow-counterclockwise me-1"></i> Restore Original</button>
        </form>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success" role="status" data-theme-result="success"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert" data-theme-result="error"><?= e($error) ?></div>
    <?php endif; ?>

    <div id="sdmsThemeClientStatus" class="sdms-theme-client-status" role="status" aria-live="polite" hidden></div>

    <form method="post" id="sdmsThemeForm">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['theme_csrf']) ?>">
        <input type="hidden" name="action" value="save">

        <section class="sdms-theme-panel">
            <div class="sdms-theme-section-heading">
                <div>
                    <h2>Recommended color schemes</h2>
                    <p>Select a template, then save to apply it system-wide.</p>
                </div>
            </div>
            <div class="sdms-theme-presets">
                <button type="button" class="sdms-theme-preset" data-preset="default">
                    <span><i style="background:#0d6efd"></i><i style="background:#ffffff"></i><i style="background:#f5f8fc"></i></span>
                    <strong>System Blue</strong><small>Original school theme</small>
                </button>
                <button type="button" class="sdms-theme-preset" data-preset="ocean">
                    <span><i style="background:#087e8b"></i><i style="background:#ffffff"></i><i style="background:#edfafa"></i></span>
                    <strong>Ocean Teal</strong><small>Calm and professional</small>
                </button>
                <button type="button" class="sdms-theme-preset" data-preset="forest">
                    <span><i style="background:#28734b"></i><i style="background:#ffffff"></i><i style="background:#f0f7f1"></i></span>
                    <strong>Forest Green</strong><small>Fresh and balanced</small>
                </button>
                <button type="button" class="sdms-theme-preset" data-preset="royal">
                    <span><i style="background:#6246a5"></i><i style="background:#ffffff"></i><i style="background:#f5f2fc"></i></span>
                    <strong>Royal Violet</strong><small>Distinct and elegant</small>
                </button>
                <button type="button" class="sdms-theme-preset" data-preset="warm">
                    <span><i style="background:#b45309"></i><i style="background:#ffffff"></i><i style="background:#fff8ed"></i></span>
                    <strong>Warm Sand</strong><small>Warm neutrals</small>
                </button>
                <button type="button" class="sdms-theme-preset" data-preset="dark">
                    <span><i style="background:#60a5fa"></i><i style="background:#1e293b"></i><i style="background:#0f172a"></i></span>
                    <strong>Midnight</strong><small>Dark interface</small>
                </button>
            </div>
        </section>

        <div class="sdms-theme-editor-grid">
            <section class="sdms-theme-panel">
                <div class="sdms-theme-section-heading">
                    <div>
                        <h2>Interface colors</h2>
                        <p>Customize each interface element.</p>
                    </div>
                </div>
                <?php 
                $fields = [
                    'primary_color' => ['Primary color', 'Links, icons, and highlights'],
                    'button_color' => ['Buttons', 'Primary action buttons'],
                    'header_color' => ['Header / navigation', 'Top navigation background'],
                    'card_color' => ['Cards and panels', 'Forms and content cards'],
                    'text_color' => ['Heading color', 'Headings and important text'],
                    'font_color' => ['Font color', 'Body text, labels, and table text'],
                    'icon_color' => ['Icon color', 'Dashboard icons, LRN badges, initials, and record icons'],
                    'border_color' => ['Borders', 'Outlines and dividers'],
                ]; 
                foreach ($fields as $key => [$label, $description]): 
                ?>
                    <label class="sdms-theme-color-field" for="<?= e($key) ?>">
                        <span><strong><?= e($label) ?></strong><small><?= e($description) ?></small></span>
                        <input type="color" id="<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e($theme[$key]) ?>">
                    </label>
                <?php endforeach; ?>
            </section>

            <section class="sdms-theme-panel">
                <div class="sdms-theme-section-heading">
                    <div>
                        <h2>Background editor</h2>
                        <p>Set the main page background separately.</p>
                    </div>
                </div>
                <label class="sdms-theme-color-field" for="background_color">
                    <span><strong>Page background</strong><small>Background behind content panels</small></span>
                    <input type="color" id="background_color" name="background_color" value="<?= e($theme['background_color']) ?>">
                </label>
                <h3 class="sdms-theme-subheading">Quick backgrounds</h3>
                <div class="sdms-theme-backgrounds">
                    <button type="button" data-background="#f5f8fc"><i style="background:#f5f8fc"></i> Soft blue</button>
                    <button type="button" data-background="#f8fafc"><i style="background:#f8fafc"></i> Cool white</button>
                    <button type="button" data-background="#edfafa"><i style="background:#edfafa"></i> Pale teal</button>
                    <button type="button" data-background="#f0f7f1"><i style="background:#f0f7f1"></i> Soft green</button>
                    <button type="button" data-background="#fff8ed"><i style="background:#fff8ed"></i> Warm cream</button>
                    <button type="button" data-background="#0f172a"><i style="background:#0f172a"></i> Midnight</button>
                </div>
                <div class="sdms-theme-live-preview" id="sdmsThemePreview">
                    <span>LIVE PREVIEW</span>
                    <h3>School announcements</h3>
                    <p>Preview the colors before saving them for everyone.</p>
                    <button type="button">Sample button</button>
                </div>
            </section>
        </div>

        <div class="sdms-theme-save-row">
            <p><i class="bi bi-globe2 me-1"></i> This is a global setting stored in the school database. All users and devices receive the saved theme.</p>
            <button type="submit" class="sdms-theme-save"><i class="bi bi-check2 me-1"></i> Apply and Save Theme</button>
        </div>
    </form>
</section>

<script src="assets/js/theme_editor.js"></script>

<?php require_once __DIR__ . '/footer.php'; ?>