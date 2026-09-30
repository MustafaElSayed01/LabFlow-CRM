<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = $siteSettings['lab_name'];
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
require __DIR__ . '/pages/home.php';
require __DIR__ . '/includes/footer.php';
