<?php
$selectedCampaign = null;
if (ctype_digit(request_string($_GET, 'id')) && request_string($_GET, 'id') !== '' && $databaseAvailable) {
    $campaignStmt = db()->prepare('SELECT id, name, code, slug, description, image_path, min_reservations, max_reservations, is_active, closed_at, deleted_at FROM campaigns WHERE id = ? LIMIT 1');
    $campaignId = (int) request_string($_GET, 'id');
    $campaignStmt->bind_param('i', $campaignId);
    $campaignStmt->execute();
    $selectedCampaign = $campaignStmt->get_result()->fetch_assoc() ?: null;
    if ($selectedCampaign && ((int) $selectedCampaign['is_active'] !== 1 || $selectedCampaign['closed_at'] || $selectedCampaign['deleted_at'])) {
        $selectedCampaign = null;
    }
}
?>
<section class="page-hero"><div class="container"><span class="section-kicker"><?= e(t('campaigns')) ?></span><h1><?= e($selectedCampaign['name'] ?? t('our_campaigns')) ?></h1><p><?= e($selectedCampaign ? t('campaign_details') : t('campaigns_intro')) ?></p></div></section>
<section class="section-space"><div class="container">
    <?php if (!$databaseAvailable): ?><div class="alert alert-warning" role="status"><?= e($language === 'ar' ? 'تعذر الاتصال بقاعدة البيانات.' : 'Could not connect to the database.') ?></div>
    <?php elseif ($selectedCampaign): ?>
        <?php $detailImage = !empty($selectedCampaign['image_path']) ? (preg_match('~^https?://~i', $selectedCampaign['image_path']) || preg_match('~^/(?!/)~', $selectedCampaign['image_path']) ? $selectedCampaign['image_path'] : asset($selectedCampaign['image_path'])) : ''; ?>
        <div class="row align-items-center g-5 campaign-detail">
            <div class="col-lg-6"><div class="detail-image"><?php if ($detailImage): ?><img src="<?= e($detailImage) ?>" alt="<?= e($selectedCampaign['name']) ?>"><?php else: ?><div class="campaign-image-placeholder"><i class="fa-solid fa-heart-pulse" aria-hidden="true"></i></div><?php endif; ?></div></div>
            <div class="col-lg-6"><span class="campaign-code"><?= e($selectedCampaign['code']) ?></span><h2><?= e($selectedCampaign['name']) ?></h2><div class="detail-description"><?= nl2br(e($selectedCampaign['description'] ?: t('campaign_details'))) ?></div>
                <?php $isOpen = in_array((int) $selectedCampaign['id'], array_map(static fn(array $item): int => (int) $item['id'], $campaigns), true); ?>
                <?php if ($isOpen): ?><a class="btn btn-primary btn-lg mt-3" href="<?= e(site_url('index.php?page=booking&campaign=' . (int) $selectedCampaign['id'])) ?>"><?= e(t('book_now')) ?><i class="fa-solid fa-arrow-left ms-2" aria-hidden="true"></i></a><?php else: ?><span class="badge text-bg-secondary mt-3"><?= e(t('campaign_full')) ?></span><?php endif; ?>
            </div>
        </div>
    <?php elseif (!$campaigns): ?><div class="empty-state"><i class="fa-regular fa-folder-open" aria-hidden="true"></i><p><?= e(t('no_campaigns')) ?></p></div>
    <?php else: ?><div class="row g-4"><?php foreach ($campaigns as $campaign): ?><div class="col-md-6 col-lg-4"><?php require __DIR__ . '/partials/campaign-card.php'; ?></div><?php endforeach; ?></div><?php endif; ?>
</div></section>
