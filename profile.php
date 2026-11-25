<?php
require_once __DIR__ . '/layout.php';
require_login();
$errors = []; $notice = '';
$q = db()->prepare('SELECT username, email, created_at FROM users WHERE id=?');
$q->execute([user_id()]); $profile = $q->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$data,$errors] = validate_form($_POST, [
        'username'=>['required'=>true,'min'=>3,'max'=>40,'username'=>true],
        'email'=>['required'=>true,'email'=>true,'max'=>120]
    ]);
    if (!$errors) {
        try {
            $u = db()->prepare('UPDATE users SET username=?,email=? WHERE id=?');
            $u->execute([$data['username'],$data['email'],user_id()]);
            $_SESSION['username'] = $data['username'];
            log_event(user_id(), 'profile updated');
            $notice = 'Profile saved.';
        } catch (PDOException $e) {
            $errors['email'] = $e->getCode()==='23000' ? 'Username or email already used.' : 'Could not update profile.';
            if ($e->getCode() !== '23000') error_log($e->getMessage());
        }
    }
    if (!$errors) {
        $q->execute([user_id()]); $profile = $q->fetch();
    } else {
        $profile['username'] = $data['username'];
        $profile['email'] = $data['email'];
    }
}
page_header('Profile'); ?>
<p>Member since <?= h(date('F j, Y',strtotime($profile['created_at']))) ?></p>
<?php if ($notice): ?><p class="notice"><?= h($notice) ?></p><?php endif; ?>
<form method="post" class="card narrow"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
<label>Username <input name="username" required maxlength="40" value="<?= h($profile['username']) ?>"></label><small class="error"><?= h($errors['username'] ?? '') ?></small>
<label>Email <input type="email" name="email" required maxlength="120" value="<?= h($profile['email']) ?>"></label><small class="error"><?= h($errors['email'] ?? '') ?></small>
<button>Update profile</button></form>
<?php page_footer();
