<?php
require_once __DIR__ . '/layout.php';
require_login();
$categories = subjects();
$errors = []; $data = ['name'=>'','email'=>'','subject_id'=>'','message'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$data,$errors] = validate_form($_POST, message_rules());
    validate_subject($data, $errors, $categories);
    if (!$errors) {
        $insert = db()->prepare('INSERT INTO messages (user_id,category_id,name,email,message) VALUES (?,?,?,?,?)');
        $insert->execute([user_id(),(int)$data['subject_id'],$data['name'],$data['email'],$data['message']]);
        log_event(user_id(), 'message submitted');
        redirect_to('submissions.php?added=1');
    }
}
page_header('Contact'); ?>
<p>Send a message using the form below.</p>
<form method="post" class="card narrow">
<input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
<label>Name <input name="name" maxlength="100" required value="<?= h($data['name']) ?>"></label><small class="error"><?= h($errors['name'] ?? '') ?></small>
<label>Email <input type="email" name="email" maxlength="120" required value="<?= h($data['email']) ?>"></label><small class="error"><?= h($errors['email'] ?? '') ?></small>
<label>Subject <select name="subject_id" required><option value="">Choose subject</option>
<?php foreach ($categories as $subject): ?><option value="<?= (int)$subject['id'] ?>" <?= $data['subject_id']===(string)$subject['id'] ? 'selected' : '' ?>><?= h($subject['label']) ?></option><?php endforeach; ?>
</select></label><small class="error"><?= h($errors['subject_id'] ?? '') ?></small>
<label>Message <textarea name="message" rows="5" maxlength="2000" required><?= h($data['message']) ?></textarea></label><small class="error"><?= h($errors['message'] ?? '') ?></small>
<button>Send message</button>
</form>
<?php page_footer();
