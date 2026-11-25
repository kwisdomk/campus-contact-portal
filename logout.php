<?php
require_once __DIR__ . '/functions.php';
if (signed_in()) log_event(user_id(), 'logout');
$_SESSION = [];
$params = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires'=>time()-3600, 'path'=>$params['path'],
    'domain'=>$params['domain'], 'secure'=>$params['secure'],
    'httponly'=>true, 'samesite'=>'Lax'
]);
session_destroy();
redirect_to('login.php');
