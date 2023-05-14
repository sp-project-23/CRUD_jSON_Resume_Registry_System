<?php
session_start();
unset($_SESSION['name']);
unset($_SESSION['user_id']);
$_SESSION['success'] = 'You are logged out successfully';
header('Location: index.php');
?>