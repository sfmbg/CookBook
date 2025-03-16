<?php
function readJson($file) {
    if (!file_exists($file)) {
        return [];
    }
    $jsonContent = file_get_contents($file);
    return json_decode($jsonContent, true);
}
function writeJson($file, $data) {
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); 
    file_put_contents($file, $jsonContent); 
}
function userExists($username, $users) {
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return true;
        }
    }
    return false;
}
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
function validateUserData($username, $password, $confirmPassword, $role) {
    $errors = [];
    if (empty($username)) {
        $errors['username'] = 'Uživatelské jméno je povinné.';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Uživatelské jméno je příliš krátké (min. 3 znaky).';
    }
    if (empty($password)) {
        $errors['password'] = 'Heslo je povinné.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Heslo je příliš krátké (min. 6 znaků).';
    }
    if ($confirmPassword !== $password) {
        $errors['confirm_password'] = 'Hesla se neshodují.';
    }
    if ($role !== 'user' && $role !== 'admin') {
        $errors['role'] = 'Neplatná role.';
    }
    return $errors; 
}
?>
