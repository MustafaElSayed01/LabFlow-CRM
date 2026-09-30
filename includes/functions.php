<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function request_string(array $input, string $key): string
{
    return isset($input[$key]) && is_string($input[$key]) ? $input[$key] : '';
}

function t(string $key): string
{
    global $language;
    return TRANSLATIONS[$language][$key] ?? TRANSLATIONS['en'][$key] ?? $key;
}

function site_url(string $path = ''): string
{
    $base = rtrim(app_env('APP_URL') ?: '', '/');
    return $base === '' ? $path : $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function asset(string $path): string
{
    return site_url($path);
}

function safe_public_link(mixed $value): string
{
    $value = trim((string) $value);
    if ($value !== '' && preg_match('~^(?:https?://|/(?!/)|#|index\.php(?:[?#]|$))~i', $value)) {
        return $value;
    }
    return '#';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_valid(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && is_string($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function safe_hex(mixed $value, string $fallback): string
{
    $value = (string) $value;
    return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $fallback;
}

function site_settings(): array
{
    $settings = [
        'lab_name' => app_env('SITE_NAME') ?: 'LabFlow', 'about' => null,
        'logo_path' => app_env('SITE_LOGO') ?: 'assets/images/logo.jpeg',
        'favicon_path' => app_env('SITE_FAVICON') ?: 'assets/images/favicon.svg',
        'phone_number' => null, 'whatsapp_number' => null, 'email' => null,
        'address' => null, 'google_map_link' => null, 'working_hours' => null,
        'facebook_url' => null, 'instagram_url' => null, 'youtube_url' => null, 'tiktok_url' => null,
    ];
    if (!db_ready()) {
        return $settings;
    }
    try {
        $result = db()->query('SELECT lab_name, about, logo_path, favicon_path, phone_number, whatsapp_number, email, address, google_map_link, working_hours, facebook_url, instagram_url, youtube_url, tiktok_url FROM lab_settings ORDER BY id ASC LIMIT 1');
        $row = $result->fetch_assoc();
        if ($row) {
            foreach ($settings as $key => $value) {
                if (isset($row[$key]) && $row[$key] !== '') {
                    $settings[$key] = $row[$key];
                }
            }
        }
    } catch (Throwable) {
        // The public template can render with its environment defaults before SQL is imported.
    }
    return $settings;
}

function theme_settings(): array
{
    $theme = DEFAULT_THEME;
    if (!db_ready()) {
        return $theme;
    }
    try {
        $result = db()->query('SELECT primary_light, secondary_light, accent_light, background_light, surface_light, text_light, primary_dark, secondary_dark, accent_dark, background_dark, surface_dark, text_dark FROM theme_settings ORDER BY id ASC LIMIT 1');
        $row = $result->fetch_assoc();
        if ($row) {
            foreach ($theme as $key => $fallback) {
                $theme[$key] = safe_hex($row[$key] ?? '', $fallback);
            }
        }
    } catch (Throwable) {
        // Keep the editable CSS defaults until the theme table is imported.
    }
    return $theme;
}

function active_campaigns(?mysqli $connection = null): array
{
    $connection ??= db();
    $sql = "SELECT c.id, c.name, c.code, c.slug, c.description, c.image_path, c.min_reservations, c.max_reservations, c.sort_order,
                   COUNT(r.id) AS reservation_count
            FROM campaigns c
            LEFT JOIN reservations r ON r.campaign_id = c.id AND r.status <> 'cancelled' AND r.deleted_at IS NULL
            WHERE c.is_active = 1 AND c.closed_at IS NULL AND c.deleted_at IS NULL
            GROUP BY c.id, c.name, c.code, c.slug, c.description, c.image_path, c.min_reservations, c.max_reservations, c.sort_order
            HAVING c.max_reservations IS NULL OR COUNT(r.id) < c.max_reservations
            ORDER BY c.sort_order ASC, c.id DESC";
    return $connection->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function active_branches(?mysqli $connection = null): array
{
    $connection ??= db();
    return $connection->query('SELECT id, code, name, phone_number, email, address, google_map_link, latitude, longitude, sort_order FROM branches WHERE is_active = 1 AND deleted_at IS NULL ORDER BY sort_order ASC, name ASC')->fetch_all(MYSQLI_ASSOC);
}

function campaign_branches(int $campaignId): array
{
    $stmt = db()->prepare('SELECT b.id, b.name, b.address FROM campaign_branches cb JOIN branches b ON b.id = cb.branch_id WHERE cb.campaign_id = ? AND b.is_active = 1 AND b.deleted_at IS NULL ORDER BY b.sort_order ASC, b.name ASC');
    $stmt->bind_param('i', $campaignId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function campaign_is_open(mysqli $connection, int $campaignId, bool $lock = false): ?array
{
    $suffix = $lock ? ' FOR UPDATE' : '';
    $sql = "SELECT c.id, c.name, c.max_reservations,
                   (SELECT COUNT(*) FROM reservations r WHERE r.campaign_id = c.id AND r.status <> 'cancelled' AND r.deleted_at IS NULL) AS reservation_count
            FROM campaigns c WHERE c.id = ? AND c.is_active = 1 AND c.closed_at IS NULL AND c.deleted_at IS NULL" . $suffix;
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('i', $campaignId);
    $stmt->execute();
    $campaign = $stmt->get_result()->fetch_assoc();
    if (!$campaign || ($campaign['max_reservations'] !== null && (int) $campaign['reservation_count'] >= (int) $campaign['max_reservations'])) {
        return null;
    }
    return $campaign;
}

function branch_is_in_campaign(mysqli $connection, int $campaignId, int $branchId): bool
{
    $stmt = $connection->prepare('SELECT 1 FROM campaign_branches cb JOIN branches b ON b.id = cb.branch_id WHERE cb.campaign_id = ? AND cb.branch_id = ? AND b.is_active = 1 AND b.deleted_at IS NULL LIMIT 1');
    $stmt->bind_param('ii', $campaignId, $branchId);
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_row();
}

function submit_public_booking(array $input): string
{
    $connection = db();
    $name = trim(request_string($input, 'full_name'));
    $phoneInput = strtr(trim(request_string($input, 'phone_number')), [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ]);
    $phone = preg_replace('/[^0-9+]/', '', $phoneInput);
    $campaignId = filter_var(request_string($input, 'campaign_id'), FILTER_VALIDATE_INT) ?: 0;
    $branchId = filter_var(request_string($input, 'branch_id'), FILTER_VALIDATE_INT) ?: 0;

    if ($name === '' || mb_strlen($name) > 200 || $branchId < 0) {
        return 'invalid';
    }
    if ($campaignId < 1) {
        return 'campaign_required';
    }
    if (!preg_match('/^\+?[0-9]{7,19}$/', $phone)) {
        return 'phone';
    }
    try {
        $connection->begin_transaction();
        $campaign = campaign_is_open($connection, $campaignId, true);
        if (!$campaign) {
            $connection->rollback();
            return 'campaign';
        }

        $availableBranches = campaign_branches($campaignId);
        if (count($availableBranches) === 1) {
            $branchId = (int) $availableBranches[0]['id'];
        } elseif (count($availableBranches) > 1 && $branchId < 1) {
            $connection->rollback();
            return 'branch_required';
        } elseif (count($availableBranches) === 0) {
            $branchId = 0;
        } elseif (!branch_is_in_campaign($connection, $campaignId, $branchId)) {
            $connection->rollback();
            return 'branch';
        }

        $patientStmt = $connection->prepare('SELECT id FROM patients WHERE phone_number = ? AND deleted_at IS NULL LIMIT 1');
        $patientStmt->bind_param('s', $phone);
        $patientStmt->execute();
        $patient = $patientStmt->get_result()->fetch_assoc();
        if ($patient) {
            $patientId = (int) $patient['id'];
        } else {
            $prefix = $connection->query('SELECT patient_prefix FROM lab_settings ORDER BY id ASC LIMIT 1')->fetch_assoc()['patient_prefix'] ?? 'PAT';
            $prefix = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $prefix) ?: 'PAT';
            $sequence = $connection->query("UPDATE sequences SET current_value = LAST_INSERT_ID(current_value + 1) WHERE name = 'patient_code'");
            if ($sequence->affected_rows !== 1) {
                throw new RuntimeException('patient_sequence_missing');
            }
            $code = $prefix . '-' . str_pad((string) $connection->insert_id, 6, '0', STR_PAD_LEFT);
            $insertPatient = $connection->prepare('INSERT INTO patients (code, name, phone_number) VALUES (?, ?, ?)');
            $insertPatient->bind_param('sss', $code, $name, $phone);
            $insertPatient->execute();
            $patientId = (int) $connection->insert_id;
        }

        if ($branchId > 0) {
            $insertReservation = $connection->prepare("INSERT INTO reservations (patient_id, campaign_id, branch_id, status) VALUES (?, ?, ?, 'pending')");
            $insertReservation->bind_param('iii', $patientId, $campaignId, $branchId);
        } else {
            $insertReservation = $connection->prepare("INSERT INTO reservations (patient_id, campaign_id, branch_id, status) VALUES (?, ?, NULL, 'pending')");
            $insertReservation->bind_param('ii', $patientId, $campaignId);
        }
        $insertReservation->execute();
        $reservationId = (int) $connection->insert_id;
        $history = $connection->prepare("INSERT INTO reservation_history (reservation_id, action, new_status) VALUES (?, 'created', 'pending')");
        $history->bind_param('i', $reservationId);
        $history->execute();
        $connection->commit();
        return 'success';
    } catch (mysqli_sql_exception $exception) {
        $connection->rollback();
        if ($exception->getCode() === 1062) {
            return str_contains($exception->getMessage(), 'active_patient_campaign') ? 'duplicate' : 'error';
        }
        return 'error';
    } catch (Throwable) {
        $connection->rollback();
        return 'error';
    }
}
