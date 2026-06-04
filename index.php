<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(isset($_SESSION['admin']))    { header('Location: dashboard.php'); exit; }
if(isset($_SESSION['customer'])) { header('Location: customer.php');  exit; }
header('Location: login.php'); exit;
?>
