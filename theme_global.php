<?php
/** Global theme loader. Include from header.php after config.php. */
if (!function_exists('sdms_get_global_theme')) {
    function sdms_get_global_theme(PDO $pdo): array
    {
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

        try {
            $stmt = $pdo->query('SELECT primary_color, button_color, header_color, card_color, background_color, text_color, font_color, icon_color, border_color FROM system_theme_settings WHERE id = 1 LIMIT 1');
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return $defaults;
            }
            foreach ($defaults as $key => $default) {
                if (!isset($row[$key]) || !preg_match('/^#[0-9a-fA-F]{6}$/', (string)$row[$key])) {
                    $row[$key] = $default;
                }
            }
            return array_intersect_key($row, $defaults) + $defaults;
        } catch (Throwable $e) {
            // Fallback gracefully if database or table is missing
            return $defaults;
        }
    }
}

if (!isset($sdmsGlobalTheme) && isset($pdo) && $pdo instanceof PDO) {
    $sdmsGlobalTheme = sdms_get_global_theme($pdo);
}