<?php 
session_start();
include 'fce.php';

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

if (!$currentUser) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="profil.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Domů</a></li>
                <li><a href="profil.php">Profil</a></li>
                <li><a href="mojeprispevky.php">Moje recepty</a></li>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <li><a href="vypisuzivatelu.php">Výpis uživatelů</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Odhlásit se</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Váš profil</h1>
        <img src="pfp.png" alt="profilový obrázek">
        <p><span class="strong">Uživatelské jméno:</span> <?= htmlspecialchars($currentUser['username']) ?></p>
        <p><span class="strong">Role:</span> <?= htmlspecialchars($currentUser['role']) ?></p>

        <form method="post" action="logout.php">
            <button type="submit" name="logout">Odhlásit se</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2025 Cookbook</p>
    </footer>
</body>
</html>

