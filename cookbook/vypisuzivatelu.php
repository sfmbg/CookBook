<?php
session_start();
include 'fce.php'; 

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['user'];
$users = readJson('users.json');
$currentUser = null;

foreach ($users as $user) {
    if ($user['username'] === $username) {
        $currentUser = $user;
        break;
    }
}

if (!$currentUser || $currentUser['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_to_admin'])) {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Chyba CSRF: Neplatný token!');
    }
    $targetUser = $_POST['username'];

    foreach ($users as &$u) {
        if ($u['username'] === $targetUser && $u['role'] === 'user') {
            $u['role'] = 'admin';
            file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT)); 
            header('Location: vypisuzivatelu.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Výpis uživatelů</title>
    <link rel="stylesheet" href="vypisuzivatelu.css">
    <link rel="stylesheet" href="print.css">
</head>
<body>
<header>
    <nav>
        <ul>
            <li><a href="index.php">Domů</a></li>
            <li><a href="profil.php">Profil</a></li>
            <li><a href="logout.php">Odhlásit se</a></li>
        </ul>
    </nav>
</header>
<main>
    <h1>Výpis uživatelů</h1>
    <table>
        <thead>
            <tr>
                <th>Uživatelské jméno</th>
                <th>Role</th>
                <th>Akce</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <?php if ($user['role'] === 'user'): ?>
                            <form method="post" action="vypisuzivatelu.php">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                
                                <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                <button type="submit" name="change_to_admin">Admin</button>
                            </form>
                        <?php else: ?>
                            Nelze
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
<footer>
    <p>&copy; 2025 Cookbook</p>
</footer>
</body>
</html>