<?php
session_start();
include 'fce.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$postId = $_GET['id'];
$posts = readJson('prispevky.json');
$post = null;
foreach ($posts as $item) {
    if ($item['id'] === $postId) {
        $post = $item;
        break;
    }
}

if (!$post) {
    header('Location: index.php');
    exit;
}
if ($_SESSION['user'] !== $post['username_autor'] && $_SESSION['role'] !== 'admin') {
    header('Location: index.php'); // Přesměruje uživatele bez přístupových práv
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nazev_prispevku'], $_POST['kategorie'])) {
    $nazevPrispevku = trim($_POST['nazev_prispevku']); // Název příspěvku
    $kategorie = trim($_POST['kategorie']); // Kategorie příspěvku

    if ($nazevPrispevku === '' || ($kategorie !== 'sladké' && $kategorie !== 'slané')) {
        $error = 'Vyplňte všechna pole správně.'; // Zobrazení chybové hlášky
    } else {
        foreach ($posts as &$item) {
            if ($item['id'] === $postId) { // Najde odpovídající příspěvek
                $item['nazev_prispevku'] = $nazevPrispevku; // Aktualizuje název
                $item['kategorie'] = $kategorie; // Aktualizuje kategorii
                break;
            }
        }
        writeJson('prispevky.json', $posts); // Uloží změny zpět do JSON
        header('Location: index.php'); // Přesměruje na hlavní stránku
        exit;
    }
}
if (isset($_POST['delete'])) { // Pokud uživatel klikne na tlačítko "Odstranit"
    foreach ($posts as $key => $item) {
        if ($item['id'] === $postId) { // Najde odpovídající příspěvek
            unset($posts[$key]); // Odstraní příspěvek
            break;
        }
    }
    writeJson('prispevky.json', array_values($posts)); // Přepíše JSON bez smazaného příspěvku
    header('Location: index.php'); // Přesměruje zpět na hlavní stránku
    exit;
}
?>
