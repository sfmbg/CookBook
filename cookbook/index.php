<?php
session_start();
include 'fce.php';
$currentUser = null;
if (isset($_SESSION['user'])) {
    $loggedUsername = $_SESSION['user'];
    $users = readJson('users.json');
    foreach ($users as $u) {
        if ($u['username'] === $loggedUsername) {
            $currentUser = $u;
            break;
        }
    }
}
$posts = readJson('prispevky.json');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
    if ($currentUser && $currentUser['role'] === 'admin') {
        $deletePostId = $_POST['delete_post_id'];
        foreach ($posts as $index => $post) {
            if (isset($post['id']) && $post['id'] === $deletePostId) {

                if (!empty($post['image_path']) 
                    && file_exists($post['image_path']) 
                    && $post['image_path'] !== 'obrazky/default.png'
                ) {
                    unlink($post['image_path']);
                }
                unset($posts[$index]);
                $posts = array_values($posts);
                writeJson('prispevky.json', $posts);

                break; 
            }
        }
    }
    header('Location: index.php');
    exit;
}
$categoryFilter = '';
if (isset($_GET['category']) && in_array($_GET['category'], ['slané', 'sladké'])) {
    $categoryFilter = $_GET['category'];
    $posts = array_filter($posts, function($post) use ($categoryFilter) {
        return isset($post['category']) && $post['category'] === $categoryFilter;
    });
}
usort($posts, function($a, $b) {
    return strtotime($b['post_time']) - strtotime($a['post_time']);
});
$pocetPrispevkuNaStranku = 5; 
$celkovyPocetPrispevku = count($posts);
$pocetStranek = ceil($celkovyPocetPrispevku / $pocetPrispevkuNaStranku);
$stranka = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$stranka = max(1, min($stranka, $pocetStranek));
$zacatekIndexu = ($stranka - 1) * $pocetPrispevkuNaStranku;
$zobrazenePrispevky = array_slice($posts, $zacatekIndexu, $pocetPrispevkuNaStranku);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepty</title>
    <link rel="stylesheet" href="index2.css">
    <link rel="stylesheet" href="print.css">
    <script src="script2.js"></script>
</head>
<body>
<header>
    <nav>
        <ul>
            <?php if (isset($_SESSION['user'])): ?>
                <li><a href="pridatprispevek.php">Přidat recept</a></li>
                <li><a href="profil.php">Profil</a></li>
                <li><a href="logout.php">Odhlásit se</a></li>
            <?php else: ?>
                <li><a href="login.php">Přihlášení</a></li>
                <li><a href="registration.php">Registrace</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>
    <span class="nadpis">Cookbook</span>
    <p class="popis">Vítejte na stránce Cookbook věnované receptům. Sdílejte své oblíbené recepty, objevujte nové nápady a nechte se inspirovat ostatními. Mějte všechny Vaše příspěvky přehledně na jednom místě. Vaření nebylo nikdy jednodušší!</p>
    <h1>Nejnovější recepty</h1>
    <form method="get" action="index.php" id="filterForm">
        <label for="category">Filtrovat podle kategorie:</label>
        <select name="category" id="category">
            <option value="">-- Vyberte kategorii --</option>
            <option value="slané" <?php echo ($categoryFilter === 'slané') ? 'selected' : ''; ?>>Slané</option>
            <option value="sladké" <?php echo ($categoryFilter === 'sladké') ? 'selected' : ''; ?>>Sladké</option>
        </select>
        <button type="submit">Filtrovat</button>
        <button type="button" id="resetButton">Reset</button>
    </form>
    <?php if (empty($zobrazenePrispevky)): ?>
        <p>Žádné recepty k zobrazení.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($zobrazenePrispevky as $prispevek): ?>
                <li>
                    <p><span class="strong">Název:</span> 
                       <?= htmlspecialchars($prispevek['title'] ?? 'Bez názvu') ?>
                    </p>
                       <img src="<?= htmlspecialchars($prispevek['image_path'] ?? 'obrazky/default.png') ?>" 
                            alt="Obrázek receptu" width="200">

                    <p><span class="strong">Autor:</span> 
                       <?= htmlspecialchars($prispevek['author'] ?? 'Neznámý') ?>
                    </p>

                    <p><span class="strong">Kategorie:</span> 
                       <?= htmlspecialchars($prispevek['category'] ?? 'Neuvedeno') ?>
                    </p>

                    <p><span class="strong">Suroviny:</span> <br>
                       <?= nl2br(htmlspecialchars($prispevek['ingredients'] ?? 'Neuvedeno')) ?>
                    </p>

                    <p><span class="strong">Postup:</span> <br>
                       <?= nl2br(htmlspecialchars($prispevek['content'] ?? 'Obsah není dostupný')) ?>
                    </p>

                    <p><span class="strong">Přidáno:</span> 
                       <?= htmlspecialchars(date('d.m.Y H:i', strtotime($prispevek['post_time'] ?? ''))) ?>
                    </p>

                    <!-- Pokud je uživatel admin, zobraz tlačítko Smazat -->
                    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                        <form method="post" action="index.php">
                            <input type="hidden" name="delete_post_id" 
                                   value="<?= htmlspecialchars($prispevek['id'] ?? '') ?>">
                            <button type="submit">Smazat</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <div class="strankovani">
        <?php if ($stranka > 1): ?>
            <a href="index.php?page=<?= $stranka - 1 ?>">Předchozí</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $pocetStranek; $i++): ?>
            <a href="index.php?page=<?= $i ?>" 
               class="<?= $i === $stranka ? 'aktivni' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($stranka < $pocetStranek): ?>
            <a href="index.php?page=<?= $stranka + 1 ?>">Další</a>
        <?php endif; ?>
    </div>
</main>
<footer>
    <p>&copy; 2025 Cookbook</p>
</footer>
</body>
</html>