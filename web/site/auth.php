<?php
session_start();
if (session_status() === PHP_SESSION_NONE) {
    header('Location: index.php');
}
?>