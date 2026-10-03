<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/admin.php';

$countResult = db()->query("SELECT COUNT(*) AS total FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'super_admin' AND u.deleted_at IS NULL");
$setupComplete = (int) $countResult->fetch_assoc()['total'] > 0;
$setupKey = app_env('ADMIN_SETUP_KEY') ?: '';
$setupReady = strlen($setupKey) >= 32 && !str_contains($setupKey, 'replace-with-');
$errorMessage = '';

if ($setupComplete) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(request_string($_POST, 'name'));
    $email = strtolower(trim(request_string($_POST, 'email')));
    $phone = preg_replace('/[^0-9+]/', '', trim(request_string($_POST, 'phone_number')));
    $password = request_string($_POST, 'password');
    $providedKey = request_string($_POST, 'setup_key');

    if (!csrf_valid() || !$setupReady || !hash_equals($setupKey, $providedKey)) {
        $errorMessage = $language === 'ar' ? 'تعذر التحقق من بيانات الإعداد.' : 'Setup could not be verified.';
    } elseif ($name === '' || mb_strlen($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255 || mb_strlen($phone) > 20 || strlen($password) < 12) {
        $errorMessage = $language === 'ar' ? 'أكمل الحقول المطلوبة واختر كلمة مرور من 12 حرفاً على الأقل.' : 'Complete the required fields and use a password with at least 12 characters.';
    } else {
        $transactionStarted = false;
        try {
            $connection = db();
            $connection->begin_transaction();
            $transactionStarted = true;
            $connection->query("SELECT id FROM roles WHERE name = 'super_admin' FOR UPDATE");
            $countResult = $connection->query("SELECT COUNT(*) AS total FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'super_admin' AND u.deleted_at IS NULL");
            if ((int) $countResult->fetch_assoc()['total'] > 0) {
                $connection->rollback();
                header('Location: login.php');
                exit;
            }
            $roleResult = $connection->query("SELECT id FROM roles WHERE name = 'super_admin' LIMIT 1");
            $roleId = (int) ($roleResult->fetch_assoc()['id'] ?? 0);
            if ($roleId < 1) {
                throw new RuntimeException('roles_not_seeded');
            }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $connection->prepare('INSERT INTO users (role_id, name, phone_number, email, password) VALUES (?, ?, ?, ?, ?)');
            $phoneValue = $phone !== '' ? $phone : null;
            $insert->bind_param('issss', $roleId, $name, $phoneValue, $email, $passwordHash);
            $insert->execute();
            $userId = (int) $connection->insert_id;
            admin_audit($connection, $userId, 'insert', 'users', $userId, null, ['name' => $name, 'email' => $email, 'role' => 'super_admin']);
            $connection->commit();
            $transactionStarted = false;
            admin_flash('success', $language === 'ar' ? 'تم إنشاء حساب المشرف الأعلى. سجّل الدخول للمتابعة.' : 'Super admin created. Sign in to continue.');
            header('Location: login.php');
            exit;
        } catch (Throwable) {
            if (isset($connection) && $transactionStarted) {
                try { $connection->rollback(); } catch (Throwable) {}
            }
            $errorMessage = $language === 'ar' ? 'تعذر إنشاء الحساب. قد يكون البريد أو الهاتف مستخدماً.' : 'Could not create the account. The email or phone may already be in use.';
        }
    }
}
?>
<!doctype html>
<html lang="<?= e($language) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($language === 'ar' ? 'إعداد المشرف الأعلى' : 'Super admin setup') ?></title>
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap' . ($isRtl ? '.rtl' : '') . '.min.css') ?>"><link rel="stylesheet" href="<?= asset('assets/css/main.css') ?>"><link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body class="admin-auth-page">
<main class="auth-card"><a href="<?= e(site_url('index.php')) ?>" class="auth-brand"><img src="<?= e(admin_media_url((string) $siteSettings['logo_path'])) ?>" alt="<?= e($siteSettings['lab_name']) ?>"></a>
    <span class="section-kicker"><?= e($language === 'ar' ? 'إعداد لمرة واحدة' : 'One-time setup') ?></span><h1><?= e($language === 'ar' ? 'إنشاء المشرف الأعلى' : 'Create the super admin') ?></h1>
    <?php if (!$setupReady): ?><div class="alert alert-warning"><?= e($language === 'ar' ? 'أضف ADMIN_SETUP_KEY بطول 32 حرفاً على الأقل إلى ملف .env قبل الإعداد.' : 'Set ADMIN_SETUP_KEY to a secret of at least 32 characters in .env before setup.') ?></div><?php endif; ?>
    <?php if ($errorMessage !== ''): ?><div class="alert alert-danger" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
    <form method="post" class="vstack gap-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label class="form-label"><?= e($language === 'ar' ? 'مفتاح الإعداد' : 'Setup key') ?><input class="form-control mt-1" name="setup_key" type="password" autocomplete="off" required <?= !$setupReady ? 'disabled' : '' ?>></label>
        <label class="form-label"><?= e($language === 'ar' ? 'الاسم' : 'Name') ?><input class="form-control mt-1" name="name" maxlength="150" required></label>
        <label class="form-label"><?= e($language === 'ar' ? 'البريد الإلكتروني' : 'Email') ?><input class="form-control mt-1" name="email" type="email" maxlength="255" autocomplete="email" required></label>
        <label class="form-label"><?= e($language === 'ar' ? 'الهاتف (اختياري)' : 'Phone (optional)') ?><input class="form-control mt-1" name="phone_number" type="tel" maxlength="20" autocomplete="tel"></label>
        <label class="form-label"><?= e($language === 'ar' ? 'كلمة المرور' : 'Password') ?><input class="form-control mt-1" name="password" type="password" minlength="12" autocomplete="new-password" required></label>
        <button class="btn btn-primary btn-lg" type="submit" <?= !$setupReady ? 'disabled' : '' ?>><?= e($language === 'ar' ? 'إنشاء الحساب' : 'Create account') ?></button>
    </form>
</main>
</body></html>
