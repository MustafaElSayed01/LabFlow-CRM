<?php
$banners = [];
if ($databaseAvailable) {
    try {
        $banners = db()->query("SELECT title, subtitle, image_path, button_text, button_link, link_target FROM banners WHERE is_active = 1 AND (starts_at IS NULL OR starts_at <= NOW()) AND (ends_at IS NULL OR ends_at >= NOW()) ORDER BY sort_order ASC, id DESC")->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable) {
        $banners = [];
    }
}
?>
<section class="hero-section">
    <div class="hero-orb hero-orb-one" aria-hidden="true"></div><div class="hero-orb hero-orb-two" aria-hidden="true"></div>
    <div class="container hero-content">
        <?php if ($banners): ?>
            <div id="heroBanners" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($banners as $index => $banner): ?>
                        <?php $bannerImage = preg_match('~^https?://~i', $banner['image_path']) || preg_match('~^/(?!/)~', $banner['image_path']) ? $banner['image_path'] : asset($banner['image_path']); ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <div class="banner-slide">
                                <img class="banner-image" src="<?= e($bannerImage) ?>" alt="" aria-hidden="true">
                                <div class="hero-copy"><span class="eyebrow"><?= e(t('hero_kicker')) ?></span><h1><?= e($banner['title'] ?: t('hero_title')) ?></h1><p><?= e($banner['subtitle'] ?: t('hero_text')) ?></p>
                                    <?php if (!empty($banner['button_link'])): ?><a class="btn btn-primary btn-lg" href="<?= e(safe_public_link($banner['button_link'])) ?>" <?= $banner['link_target'] === '_blank' ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($banner['button_text'] ?: t('explore_campaigns')) ?><i class="fa-solid fa-arrow-left ms-2" aria-hidden="true"></i></a><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($banners) > 1): ?><button class="carousel-control-prev" type="button" data-bs-target="#heroBanners" data-bs-slide="prev" aria-label="Previous"><span class="carousel-control-prev-icon" aria-hidden="true"></span></button><button class="carousel-control-next" type="button" data-bs-target="#heroBanners" data-bs-slide="next" aria-label="Next"><span class="carousel-control-next-icon" aria-hidden="true"></span></button><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row align-items-center g-5">
                <div class="col-lg-7 hero-copy">
                    <span class="eyebrow"><span class="eyebrow-dot"></span><?= e(t('hero_kicker')) ?></span>
                    <h1><?= e(t('hero_title')) ?></h1>
                    <p><?= e(t('hero_text')) ?></p>
                    <div class="d-flex flex-wrap gap-3 mt-4"><a class="btn btn-primary btn-lg" href="<?= e(site_url('index.php?page=campaigns')) ?>"><?= e(t('explore_campaigns')) ?><i class="fa-solid fa-arrow-left ms-2" aria-hidden="true"></i></a><a class="btn btn-outline-primary btn-lg" href="<?= e(site_url('index.php?page=booking')) ?>"><?= e(t('book')) ?></a></div>
                    <div class="hero-assurance mt-4"><span class="assurance-icon"><i class="fa-solid fa-heart-pulse" aria-hidden="true"></i></span><span><?= e(t('site_tagline')) ?></span></div>
                </div>
                <div class="col-lg-5"><div class="hero-visual"><div class="hero-visual-ring"></div><div class="hero-visual-card hero-card-main"><span class="visual-icon"><i class="fa-solid fa-flask-vial" aria-hidden="true"></i></span><strong><?= e($siteSettings['lab_name']) ?></strong><span><?= e(t('site_tagline')) ?></span></div><div class="hero-visual-card hero-card-float"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span><?= e(t('capacity_open')) ?></span></div><span class="hero-spark spark-a" aria-hidden="true">✦</span><span class="hero-spark spark-b" aria-hidden="true">✦</span></div></div>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section-space" aria-labelledby="campaigns-heading">
    <div class="container">
        <div class="section-heading"><div><span class="section-kicker"><?= e(t('campaigns')) ?></span><h2 id="campaigns-heading"><?= e(t('our_campaigns')) ?></h2><p><?= e(t('campaigns_intro')) ?></p></div><a class="text-link" href="<?= e(site_url('index.php?page=campaigns')) ?>"><?= e(t('view_all')) ?><i class="fa-solid fa-arrow-left ms-2" aria-hidden="true"></i></a></div>
        <?php if (!$databaseAvailable): ?><div class="alert alert-warning" role="status"><?= e($language === 'ar' ? 'يرجى إعداد اتصال قاعدة البيانات لاستعراض المحتوى.' : 'Configure the database connection to load site content.') ?></div>
        <?php elseif (!$campaigns): ?><div class="empty-state"><i class="fa-regular fa-folder-open" aria-hidden="true"></i><p><?= e(t('no_campaigns')) ?></p></div>
        <?php else: ?><div class="row g-4"><?php foreach (array_slice($campaigns, 0, 3) as $campaign): ?>
            <div class="col-md-6 col-lg-4"><?php require __DIR__ . '/partials/campaign-card.php'; ?></div>
        <?php endforeach; ?></div><?php endif; ?>
    </div>
</section>

<section class="section-space section-tint" aria-labelledby="branches-heading">
    <div class="container">
        <div class="section-heading"><div><span class="section-kicker"><?= e(t('branches')) ?></span><h2 id="branches-heading"><?= e(t('our_branches')) ?></h2><p><?= e(t('branches_intro')) ?></p></div><a class="text-link" href="<?= e(site_url('index.php?page=branches')) ?>"><?= e(t('view_all')) ?><i class="fa-solid fa-arrow-left ms-2" aria-hidden="true"></i></a></div>
        <?php if ($databaseAvailable && $branches): ?><div class="row g-4"><?php foreach (array_slice($branches, 0, 3) as $branch): ?><div class="col-md-6 col-lg-4"><article class="branch-card h-100"><div class="branch-icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></div><h3><?= e($branch['name']) ?></h3><p><i class="fa-solid fa-map-pin me-2" aria-hidden="true"></i><?= e($branch['address']) ?></p><?php if (!empty($branch['phone_number'])): ?><a href="tel:<?= e($branch['phone_number']) ?>" class="branch-contact"><i class="fa-solid fa-phone me-2" aria-hidden="true"></i><?= e($branch['phone_number']) ?></a><?php endif; ?><?php if (!empty($branch['google_map_link'])): ?><a class="branch-map" href="<?= e(safe_public_link($branch['google_map_link'])) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('map')) ?><i class="fa-solid fa-arrow-up-right-from-square ms-2" aria-hidden="true"></i></a><?php endif; ?></article></div><?php endforeach; ?></div>
        <?php elseif ($databaseAvailable): ?><div class="empty-state"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><p><?= e(t('no_branches')) ?></p></div><?php endif; ?>
    </div>
</section>

<section class="cta-band"><div class="container"><div class="cta-inner"><div><span class="section-kicker"><?= e(t('site_tagline')) ?></span><h2><?= e(t('booking_title')) ?></h2><p><?= e(t('booking_intro')) ?></p></div><a class="btn btn-light btn-lg" href="<?= e(site_url('index.php?page=booking')) ?>"><?= e(t('book_now')) ?><i class="fa-solid fa-arrow-left ms-2" aria-hidden="true"></i></a></div></div></section>
