<?php
$bookingResult = null;
$selectedCampaignId = (int) (request_string($_POST, 'campaign_id') ?: request_string($_GET, 'campaign'));
$selectedBranchId = (int) request_string($_POST, 'branch_id');
$formValues = [
    'full_name' => request_string($_POST, 'full_name'), 'phone_number' => request_string($_POST, 'phone_number'),
    'email' => request_string($_POST, 'email'), 'reservation_date' => request_string($_POST, 'reservation_date'),
    'reservation_time' => request_string($_POST, 'reservation_time'), 'notes' => request_string($_POST, 'notes'),
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $bookingResult = 'error';
    } elseif (trim(request_string($_POST, 'website_url')) !== '') {
        $bookingResult = 'success';
    } elseif (request_string($_POST, 'consent') !== '1') {
        $bookingResult = 'invalid';
    } else {
        $recentBookings = array_filter($_SESSION['booking_attempts'] ?? [], static fn(int $stamp): bool => $stamp > time() - 600);
        if (count($recentBookings) >= 5) {
            $bookingResult = 'error';
        } else {
            $_SESSION['booking_attempts'] = array_values($recentBookings);
            $_SESSION['booking_attempts'][] = time();
            $bookingResult = submit_public_booking($_POST);
            if ($bookingResult === 'success') {
                $formValues = array_fill_keys(array_keys($formValues), '');
            }
        }
    }
}

$availableBranches = [];
$branchOptions = [];
if ($databaseAvailable && $campaigns) {
    foreach ($campaigns as $campaign) {
        $branchOptions[(string) $campaign['id']] = campaign_branches((int) $campaign['id']);
    }
    $availableBranches = $branchOptions[(string) $selectedCampaignId] ?? [];
}
$bookingMessages = [
    'success' => ['success', t('booking_success')], 'error' => ['danger', t('booking_error')],
    'invalid' => ['warning', t('required')], 'campaign' => ['warning', t('campaign_full')],
    'branch' => ['warning', t('select_branch')], 'slot' => ['warning', t('slot_taken')],
    'hours' => ['warning', t('outside_hours')], 'duplicate' => ['warning', t('already_booked')],
    'phone' => ['warning', t('invalid_phone')], 'date' => ['warning', t('invalid_date')],
];
?>
<section class="page-hero"><div class="container"><span class="section-kicker"><?= e(t('book')) ?></span><h1><?= e(t('booking_title')) ?></h1><p><?= e(t('booking_intro')) ?></p></div></section>
<section class="section-space"><div class="container"><div class="row justify-content-center"><div class="col-lg-9 col-xl-8">
    <?php if (isset($bookingMessages[$bookingResult ?? ''])): [$alertType, $alertText] = $bookingMessages[$bookingResult]; ?><div class="alert alert-<?= e($alertType) ?>" role="status"><?= e($alertText) ?></div><?php endif; ?>
    <?php if (!$databaseAvailable): ?><div class="alert alert-warning" role="status"><?= e($language === 'ar' ? 'يرجى إعداد قاعدة البيانات قبل تفعيل الحجز.' : 'Configure the database before enabling bookings.') ?></div>
    <?php elseif (!$campaigns || !$branches): ?><div class="empty-state"><i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i><p><?= e(!$campaigns ? t('no_campaigns') : t('no_branches')) ?></p></div>
    <?php else: ?>
    <form method="post" action="<?= e(site_url('index.php?page=booking')) ?>" class="booking-form needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-trap" aria-hidden="true"><label for="website-url">Website</label><input id="website-url" type="text" name="website_url" tabindex="-1" autocomplete="off"></div>
        <div class="row g-4">
            <div class="col-md-6"><label class="form-label" for="full-name"><?= e(t('full_name')) ?> <span class="required-mark">*</span></label><input class="form-control" id="full-name" name="full_name" value="<?= e($formValues['full_name']) ?>" maxlength="200" autocomplete="name" required><div class="invalid-feedback"><?= e(t('required')) ?></div></div>
            <div class="col-md-6"><label class="form-label" for="phone-number"><?= e(t('phone_number')) ?> <span class="required-mark">*</span></label><input class="form-control" id="phone-number" name="phone_number" value="<?= e($formValues['phone_number']) ?>" type="tel" maxlength="24" autocomplete="tel" required pattern="[0-9٠-٩۰-۹+() .-]{7,24}"><div class="invalid-feedback"><?= e(t('invalid_phone')) ?></div></div>
            <div class="col-md-6"><label class="form-label" for="email"><?= e(t('email')) ?></label><input class="form-control" id="email" name="email" value="<?= e($formValues['email']) ?>" type="email" maxlength="254" autocomplete="email"></div>
            <div class="col-md-6"><label class="form-label" for="campaign-id"><?= e(t('campaign')) ?> <span class="required-mark">*</span></label><select class="form-select" id="campaign-id" name="campaign_id" required><option value=""><?= e(t('choose_campaign')) ?></option><?php foreach ($campaigns as $campaign): ?><option value="<?= (int) $campaign['id'] ?>" <?= $selectedCampaignId === (int) $campaign['id'] ? 'selected' : '' ?>><?= e($campaign['name']) ?></option><?php endforeach; ?></select><div class="invalid-feedback"><?= e(t('select_campaign')) ?></div></div>
            <div class="col-md-6"><label class="form-label" for="branch-id"><?= e(t('branch')) ?> <span class="required-mark">*</span></label><select class="form-select" id="branch-id" name="branch_id" required data-selected-branch="<?= $selectedBranchId ?>"><option value=""><?= e(t('choose_branch')) ?></option><?php foreach ($availableBranches as $branch): ?><option value="<?= (int) $branch['id'] ?>" <?= $selectedBranchId === (int) $branch['id'] ? 'selected' : '' ?>><?= e($branch['name']) ?></option><?php endforeach; ?></select><div class="invalid-feedback"><?= e(t('select_branch')) ?></div></div>
            <div class="col-md-6"><label class="form-label" for="reservation-date"><?= e(t('date')) ?> <span class="required-mark">*</span></label><input class="form-control" id="reservation-date" type="date" name="reservation_date" value="<?= e($formValues['reservation_date']) ?>" min="<?= e(date('Y-m-d')) ?>" required><div class="invalid-feedback"><?= e(t('invalid_date')) ?></div></div>
            <div class="col-md-6"><label class="form-label" for="reservation-time"><?= e(t('time')) ?> <span class="required-mark">*</span></label><input class="form-control" id="reservation-time" type="time" name="reservation_time" value="<?= e($formValues['reservation_time']) ?>" step="900" required><div class="invalid-feedback"><?= e(t('required')) ?></div></div>
            <div class="col-12"><label class="form-label" for="notes"><?= e(t('notes')) ?></label><textarea class="form-control" id="notes" name="notes" rows="3" maxlength="2000"><?= e($formValues['notes']) ?></textarea></div>
            <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="consent" value="1" id="consent" required <?= request_string($_POST, 'consent') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="consent"><?= e(t('consent')) ?></label><div class="invalid-feedback"><?= e(t('required')) ?></div></div><small class="privacy-note"><?= e(t('privacy_note')) ?></small></div>
            <div class="col-12 d-flex flex-column flex-sm-row align-items-sm-center gap-3"><button class="btn btn-primary btn-lg" type="submit"><?= e(t('submit_booking')) ?><i class="fa-solid fa-arrow-left ms-2" aria-hidden="true"></i></button><span class="privacy-note"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i><?= e(t('privacy_note')) ?></span></div>
        </div>
    </form>
    <script type="application/json" id="campaign-branch-data"><?= json_encode($branchOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>
</div></div></div></section>
