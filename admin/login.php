<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/admin.php';

if (admin_is_authenticated()) {
    header('Location: index.php');
    exit;
}
$errorMessage = '';
$flash = admin_take_flash();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attempts = array_filter($_SESSION['admin_login_attempts'] ?? [], static fn(int $stamp): bool => $stamp > time() - 900);
    $_SESSION['admin_login_attempts'] = array_values($attempts);
    if (count($attempts) >= 10) {
        $errorMessage = $language === 'ar' ? 'محاولات كثيرة. حاول بعد قليل.' : 'Too many attempts. Try again later.';
    } elseif (!csrf_valid()) {
        $errorMessage = $language === 'ar' ? 'انتهت صلاحية النموذج. حدّث الصفحة وحاول مجدداً.' : 'The form expired. Refresh and try again.';
    } else {
        $_SESSION['admin_login_attempts'][] = time();
        $identity = trim(request_string($_POST, 'identity'));
        $password = request_string($_POST, 'password');
        if ($identity !== '' && $password !== '') {
            $stmt = db()->prepare('SELECT u.id, u.password, u.name, u.is_active, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE (u.email = ? OR u.phone_number = ?) AND u.deleted_at IS NULL LIMIT 1');
            $stmt->bind_param('ss', $identity, $identity);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            if ($user && (int) $user['is_active'] === 1 && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                unset($_SESSION['csrf_token']);
                $_SESSION['admin_user_id'] = (int) $user['id'];
                unset($_SESSION['admin_login_attempts']);
                $update = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
                $userId = (int) $user['id'];
                $update->bind_param('i', $userId);
                $update->execute();
                header('Location: index.php');
                exit;
            }
        }
        $errorMessage = $language === 'ar' ? 'بيانات الدخول غير صحيحة أو الحساب غير نشط.' : 'Invalid credentials or inactive account.';
    }
}
$superAdmins = db()->query("SELECT COUNT(*) AS total FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'super_admin' AND u.deleted_at IS NULL")->fetch_assoc();
?>
<!doctype html>
<html lang="<?= e($language) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($language === 'ar' ? 'دخول الإدارة' : 'Admin sign in') ?></title><link rel="stylesheet" href="<?= asset('assets/css/bootstrap' . ($isRtl ? '.rtl' : '') . '.min.css') ?>"><link rel="stylesheet" href="<?= asset('assets/css/main.css') ?>"><link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>"></head>
<body class="admin-auth-page"><main class="auth-card"><a href="<?= e(site_url('index.php')) ?>" class="auth-brand"><img src="<?= e(admin_media_url((string) $siteSettings['logo_path'])) ?>" alt="<?= e($siteSettings['lab_name']) ?>"></a><span class="section-kicker"><?= e($language === 'ar' ? 'لوحة التحكم' : 'Administration') ?></span><h1><?= e($language === 'ar' ? 'تسجيل الدخول' : 'Sign in') ?></h1>
    <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif; ?>
    <?php if ($errorMessage !== ''): ?><div class="alert alert-danger" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
    <form method="post" class="vstack gap-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label class="form-label"><?= e($language === 'ar' ? 'البريد الإلكتروني أو الهاتف' : 'Email or phone') ?><input class="form-control mt-1" name="identity" autocomplete="username" required autofocus></label><label class="form-label"><?= e($language === 'ar' ? 'كلمة المرور' : 'Password') ?><input class="form-control mt-1" name="password" type="password" autocomplete="current-password" required></label><button class="btn btn-primary btn-lg" type="submit"><?= e($language === 'ar' ? 'دخول' : 'Sign in') ?></button></form>
    <?php if ((int) ($superAdmins['total'] ?? 0) === 0): ?><p class="auth-footnote"><a href="setup.php"><?= e($language === 'ar' ? 'الإعداد الأول للمشرف الأعلى' : 'First-time super admin setup') ?></a></p><?php endif; ?>
    <a class="auth-home-link" href="<?= e(site_url('index.php')) ?>">← <?= e(t('home')) ?></a>
</main></body></html>
