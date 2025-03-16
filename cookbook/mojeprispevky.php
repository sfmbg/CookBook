<?php 
session_start();
include 'fce.php'; 
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$allPosts = readJson('prispevky.json');
$loggedUser = $_SESSION['user'];
$myPosts = array_filter($allPosts, function($post) use ($loggedUser) {
    return isset($post['author']) && $post['author'] === $loggedUser;
});
usort($myPosts, function($a, $b) {
    return strtotime($b['post_time']) - strtotime($a['post_time']);
});

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Chyba CSRF: Neplatný token!');
    }

    $editId = $_POST['edit_id'];
    $newTitle = trim($_POST['title']);
    $newCategory = $_POST['category'];
    $newIngredients = trim($_POST['ingredients']);
    $newContent = trim($_POST['content']);

    foreach ($allPosts as &$post) {
        if ($post['id'] === $editId && $post['author'] === $loggedUser) {
            $post['title'] = $newTitle;
            $post['category'] = $newCategory;
            $post['ingredients'] = $newIngredients;
            $post['content'] = $newContent;

            writeJson('prispevky.json', $allPosts);

            header('Location: mojeprispevky.php');
            exit;
        }
    }
}

$postsPerPage = 5;  
$totalPosts = count($myPosts);
$totalPages = $totalPosts > 0 ? ceil($totalPosts / $postsPerPage) : 1;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($page, $totalPages));

$startIndex = ($page - 1) * $postsPerPage;
$myPosts = array_values($myPosts);
$displayedPosts = array_slice($myPosts, $startIndex, $postsPerPage);
$editPostId = isset($_GET['edit_id']) ? $_GET['edit_id'] : null;
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moje recepty</title>
    <link rel="stylesheet" href="mojeprispevky.css">
    <link rel="stylesheet" href="print.css">
</head>
<body>
<header>
    <nav>
        <ul>
            <li><a href="index.php">Domů</a></li>
            <li><a href="pridatprispevek.php">Přidat recept</a></li>
            <li><a href="profil.php">Profil</a></li>
            <li><a href="logout.php">Odhlásit se</a></li>
        </ul>
    </nav>
</header>
<main>
    <h1>Moje recepty</h1>

    <?php if (empty($displayedPosts)): ?>
        <p class="neniprispevek">Nemáte žádné recepty k zobrazení.</p>
    <?php else: ?>
        <ul class="prispevky">
            <?php foreach ($displayedPosts as $post): ?>
                <?php if ($editPostId === $post['id']): ?>
                    <li>
                        <form method="POST" action="mojeprispevky.php?page=<?= $page ?>">
                            <!-- CSRF token -->
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="edit_id" value="<?= htmlspecialchars($post['id']) ?>">

                            <div class="form-group">
                            <label>Název receptu:</label>
                            <input type="text" name="title" 
                                   value="<?= htmlspecialchars($post['title'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                            <label>Kategorie:</label>
                            <select name="category" required>
                                <option value="slané"  <?php if (($post['category'] ?? '') === 'slané')  echo 'selected'; ?>>Slané</option>
                                <option value="sladké" <?php if (($post['category'] ?? '') === 'sladké') echo 'selected'; ?>>Sladké</option>
                            </select>
                            </div>
                            <div class="form-group">
                            <label>Seznam surovin:</label>
                            <textarea name="ingredients" required><?= htmlspecialchars($post['ingredients'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                            <label>Postup přípravy:</label>
                            <textarea name="content" required><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                            </div>
                            <button type="submit">Uložit změny</button>
                            <!-- Odkaz "Storno" - vrátí se bez úprav -->
                            <a class="odkaz" href="mojeprispevky.php?page=<?= $page ?>">Zrušit</a>
                        </form>
                    </li>
                <?php else: ?>
                    <li>
                        <p><span class="strong">Název:</span> <?= htmlspecialchars($post['title'] ?? 'Bez názvu') ?></p>
                        <?php if (!empty($post['image_path'])): ?>
                            <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Obrázek receptu" width="200">
                        <?php endif; ?>
                        <p><span class="strong">Kategorie:</span> <?= htmlspecialchars($post['category'] ?? '') ?></p>
                        <p><span class="strong">Suroviny:</span> <?= nl2br(htmlspecialchars($post['ingredients'] ?? '')) ?></p>
                        <p><span class="strong">Postup:</span> <?= nl2br(htmlspecialchars($post['content'] ?? '')) ?></p>
                        
                        <p><span class="strong">Přidáno:</span> 
                        <?= htmlspecialchars(
                            date('d.m.Y H:i', strtotime($post['post_time'] ?? ''))
                        ) ?>
                        </p>
                        <a class="odkaz" href="mojeprispevky.php?edit_id=<?= urlencode($post['id']) ?>&page=<?= $page ?>">Upravit</a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if ($totalPages > 1): ?>
        <div class="strankovani">
            <?php if ($page > 1): ?>
                <a href="mojeprispevky.php?page=<?= $page - 1 ?>">Předchozí</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="mojeprispevky.php?page=<?= $i ?>" 
                   class="<?= ($i === $page) ? 'aktivni' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="mojeprispevky.php?page=<?= $page + 1 ?>">Další</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>
<footer>
    <p>&copy; 2025 Cookbook</p>
</footer>
</body>
</html>

