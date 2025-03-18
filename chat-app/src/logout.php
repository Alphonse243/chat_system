<?php 
if (isset($_SESSION['user_id'])) {
    session_destroy($_SESSION['user_id']);
    header('Location: login.php');
    exit;
}

