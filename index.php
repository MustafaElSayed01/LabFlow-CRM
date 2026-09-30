<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$page = request_string($_GET, 'page') ?: 'home';
if (!in_array($page, ['home', 'campaigns', 'branches', 'booking'], true)) {
    http_response_code(404);
    $page = 'not_found';
}
$pageTitle = match ($page) {
    'campaigns' => t('campaigns'), 'branches' => t('branches'), 'booking' => t('booking_title'), default => $siteSettings['lab_name'],
};
$databaseAvailable = db_ready();
$campaigns = $branches = [];
if ($databaseAvailable) {
    try {
        $campaigns = active_campaigns();
        $branches = active_branches();
    } catch (Throwable) {
        $databaseAvailable = false;
    }
}

require __DIR__ . '/includes/header.php';

if ($page === 'booking') {
    require __DIR__ . '/pages/booking.php';
} elseif ($page === 'campaigns') {
    require __DIR__ . '/pages/campaigns.php';
} elseif ($page === 'branches') {
    require __DIR__ . '/pages/branches.php';
} elseif ($page === 'home') {
    require __DIR__ . '/pages/home.php';
} else {
    ?>
<section class="section-space">
    <div class="container text-center"><span class="section-kicker">404</span>
        <h1 class="display-6 fw-bold mb-3"><?= e(t('not_found')) ?></h1><a class="btn btn-primary"
            href="<?= e(site_url('index.php')) ?>"><?= e(t('home')) ?></a>
    </div>
</section>
<?php
}

require __DIR__ . '/includes/footer.php';