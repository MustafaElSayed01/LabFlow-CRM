<?php
$pageTitle = $pageTitle ?? $siteSettings['lab_name'];
$logoPath = (string) $siteSettings['logo_path'];
$faviconPath = (string) $siteSettings['favicon_path'];
$brandLogo = preg_match('~^https?://~i', $logoPath) || preg_match('~^/(?!/)~', $logoPath) ? $logoPath : asset($logoPath);
$brandFavicon = preg_match('~^https?://~i', $faviconPath) || preg_match('~^/(?!/)~', $faviconPath) ? $faviconPath : asset($faviconPath);
$languageParams = ['lang' => $language === 'ar' ? 'en' : 'ar', 'page' => request_string($_GET, 'page') ?: 'home'];
foreach (['id', 'campaign'] as $preservedParameter) {
    $parameterValue = request_string($_GET, $preservedParameter);
    if ($parameterValue !== '' && ctype_digit($parameterValue)) {
        $languageParams[$preservedParameter] = (int) $parameterValue;
    }
}
?>
<!doctype html>
<html lang="<?= e($language) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="<?= e($theme['primary_light']) ?>">
    <title><?= e($pageTitle) ?> | <?= e($siteSettings['lab_name']) ?></title>
    <link rel="icon" href="<?= e($brandFavicon) ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap' . ($isRtl ? '.rtl' : '') . '.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/main.css') ?>">
    <style>
    :root {
        <?php foreach ($theme as $key=> $value): ?>--<?=e(str_replace('_', '-', $key)) ?>:<?=e($value) ?>;
        <?php endforeach;
        ?>
    }
    </style>
</head>

<body>
    <a class="skip-link" href="#main-content"><?= e($language === 'ar' ? 'انتقل إلى المحتوى' : 'Skip to content') ?></a>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg" aria-label="<?= e(t('menu')) ?>">
            <div class="container">
                <a class="navbar-brand brand-lockup" href="<?= e(site_url('index.php')) ?>"
                    aria-label="<?= e($siteSettings['lab_name']) ?>">
                    <img src="<?= e($brandLogo) ?>" alt="<?= e($siteSettings['lab_name']) ?>" class="brand-logo">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNavigation"
                    aria-controls="siteNavigation" aria-expanded="false" aria-label="<?= e(t('menu')) ?>">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="siteNavigation">
                    <ul class="navbar-nav mx-auto gap-lg-2">
                        <li class="nav-item"><a class="nav-link"
                                href="<?= e(site_url('index.php')) ?>"><?= e(t('home')) ?></a></li>
                        <li class="nav-item"><a class="nav-link"
                                href="<?= e(site_url('index.php?page=campaigns')) ?>"><?= e(t('campaigns')) ?></a></li>
                        <li class="nav-item"><a class="nav-link"
                                href="<?= e(site_url('index.php?page=branches')) ?>"><?= e(t('branches')) ?></a></li>
                    </ul>
                    <div class="d-flex align-items-center gap-2 nav-actions">
                        <a class="btn btn-primary btn-sm px-3" href="<?= e(site_url('index.php?page=booking')) ?>"><i
                                class="fa-solid fa-calendar-check me-1" aria-hidden="true"></i><?= e(t('book')) ?></a>
                        <button class="btn btn-icon" type="button" data-theme-toggle aria-label="<?= e(t('theme')) ?>"
                            title="<?= e(t('theme')) ?>"><i class="fa-solid fa-moon" aria-hidden="true"></i></button>
                        <a class="btn btn-icon language-switch"
                            href="<?= e(site_url('index.php?' . http_build_query($languageParams))) ?>"
                            lang="<?= $language === 'ar' ? 'en' : 'ar' ?>"
                            aria-label="<?= e(t('language')) ?>"><?= e(t('language')) ?></a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main id="main-content">