<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

if (isset($_GET['id'])) {
    $id = sanitize($_GET['id']);
    $result = $conn->query("SELECT * FROM akun_ff WHERE id = $id");
    echo json_encode($result->fetch_assoc());
}
?>