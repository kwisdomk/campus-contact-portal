<?php
require_once __DIR__ . '/layout.php';
require_login();
$stats = db()->prepare('SELECT COUNT(*) AS total, MAX(created_at) AS newest FROM messages WHERE user_id=?');
$stats->execute([user_id()]); $stat = $stats->fetch();
$users = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$recent = db()->prepare('SELECT m.name, c.label AS subject, m.created_at FROM messages m JOIN categories c ON c.id=m.category_id WHERE m.user_id=? ORDER BY m.created_at DESC LIMIT 5');
$recent->execute([user_id()]);
$recentRows = $recent->fetchAll();
page_header('Dashboard'); ?>
<p>Welcome, <strong><?= h($_SESSION['username']) ?></strong>. Today is <?= h(date('F j, Y')) ?>.</p>
<section class="stats"><div class="card"><strong><?= (int)$stat['total'] ?></strong><span>Your submissions</span></div><div class="card"><strong><?= $users ?></strong><span>Registered users</span></div><div class="card"><strong><?= h($stat['newest'] ? format_date($stat['newest']) : 'None') ?></strong><span>Latest submission</span></div></section>
<h2>Recent messages</h2><div class="card"><ul class="clean">
<?php if ($recentRows): foreach ($recentRows as $r): ?><li><?= h($r['name']) ?> - <?= h($r['subject']) ?> <small>(<?= h($r['created_at']) ?>)</small></li><?php endforeach; else: ?><li>No messages yet.</li><?php endif; ?>
</ul><a href="contact.php">Add a new message</a></div>
<?php page_footer();
