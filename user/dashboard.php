<?php
session_start();

// proteksi halaman user
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

header("Location: ../index.php");
exit;