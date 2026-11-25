<?php
require_once __DIR__ . '/layout.php';
require_login();
$pdo = db(); $categories = subjects();
$categoryNames = array_column($categories, 'id', 'label');
$error = ''; $notice = '';

// Handle delete, update, and CSV import actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id) {
            $q = $pdo->prepare('DELETE FROM messages WHERE id=? AND user_id=?');
            $q->execute([$id,user_id()]);
            log_event(user_id(), 'message deleted');
        }
        redirect_to('submissions.php?deleted=1');
    }
    if ($action === 'update') {
        [$data,$errors] = validate_form($_POST, message_rules());
        validate_subject($data, $errors, $categories);
        $id = filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT);
        if (!$id) $errors['id']='Invalid message ID.';
        if (!$errors) {
            $q = $pdo->prepare('UPDATE messages SET category_id=?,name=?,email=?,message=? WHERE id=? AND user_id=?');
            $q->execute([(int)$data['subject_id'],$data['name'],$data['email'],$data['message'],$id,user_id()]);
            log_event(user_id(), 'message updated');
            redirect_to('submissions.php?updated=1');
        }
        $error = implode(' ', $errors);
        if ($id) $invalidEdit = ['id'=>$id,'name'=>$data['name'],'email'=>$data['email'],'category_id'=>$data['subject_id'],'message'=>$data['message']];
    }
    if ($action === 'import') {
        if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK || $_FILES['csv']['size'] > 1024*1024 || strtolower(pathinfo($_FILES['csv']['name'],PATHINFO_EXTENSION)) !== 'csv') {
            $error = 'Choose a CSV file under 1 MB.';
        } else {
            $file = fopen($_FILES['csv']['tmp_name'], 'r');
            if ($file === false) $error = 'Could not open CSV file.';
            else {
                $heading = fgetcsv($file, 0, ',', '"', '');
                if ($heading !== ['name','email','subject','message']) $error='CSV columns must be: name,email,subject,message';
                else {
                    $count=0; $skipped=0; $q=$pdo->prepare('INSERT INTO messages (user_id,category_id,name,email,message) VALUES (?,?,?,?,?)');
                    while (($line=fgetcsv($file, 0, ',', '"', '')) !== false && $count+$skipped < 500) {
                        if (count($line) !== 4) { $skipped++; continue; }
                        [$name,$email,$subject,$message] = array_map('trim',$line);
                        [$valid,$issues] = validate_form(compact('name','email','message'), [
                            'name'=>['required'=>true,'max'=>100],
                            'email'=>['required'=>true,'max'=>120,'email'=>true],
                            'message'=>['required'=>true,'max'=>2000]
                        ]);
                        if ($issues || !array_key_exists($subject, $categoryNames)) { $skipped++; continue; }
                        $q->execute([user_id(),(int)$categoryNames[$subject],$name,$email,$message]);
                        $count++;
                    }
                    log_event(user_id(), 'CSV imported rows=' . $count);
                    $notice = "Imported $count message(s); skipped $skipped invalid row(s).";
                }
                fclose($file);
            }
        }
    }
}

// Export user messages as CSV.
if (($_GET['export'] ?? '') === 'csv') {
    $q=$pdo->prepare('SELECT m.name,m.email,c.label AS subject,m.message FROM messages m JOIN categories c ON c.id=m.category_id WHERE m.user_id=? ORDER BY m.created_at DESC');
    $q->execute([user_id()]);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="my_messages.csv"');
    $out=fopen('php://output','w');
    fputcsv($out,['name','email','subject','message'], ',', '"', '');
    while($r=$q->fetch()) {
        $safe=array_map(fn($value) => safe_csv_value((string)$value), array_values($r));
        fputcsv($out,$safe, ',', '"', '');
    }
    fclose($out);
    exit;
}

$edit = $invalidEdit ?? null;
if ($edit === null && isset($_GET['edit'])) {
    $editId=filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($editId) {
        $q=$pdo->prepare('SELECT id,name,email,category_id, message FROM messages WHERE id=? AND user_id=?');
        $q->execute([$editId,user_id()]); $edit=$q->fetch();
    }
}
$searchText=trim((string)($_GET['q'] ?? ''));
$term=function_exists('mb_substr') ? mb_substr($searchText,0,100) : substr($searchText,0,100);
$page=max(1,(int)($_GET['page'] ?? 1));
$sort=($_GET['sort'] ?? 'newest');
switch ($sort) {
    case 'oldest': $order='m.created_at ASC, m.id ASC'; break;
    case 'subject': $order='c.label ASC, m.created_at DESC'; break;
    default: $sort='newest'; $order='m.created_at DESC, m.id DESC';
}
$filter='m.user_id=:uid AND (m.name LIKE :search1 OR m.email LIKE :search2 OR c.label LIKE :search3 OR m.message LIKE :search4)';
$count=$pdo->prepare('SELECT COUNT(*) FROM messages m JOIN categories c ON c.id=m.category_id WHERE '.$filter);
$bind=[':uid'=>user_id(),':search1'=>'%'.$term.'%',':search2'=>'%'.$term.'%',':search3'=>'%'.$term.'%',':search4'=>'%'.$term.'%'];
$count->execute($bind); $total=(int)$count->fetchColumn(); $perPage=5;
$pages=max(1,(int)ceil($total/$perPage)); $page=min($page,$pages);
$query='SELECT m.id,m.name,m.email,c.label AS subject,m.message,m.created_at FROM messages m INNER JOIN categories c ON c.id=m.category_id WHERE '.$filter.' ORDER BY '.$order.' LIMIT :take OFFSET :skip';
$q=$pdo->prepare($query);
foreach ($bind as $k=>$v) $q->bindValue($k,$v,$k===':uid'?PDO::PARAM_INT:PDO::PARAM_STR);
$q->bindValue(':take',$perPage,PDO::PARAM_INT); $q->bindValue(':skip',($page-1)*$perPage,PDO::PARAM_INT);
$q->execute(); $messages=$q->fetchAll();
page_header('Submissions'); ?>
<?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>
<?php if ($notice): ?><p class="notice"><?= h($notice) ?></p><?php endif; ?>
<?php if (isset($_GET['added'])): ?><p class="notice">Message saved.</p><?php endif; ?>
<?php if (isset($_GET['updated'])): ?><p class="notice">Message updated.</p><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><p class="notice">Message deleted.</p><?php endif; ?>
<?php if ($edit): ?>
<section class="card"><h2>Edit message #<?= (int)$edit['id'] ?></h2><form method="post" action="submissions.php" class="stack">
<input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
<label>Name <input name="name" maxlength="100" required value="<?= h($edit['name']) ?>"></label>
<label>Email <input type="email" name="email" maxlength="120" required value="<?= h($edit['email']) ?>"></label>
<label>Subject <select name="subject_id" required><?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$c['id']===(int)$edit['category_id']?'selected':'' ?>><?= h($c['label']) ?></option><?php endforeach; ?></select></label>
<label>Message <textarea name="message" rows="4" maxlength="2000" required><?= h($edit['message']) ?></textarea></label>
<div><button>Save changes</button> <a href="submissions.php">Cancel</a></div></form></section>
<?php endif; ?>
<section class="card"><form method="get" class="filters"><label>Search <input name="q" value="<?= h($term) ?>" placeholder="Name, email, subject or message"></label><label>Sort <select name="sort"><option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest</option><option value="oldest" <?= $sort==='oldest'?'selected':'' ?>>Oldest</option><option value="subject" <?= $sort==='subject'?'selected':'' ?>>Subject</option></select></label><button>Filter</button></form></section>
<div class="toolbar"><span><?= $total ?> matching message(s)</span><a href="submissions.php?export=csv">Export my CSV</a> <a href="contact.php">Add message</a></div>
<div class="scroll"><table><thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Created</th><th>Actions</th></tr></thead><tbody>
<?php foreach($messages as $m): ?><tr><td><?= h($m['name']) ?></td><td><?= h($m['email']) ?></td><td><?= h($m['subject']) ?></td><td><?= nl2br(h($m['message'])) ?></td><td><?= h(format_date($m['created_at'])) ?></td><td><a href="submissions.php?edit=<?= (int)$m['id'] ?>">Edit</a>
<form method="post" class="inline" onsubmit="return confirm('Delete this message?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="link danger">Delete</button></form></td></tr><?php endforeach; ?>
<?php if (!$messages): ?><tr><td colspan="6">No messages found.</td></tr><?php endif; ?></tbody></table></div>
<div class="pages"><?php for($n=1;$n<=$pages;$n++): ?><a class="<?= $n===$page?'active':'' ?>" href="?q=<?= urlencode($term) ?>&sort=<?= urlencode($sort) ?>&page=<?= $n ?>"><?= $n ?></a><?php endfor; ?></div>
<section class="card"><h2>Import CSV</h2><p>Download a CSV first to see the format. Columns: <code>name,email,subject,message</code>. Max 1 MB / 500 rows. Invalid rows are skipped.</p>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="import"><input type="file" name="csv" accept=".csv,text/csv" required> <button>Import</button></form></section>
<?php page_footer();
