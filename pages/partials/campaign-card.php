<?php $campaignImage = !empty($campaign['image_path']) ? (preg_match('~^https?://~i', $campaign['image_path']) || preg_match('~^/(?!/)~', $campaign['image_path']) ? $campaign['image_path'] : asset($campaign['image_path'])) : ''; ?>
<article class="campaign-card h-100">
    <a class="campaign-image" href="#booking" data-campaign-select="<?= (int) $campaign['id'] ?>"
        aria-label="<?= e($campaign['name']) ?>">
        <?php if ($campaignImage): ?><img src="<?= e($campaignImage) ?>" alt="<?= e($campaign['name']) ?>"
            loading="lazy"><?php else: ?><span class="campaign-image-placeholder"><i class="fa-solid fa-heart-pulse"
                aria-hidden="true"></i><span><?= e(t('no_image')) ?></span></span><?php endif; ?>
        <span class="campaign-image-badge"><i class="fa-solid fa-shield-heart" aria-hidden="true"></i></span>
    </a>
    <div class="campaign-card-body"><span class="campaign-code"><?= e($campaign['code']) ?></span>
        <h3><a href="#booking" data-campaign-select="<?= (int) $campaign['id'] ?>"><?= e($campaign['name']) ?></a></h3>
        <?php if (!empty($campaign['description'])): ?><p>
            <?= e(mb_strimwidth(strip_tags((string) $campaign['description']), 0, 125, '…')) ?></p><?php endif; ?>
        <a class="card-action" href="#booking"
            data-campaign-select="<?= (int) $campaign['id'] ?>"><?= e(t('book_now')) ?><i
                class="fa-solid fa-arrow-left ms-2" aria-hidden="true"></i></a>
    </div>
</article>