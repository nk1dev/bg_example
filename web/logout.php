<?
 session_start();
 unset($_SESSION['user_id']);
 unset($_SESSION['username']);
 unset($_SESSION['password']);
 setcookie("test", null, -1, '/');
 session_destroy();
 header('Location: https://bloodgodz.com');
 exit;