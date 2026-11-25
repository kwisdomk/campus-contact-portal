<?php
require_once __DIR__ . '/functions.php';
function page_header(string $title): void {
    $loggedIn = signed_in();
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $folder = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $base = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . $host . $folder;
    $description = 'Simple PHP and MySQL contact portal.';
    ?>
<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($title) ?> | Campus contact</title>
<meta name="description" content="<?= h($description) ?>">
<meta property="og:title" content="<?= h($title) ?> | Campus contact">
<meta property="og:description" content="<?= h($description) ?>">
<meta property="og:image" content="<?= h($base . '/assets/logo.svg') ?>">
<meta property="og:type" content="website">
<link rel="stylesheet" href="assets/style.css">
</head><body>
<header class="site-header"><div class="wrap topbar">
<a class="brand" href="<?= $loggedIn ? 'dashboard.php' : 'login.php' ?>">Campus contact</a>
<?php if ($loggedIn): ?><nav aria-label="Main navigation">
<a href="dashboard.php">Dashboard</a><a href="profile.php">Profile</a><a href="contact.php">Contact</a><a href="submissions.php">Submissions</a><a href="info.php">Info</a><a href="logout.php">Logout</a>
</nav><?php endif; ?></div></header>
<main class="wrap"><h1><?= h($title) ?></h1>
<?php
}
function page_footer(): void { ?>
</main>
<footer class="footer"><div class="wrap">Campus contact &copy; <?= date('Y') ?></div></footer>
</body></html>
<?php }
