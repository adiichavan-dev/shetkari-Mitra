<?php
if(session_status()===PHP_SESSION_NONE) session_start();
$depth = substr_count($_SERVER['PHP_SELF'], '/');
if(!isset($_SESSION['admin'])){
    header('Location: '.($depth > 2 ? '../login.php' : 'login.php'));
    exit;
}
$admin = $_SESSION['admin'];
