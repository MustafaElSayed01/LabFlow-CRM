<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function admin_current_user(): ?array
{
    $userId = (int) ($_SESSION['admin_user_id'] ?? 0);
    if ($userId < 1) {
        return null;
    }
    $stmt = db()->prepare('SELECT u.id, u.role_id, u.name, u.email, u.phone_number, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND u.is_active = 1 AND u.deleted_at IS NULL LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?: null;
    if (!$user) {
        unset($_SESSION['admin_user_id']);
    }
    return $user;
}

function admin_is_authenticated(): bool
{
    return admin_current_user() !== null;
}

function admin_require_roles(array $allowedRoles): array
{
    $user = admin_current_user();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    if (!in_array($user['role_name'], $allowedRoles, true)) {
        http_response_code(403);
        exit('Forbidden');
    }
    return $user;
}

function admin_branch_ids(int $userId): array
{
    $stmt = db()->prepare('SELECT branch_id FROM branch_staff WHERE user_id = ? AND is_active = 1 ORDER BY is_primary DESC, branch_id ASC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'branch_id'));
}

function admin_audit(mysqli $connection, ?int $userId, string $action, string $table, ?int $recordId, ?array $oldValues = null, ?array $newValues = null): void
{
    $oldJson = $oldValues === null ? null : json_encode($oldValues, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    $newJson = $newValues === null ? null : json_encode($newValues, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    $ipAddress = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null;
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 1000) ?: null;
    $stmt = $connection->prepare('INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ississss', $userId, $action, $table, $recordId, $oldJson, $newJson, $ipAddress, $userAgent);
    $stmt->execute();
}

function admin_flash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function admin_take_flash(): ?array
{
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return is_array($flash) ? $flash : null;
}

function admin_redirect(string $view = 'dashboard'): never
{
    header('Location: index.php?view=' . rawurlencode($view));
    exit;
}

function admin_valid_datetime(string $value): ?string
{
    if ($value === '') {
        return null;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);
    return $date && $date->format('Y-m-d\TH:i') === $value ? $date->format('Y-m-d H:i:s') : null;
}

function admin_branch_scope(array $user): array
{
    return $user['role_name'] === 'receptionist' ? admin_branch_ids((int) $user['id']) : [];
}

function admin_selected_branch_is_allowed(array $user, int $branchId): bool
{
    return $user['role_name'] !== 'receptionist' || in_array($branchId, admin_branch_scope($user), true);
}

function admin_media_url(string $path): string
{
    return preg_match('~^(?:https?://|/(?!/))~i', $path) ? $path : asset($path);
}
