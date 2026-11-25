<?php
require_once __DIR__ . '/functions.php';
redirect_to(signed_in() ? 'dashboard.php' : 'login.php');
