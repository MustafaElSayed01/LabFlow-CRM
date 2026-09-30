<section class="page-hero"><div class="container"><span class="section-kicker"><?= e(t('branches')) ?></span><h1><?= e(t('our_branches')) ?></h1><p><?= e(t('branches_intro')) ?></p></div></section>
<section class="section-space"><div class="container">
    <?php if (!$databaseAvailable): ?><div class="alert alert-warning" role="status"><?= e($language === 'ar' ? 'تعذر الاتصال بقاعدة البيانات.' : 'Could not connect to the database.') ?></div>
    <?php elseif (!$branches): ?><div class="empty-state"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><p><?= e(t('no_branches')) ?></p></div>
    <?php else: ?><div class="row g-4"><?php foreach ($branches as $branch): ?>
        <div class="col-md-6 col-lg-4"><article class="branch-card h-100"><div class="branch-icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></div><h2><?= e($branch['name']) ?></h2><p><i class="fa-solid fa-map-pin me-2" aria-hidden="true"></i><?= e($branch['address']) ?></p>
            <?php if (!empty($branch['phone_number'])): ?><a href="tel:<?= e($branch['phone_number']) ?>" class="branch-contact"><i class="fa-solid fa-phone me-2" aria-hidden="true"></i><?= e($branch['phone_number']) ?></a><?php endif; ?>
            <?php if (!empty($branch['email'])): ?><a href="mailto:<?= e($branch['email']) ?>" class="branch-contact"><i class="fa-regular fa-envelope me-2" aria-hidden="true"></i><?= e($branch['email']) ?></a><?php endif; ?>
            <?php if (!empty($branch['google_map_link'])): ?><a class="branch-map" href="<?= e(safe_public_link($branch['google_map_link'])) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('map')) ?><i class="fa-solid fa-arrow-up-right-from-square ms-2" aria-hidden="true"></i></a><?php endif; ?>
        </article></div>
    <?php endforeach; ?></div><?php endif; ?>
</div></section>
