<?php
session_start();
include 'fce.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$username = '';
$password = '';
$confirm_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Neplatný CSRF token, zkuste to prosím znovu.';
    } else {
        $username = trim($_POST['username']); 
        $password = trim($_POST['password']); 
        $confirmPassword = trim($_POST['confirm_password']); 
        $role = 'user'; 

        $users = readJson('users.json');

        if (empty($username)) {
            $errors['username'] = 'Uživatelské jméno je povinné.';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Uživatelské jméno je příliš krátké (min. 3 znaky).';
        } elseif (strlen($username) > 20) {
            $errors['username'] = 'Uživatelské jméno je příliš dlouhé (max. 20 znaků).';
        } elseif (userExists($username, $users)) {
            $errors['username'] = 'Uživatelské jméno již existuje.';
        }

        if (empty($password)) {
            $errors['password'] = 'Heslo je povinné.';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Heslo je příliš krátké (min. 6 znaků).';
        } elseif (strlen($password) > 20) {
            $errors['password'] = 'Heslo je příliš dlouhé (max. 20 znaků).';
        }

        if ($confirmPassword !== $password) {
            $errors['confirm_password'] = 'Hesla se neshodují.';
        } elseif (strlen($confirmPassword) > 20) {
            $errors['confirm_password'] = 'Potvrzení hesla je příliš dlouhé (max. 20 znaků).';
        }

        if (empty($errors)) {
            $hashedPassword = hashPassword($password);
            $newUser = [
                'username' => $username,
                'password' => $hashedPassword,
                'role' => $role
            ];
            $users[] = $newUser;
            writeJson('users.json', $users); 

            $_SESSION['user'] = $username;
            header('Location: index.php'); 
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
    <title>Registrace</title>
    <link rel="stylesheet" href="form.css">
    <link rel="stylesheet" href="print.css">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
</head>
<body>
<header>
    <nav>
        <ul>
            <li><a href="index.php">Domů</a></li>
            <li><a href="login.php">Přihlášení</a></li>
        </ul>
    </nav>
</header>
<main>
    <h1>Registrace</h1>
    <form method="post" action="registration.php" id="registrationForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <?php if (!empty($errors['csrf'])): ?>
            <div class="error">
                <?= htmlspecialchars($errors['csrf']) ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="username">Uživatelské jméno:</label>
            <input type="text" id="username" name="username"
                   value="<?= htmlspecialchars($username) ?>" required maxlength="20">
            <?php if (isset($errors['username'])): ?>
                <span class="error"> <?= htmlspecialchars($errors['username']) ?> </span>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password">Heslo:</label>
            <input type="password" id="password" name="password" required maxlength="20">
            <?php if (isset($errors['password'])): ?>
                <span class="error"> <?= htmlspecialchars($errors['password']) ?> </span>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="confirm_password">Znovu heslo:</label>
            <input type="password" id="confirm_password" name="confirm_password" required maxlength="20">
            <?php if (isset($errors['confirm_password'])): ?>
                <span class="error"> <?= htmlspecialchars($errors['confirm_password']) ?> </span>
            <?php endif; ?>
        </div>
        <button type="submit" id="submitBtn">Zaregistrovat se</button>
    </form>
</main>
<footer>
    <p>&copy; 2025 Cookbook</p>
</footer>
</body>
</html>
