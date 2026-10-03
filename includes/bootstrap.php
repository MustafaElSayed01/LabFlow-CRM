<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

require_once __DIR__ . '/functions.php';

$requestedLanguage = request_string($_GET, 'lang');
$language = $requestedLanguage !== '' ? $requestedLanguage : ($_SESSION['language'] ?? 'ar');
if (in_array($language, ['ar', 'en'], true)) {
    $_SESSION['language'] = $language;
} else {
    $language = 'ar';
}
$language = $_SESSION['language'] ?? 'ar';
$isRtl = $language === 'ar';
$siteSettings = site_settings();
$theme = theme_settings();