<?php
require_once __DIR__ . '/layout.php';
require_login();
$rows = db()->prepare('SELECT c.label, COUNT(m.id) AS total FROM categories c LEFT JOIN messages m ON m.category_id=c.id AND m.user_id=? GROUP BY c.id,c.label ORDER BY total DESC,c.label');
$rows->execute([user_id()]); $report = $rows->fetchAll();
$greeting = trim((string)($_SESSION['username'] ?? 'visitor'));
// Format username for display.
$words = explode('_', str_replace('-', '_', $greeting));
$displayName = ucwords(implode(' ', $words));
$shortName = substr($displayName, 0, 20);
$categories = subjects();
$labels = array_filter(array_column($categories, 'label'));
$who = db()->prepare("SELECT CONCAT(username, ' (', email, ')') FROM users WHERE id=?");
$who->execute([user_id()]);
page_header('Info'); ?>
<section class="card"><h2>Session and cookie</h2>
<p>Signed in as <?= h($shortName) ?> (username length: <?= strlen($greeting) ?>).</p>
<p>Last login cookie: <?= h($_COOKIE['last_login'] ?? 'Not set yet') ?></p>
<p>Subjects available: <?= count($labels) ?>. Account: <?= h($who->fetchColumn()) ?>.</p>
<p>Login state stays on the server in <code>$_SESSION</code>; the cookie only stores the last login date.</p></section>
<section class="card"><h2>Messages by subject</h2>
<table><thead><tr><th>Subject</th><th>Messages</th></tr></thead><tbody>
<?php foreach ($report as $r): ?><tr><td><?= h($r['label']) ?></td><td><?= (int)$r['total'] ?></td></tr><?php endforeach; ?>
</tbody></table>
<p>Server time: <?= h(db()->query('SELECT NOW()')->fetchColumn()) ?>.</p></section>
<?php page_footer();
