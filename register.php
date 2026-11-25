<?php
require_once __DIR__ . '/layout.php';
if (signed_in()) redirect_to('dashboard.php');
$errors = []; $data = ['username'=>'', 'email'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$data, $errors] = validate_form($_POST, [
        'username'=>['required'=>true,'min'=>3,'max'=>40,'username'=>true],
        'email'=>['required'=>true,'email'=>true,'max'=>120],
        'password'=>['required'=>true,'min'=>8,'max'=>100]
    ]);
    if (!$errors) {
        try {
            $q = db()->prepare('INSERT INTO users (username,email,password_hash) VALUES (?,?,?)');
            $q->execute([$data['username'],$data['email'],password_hash($data['password'], PASSWORD_DEFAULT)]);
            redirect_to('login.php?registered=1');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') $errors['email'] = 'Username or email is already registered.';
            else { error_log($e->getMessage()); $errors['email'] = 'Could not register. Try again.'; }
        }
    }
}
page_header('Create account'); ?>
<p>Register to use the contact portal.</p>
<form method="post" class="card narrow">
<input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
<label>Username <input name="username" maxlength="40" required value="<?= h($data['username'] ?? '') ?>"></label><small class="error"><?= h($errors['username'] ?? '') ?></small>
<label>Email <input name="email" type="email" maxlength="120" required value="<?= h($data['email'] ?? '') ?>"></label><small class="error"><?= h($errors['email'] ?? '') ?></small>
<label>Password (8+ characters) <input name="password" type="password" minlength="8" required></label><small class="error"><?= h($errors['password'] ?? '') ?></small>
<button>Create account</button> <a href="login.php">Already registered?</a>
</form>
<?php page_footer();
