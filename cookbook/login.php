<?php
session_start();
include 'fce.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$username = $_POST['username'] ?? ''; 
if (isset($_SESSION['user'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $error = 'Už jste přihlášený. Nejdříve se prosím odhlaste.';
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Neplatný CSRF token, zkuste to prosím znovu.';
        } else {

            $username = trim($_POST['username']); 
            $password = trim($_POST['password']);

            $users = readJson('users.json');

            $userFound = false;
            foreach ($users as $user) {
                if ($user['username'] === $username) {
                    $userFound = true;
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user'] = $username;
                        header('Location: profil.php');
                        exit;
                    } else {
                        $error = 'Špatné heslo.';
                    }
                    break;
                }
            }

            if (!$userFound) {
                $error = 'Uživatelské jméno neexistuje.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení</title>
    <link rel="stylesheet" href="form.css">
    <link rel="stylesheet" href="print.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Domů</a></li>
                <li><a href="registration.php">Registrace</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Přihlášení</h1>
        <?php if (isset($error)): ?>
            <p class="red">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <!-- 3) Skrytý CSRF token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <label for="username">Uživatelské jméno:</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                required
                value="<?= htmlspecialchars($username) ?>"
                <?= isset($_SESSION['user']) ? 'disabled' : '' ?>
            >
            <span id="usernameFeedback" class="red"></span>

            <label for="password">Heslo:</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                <?= isset($_SESSION['user']) ? 'disabled' : '' ?>
            >
            <button type="submit" <?= isset($_SESSION['user']) ? 'disabled' : '' ?>>
                Přihlásit se
            </button>
        </form>
        <?php if (isset($_SESSION['user'])): ?>
            <p>Jste již přihlášeni jako <span class="strong"><?= htmlspecialchars($_SESSION['user']) ?></span>.</p>
            <a href="logout.php">
                <button class="logoutbutton">Odhlásit se</button>
            </a>
        <?php endif; ?>
        <p>Nemáte účet? <a href="registration.php">Zaregistrujte se zde</a>.</p>
    </main>
    <footer>
        <p>&copy; 2025 Cookbook</p>
    </footer>
</body>
</html>

