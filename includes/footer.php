</main>
<footer class="site-footer">
    <div class="container py-5">
        <div class="row g-4 align-items-start">
            <div class="col-lg-5">
                <a href="<?= e(site_url('index.php')) ?>" class="footer-brand"><img src="<?= e($brandLogo) ?>"
                        alt="<?= e($siteSettings['lab_name']) ?>" class="brand-logo"></a>
                <p class="footer-note mt-3 mb-0"><?= e($siteSettings['about'] ?: t('footer_note')) ?></p>
            </div>
            <div class="col-6 col-lg-3">
                <h2 class="footer-heading"><?= e(t('menu')) ?></h2>
                <a class="footer-link"
                    href="#campaigns"><?= e(t('campaigns')) ?></a>
                <a class="footer-link" href="#branches"><?= e(t('branches')) ?></a>
                <a class="footer-link" href="#booking"><?= e(t('book')) ?></a>
            </div>
            <div class="col-6 col-lg-4">
                <h2 class="footer-heading"><?= e(t('contact')) ?></h2>
                <?php if (!empty($siteSettings['phone_number'])): ?><a class="footer-link"
                    href="tel:<?= e($siteSettings['phone_number']) ?>"><?= e($siteSettings['phone_number']) ?></a><?php endif; ?>
                <?php if (!empty($siteSettings['email'])): ?><a class="footer-link"
                    href="mailto:<?= e($siteSettings['email']) ?>"><?= e($siteSettings['email']) ?></a><?php endif; ?>
                <?php if (!empty($siteSettings['address'])): ?><p class="footer-note mb-0">
                    <?= e($siteSettings['address']) ?></p><?php endif; ?>
            </div>
        </div>
        <div class="footer-bottom mt-4 pt-3"><span>© <?= date('Y') ?>
                <?= e($siteSettings['lab_name']) ?></span><span><?= e(t('site_tagline')) ?></span></div>
    </div>
</footer>
<script src="<?= asset('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= asset('assets/js/main.js') ?>"></script>
</body>

</html>
