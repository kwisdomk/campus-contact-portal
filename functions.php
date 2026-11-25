<?php
require_once __DIR__ . '/config.php';

function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirect_to(string $page): never {
    header('Location: ' . $page);
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Invalid request token. Go back and reload the form.');
    }
}

// Validate form fields against simple rules.
function validate_form(array $data, array $rules): array {
    $clean = []; $errors = [];
    foreach ($rules as $key => $rule) {
        $value = trim((string)($data[$key] ?? ''));
        if ($value === '' && ($rule['required'] ?? false)) {
            $errors[$key] = 'This field is required.';
        } elseif ($value !== '' && isset($rule['max']) && (function_exists('mb_strlen') ? mb_strlen($value) : strlen($value)) > $rule['max']) {
            $errors[$key] = 'Too long (max ' . $rule['max'] . ' characters).';
        } elseif ($value !== '' && isset($rule['min']) && (function_exists('mb_strlen') ? mb_strlen($value) : strlen($value)) < $rule['min']) {
            $errors[$key] = 'Too short (min ' . $rule['min'] . ' characters).';
        } elseif ($value !== '' && ($rule['email'] ?? false) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$key] = 'Enter a valid email address.';
        } elseif ($value !== '' && ($rule['username'] ?? false) && !preg_match('/^[A-Za-z0-9_]+$/', $value)) {
            $errors[$key] = 'Use letters, numbers or underscores only.';
        }
        $clean[$key] = $value;
    }
    return [$clean, $errors];
}

function format_date(string $mysqlDate): string {
    return date('F j, Y', strtotime($mysqlDate));
}

function safe_csv_value(string $value): string {
    return preg_match('/^[\x00-\x20]*[=+\-@]/u', $value) ? "'" . $value : $value;
}

function signed_in(): bool {
    return isset($_SESSION['user_id']);
}

function require_login(): void {
    if (!signed_in()) redirect_to('login.php');
    if (time() - (int)($_SESSION['last_activity'] ?? time()) > 1800) {
        $_SESSION = [];
        session_regenerate_id(true);
        redirect_to('login.php?expired=1');
    }
    $_SESSION['last_activity'] = time();
}

function user_id(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

function log_event(int $userId, string $event): bool {
    $line = date('c') . ' user=' . $userId . ' ' . str_replace(["\r", "\n"], ' ', $event) . PHP_EOL;
    return file_put_contents(__DIR__ . '/data/activity.log', $line, FILE_APPEND | LOCK_EX) !== false;
}

function subjects(): array {
    return db()->query('SELECT id, label FROM categories ORDER BY label')->fetchAll();
}

function message_rules(): array {
    return [
        'name' => ['required'=>true, 'max'=>100],
        'email' => ['required'=>true, 'email'=>true, 'max'=>120],
        'subject_id' => ['required'=>true],
        'message' => ['required'=>true, 'max'=>2000]
    ];
}

function validate_subject(array $clean, array &$errors, array $categories): void {
    $validIds = array_map(fn($r) => (string)$r['id'], $categories);
    if (!in_array($clean['subject_id'], $validIds, true)) {
        $errors['subject_id'] = 'Select a valid subject.';
    }
}
