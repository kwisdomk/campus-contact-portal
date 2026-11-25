<?php
require_once __DIR__ . '/layout.php';
if (signed_in()) redirect_to('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $q = db()->prepare('SELECT id, username, password_hash FROM users WHERE email=? LIMIT 1');
    $q->execute([$email]); $user = $q->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['last_activity'] = time();
        setcookie('last_login', date('Y-m-d H:i:s'), [
            'expires'=>time()+30*86400, 'path'=>'/',
            'httponly'=>true, 'samesite'=>'Lax',
            'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
        ]);
        log_event(user_id(), 'login');
        redirect_to('dashboard.php');
    }
    $error = 'Incorrect email or password.';
}
page_header('Log in'); ?>
<?php if (isset($_GET['registered'])): ?><p class="notice">Account created. Please log in.</p><?php endif; ?>
<?php if (isset($_GET['expired'])): ?><p class="notice">Session expired. Please log in again.</p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>
<form method="post" class="card narrow">
<input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
<label>Email <input name="email" type="email" maxlength="120" required></label>
<label>Password <input name="password" type="password" required></label>
<button>Log in</button> <a href="register.php">Create account</a>
</form>
<?php page_footer();
