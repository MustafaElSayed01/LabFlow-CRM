<?php
$banners = [];
$campaignBranches = [];
foreach ($campaigns as $campaign) {
    $campaignBranches[(string) $campaign['id']] = campaign_branches((int) $campaign['id']);
}
if ($databaseAvailable) {
    try {
        $banners = db()->query("SELECT title, subtitle, image_path, button_text, button_link, link_target FROM banners WHERE is_active = 1 AND (starts_at IS NULL OR starts_at <= NOW()) AND (ends_at IS NULL OR ends_at >= NOW()) ORDER BY sort_order ASC, id DESC")->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable) {
        $banners = [];
    }
}

$bookingResult = null;
$selectedCampaignId = count($campaigns) === 1 ? (int) $campaigns[0]['id'] : (int) request_string($_POST, 'campaign_id');
$selectedBranchId = (int) request_string($_POST, 'branch_id');
$formValues = [
    'full_name' => request_string($_POST, 'full_name'),
    'phone_number' => request_string($_POST, 'phone_number'),
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $bookingResult = 'error';
    } elseif (trim(request_string($_POST, 'website_url')) !== '') {
        $bookingResult = 'success';
    } else {
        $recentBookings = array_filter($_SESSION['booking_attempts'] ?? [], static fn(int $stamp): bool => $stamp > time() - 600);
        if (count($recentBookings) >= 5) {
            $bookingResult = 'error';
        } else {
            $_SESSION['booking_attempts'] = array_values($recentBookings);
            $_SESSION['booking_attempts'][] = time();
            $bookingResult = submit_public_booking($_POST);
            if ($bookingResult === 'success') {
                $formValues = ['full_name' => '', 'phone_number' => ''];
                $selectedBranchId = 0;
                if (count($campaigns) > 1) {
                    $selectedCampaignId = 0;
                }
            }
        }
    }
}

$bookingMessages = [
    'success' => ['success', t('booking_success')],
    'error' => ['danger', t('booking_error')],
    'invalid' => ['warning', t('required')],
    'phone' => ['warning', t('invalid_phone')],
    'campaign' => ['warning', t('campaign_full')],
    'campaign_required' => ['warning', t('select_campaign')],
    'branch_required' => ['warning', t('select_branch')],
    'branch' => ['warning', t('select_branch')],
    'duplicate' => ['warning', t('already_booked')],
];
?>
<section id="home" class="hero-section">
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
                    <div class="d-flex flex-wrap gap-3 mt-4"><a class="btn btn-primary btn-lg" href="#campaigns"><?= e(t('explore_campaigns')) ?><i class="fa-solid fa-arrow-left ms-2" aria-hidden="true"></i></a><a class="btn btn-outline-primary btn-lg" href="#booking"><?= e(t('book')) ?></a></div>
                    <div class="hero-assurance mt-4"><span class="assurance-icon"><i class="fa-solid fa-heart-pulse" aria-hidden="true"></i></span><span><?= e(t('site_tagline')) ?></span></div>
                </div>
                <div class="col-lg-5"><div class="hero-visual"><div class="hero-visual-ring"></div><div class="hero-visual-card hero-card-main"><span class="visual-icon"><i class="fa-solid fa-flask-vial" aria-hidden="true"></i></span><strong><?= e($siteSettings['lab_name']) ?></strong><span><?= e(t('site_tagline')) ?></span></div><div class="hero-visual-card hero-card-float"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span><?= e(t('capacity_open')) ?></span></div><span class="hero-spark spark-a" aria-hidden="true">✦</span><span class="hero-spark spark-b" aria-hidden="true">✦</span></div></div>
            </div>
        <?php endif; ?>
    </div>
</section>

<section id="campaigns" class="section-space anchor-section" aria-labelledby="campaigns-heading">
    <div class="container">
        <div class="section-heading"><div><span class="section-kicker"><?= e(t('campaigns')) ?></span><h2 id="campaigns-heading"><?= e(t('our_campaigns')) ?></h2><p><?= e(t('campaigns_intro')) ?></p></div><a class="text-link" href="#booking"><?= e(t('book_now')) ?><i class="fa-solid fa-arrow-left ms-2" aria-hidden="true"></i></a></div>
        <?php if (!$databaseAvailable): ?><div class="alert alert-warning" role="status"><?= e($language === 'ar' ? 'يرجى إعداد اتصال قاعدة البيانات لاستعراض المحتوى.' : 'Configure the database connection to load site content.') ?></div>
        <?php elseif (!$campaigns): ?><div class="empty-state"><i class="fa-regular fa-folder-open" aria-hidden="true"></i><p><?= e(t('no_campaigns')) ?></p></div>
        <?php else: ?><div class="row g-4"><?php foreach ($campaigns as $campaign): ?><div class="col-md-6 col-lg-4"><?php require __DIR__ . '/partials/campaign-card.php'; ?></div><?php endforeach; ?></div><?php endif; ?>
    </div>
</section>

<section id="branches" class="section-space section-tint anchor-section" aria-labelledby="branches-heading">
    <div class="container">
        <div class="section-heading"><div><span class="section-kicker"><?= e(t('branches')) ?></span><h2 id="branches-heading"><?= e(t('our_branches')) ?></h2><p><?= e(t('branches_intro')) ?></p></div></div>
        <?php if ($databaseAvailable && $branches): ?><div class="row g-4"><?php foreach ($branches as $branch): ?><div class="col-md-6 col-lg-4"><article class="branch-card h-100"><div class="branch-icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></div><h3><?= e($branch['name']) ?></h3><p><i class="fa-solid fa-map-pin me-2" aria-hidden="true"></i><?= e($branch['address']) ?></p><?php if (!empty($branch['phone_number'])): ?><a href="tel:<?= e($branch['phone_number']) ?>" class="branch-contact"><i class="fa-solid fa-phone me-2" aria-hidden="true"></i><?= e($branch['phone_number']) ?></a><?php endif; ?><?php if (!empty($branch['google_map_link'])): ?><a class="branch-map" href="<?= e(safe_public_link($branch['google_map_link'])) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('map')) ?><i class="fa-solid fa-arrow-up-right-from-square ms-2" aria-hidden="true"></i></a><?php endif; ?></article></div><?php endforeach; ?></div>
        <?php elseif ($databaseAvailable): ?><div class="empty-state"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><p><?= e(t('no_branches')) ?></p></div><?php endif; ?>
    </div>
</section>

<section id="booking" class="section-space anchor-section" aria-labelledby="booking-heading">
    <div class="container">
        <div class="section-heading"><div><span class="section-kicker"><?= e(t('book')) ?></span><h2 id="booking-heading"><?= e(t('booking_title')) ?></h2><p><?= e(t('booking_intro')) ?></p></div></div>
        <div class="row justify-content-center"><div class="col-lg-9 col-xl-8">
            <?php if (isset($bookingMessages[$bookingResult ?? ''])): [$alertType, $alertText] = $bookingMessages[$bookingResult]; ?><div class="alert alert-<?= e($alertType) ?>" role="status"><?= e($alertText) ?></div><?php endif; ?>
            <?php if (!$databaseAvailable): ?><div class="alert alert-warning" role="status"><?= e($language === 'ar' ? 'يرجى إعداد قاعدة البيانات قبل تفعيل الحجز.' : 'Configure the database before enabling bookings.') ?></div>
            <?php elseif (!$campaigns): ?><div class="empty-state"><i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i><p><?= e(t('no_campaigns')) ?></p></div>
            <?php else: ?>
            <form method="post" action="<?= e(site_url('index.php#booking')) ?>" class="booking-form needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="form-trap" aria-hidden="true"><label for="website-url">Website</label><input id="website-url" type="text" name="website_url" tabindex="-1" autocomplete="off"></div>
                <fieldset class="campaign-picker mb-4"><legend class="form-label"><?= e(t('campaign')) ?> <span class="required-mark">*</span></legend>
                    <div class="campaign-tag-list" role="group" aria-label="<?= e(t('campaign')) ?>">
                        <?php foreach ($campaigns as $campaignIndex => $campaign): $isSelectedCampaign = $selectedCampaignId === (int) $campaign['id']; ?>
                            <button type="button" class="campaign-tag <?= $isSelectedCampaign ? 'is-selected' : '' ?>" style="--campaign-hue:<?= ($campaignIndex * 67) % 360 ?>" data-campaign-id="<?= (int) $campaign['id'] ?>" aria-pressed="<?= $isSelectedCampaign ? 'true' : 'false' ?>">
                                <span class="campaign-tag-dot" aria-hidden="true"></span><?= e($campaign['name']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="campaign_id" id="selected-campaign-id" value="<?= $selectedCampaignId ?>">
                    <div class="campaign-validation invalid-feedback"><?= e(t('select_campaign')) ?></div>
                </fieldset>
                <script type="application/json" id="campaign-branch-data"><?= json_encode($campaignBranches, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
                <div id="branch-picker" class="mb-4" hidden>
                    <label class="form-label" for="branch-select"><?= e(t('branch')) ?> <span class="required-mark" id="branch-required-mark">*</span></label>
                    <select class="form-select" id="branch-select" data-selected-branch="<?= $selectedBranchId ?>"><option value=""><?= e(t('choose_branch')) ?></option></select>
                    <input type="hidden" id="single-branch-id">
                    <div class="single-branch-name text-muted mt-2" id="single-branch-name" hidden></div>
                    <div class="invalid-feedback"><?= e(t('select_branch')) ?></div>
                </div>
                <div class="row g-4">
                    <div class="col-md-6"><label class="form-label" for="full-name"><?= e(t('full_name')) ?> <span class="required-mark">*</span></label><input class="form-control" id="full-name" name="full_name" value="<?= e($formValues['full_name']) ?>" maxlength="200" autocomplete="name" required><div class="invalid-feedback"><?= e(t('required')) ?></div></div>
                    <div class="col-md-6"><label class="form-label" for="phone-number"><?= e(t('phone_number')) ?> <span class="required-mark">*</span></label><input class="form-control" id="phone-number" name="phone_number" value="<?= e($formValues['phone_number']) ?>" type="tel" maxlength="24" autocomplete="tel" required pattern="[0-9٠-٩۰-۹+() .-]{7,24}"><div class="invalid-feedback"><?= e(t('invalid_phone')) ?></div></div>
                    <div class="col-12"><button class="btn btn-primary btn-lg" type="submit"><?= e(t('submit_booking')) ?><i class="fa-solid fa-arrow-left ms-2" aria-hidden="true"></i></button></div>
                </div>
            </form>
            <?php endif; ?>
        </div></div>
    </div>
</section>
