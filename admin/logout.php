<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/admin.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    http_response_code(400);
    exit('Invalid request');
}
unset($_SESSION['admin_user_id']);
session_regenerate_id(true);
admin_flash('success', $language === 'ar' ? 'تم تسجيل الخروج.' : 'You have signed out.');
header('Location: login.php');
exit;
