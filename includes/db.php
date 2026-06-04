<?php
$conn = new mysqli('localhost', 'root', '', 'shetkari_mitra');
if ($conn->connect_error) die('DB Error: ' . $conn->connect_error);
$conn->set_charset('utf8mb4');
?>
