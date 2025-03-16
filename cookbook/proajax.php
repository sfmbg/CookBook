<?php
session_start();
include 'fce.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Neplatný CSRF token.']);
        exit;
    }
    $username = trim($_POST['username'] ?? '');

    if (empty($username)) {
        echo json_encode(['status' => 'error', 'message' => 'Uživatelské jméno je povinné.']);
        exit;
    } elseif (strlen($username) < 3) {
        echo json_encode(['status' => 'error', 'message' => 'Uživatelské jméno je příliš krátké.']);
        exit;
    }
    $users = readJson('users.json');
    if (userExists($username, $users)) {
        echo json_encode(['status' => 'exists', 'message' => 'Uživatelské jméno již existuje.']);
    } else {
        echo json_encode(['status' => 'available', 'message' => 'Uživatelské jméno je dostupné.']);
    }
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Neplatná metoda požadavku.']);
    exit;
}
?>
