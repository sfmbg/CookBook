<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$errorMsg = '';
$title = '';
$category = '';
$ingredients = '';
$instructions = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMsg = 'Chyba CSRF: Neplatný token!';
    } else {
        $title = trim($_POST['title']);
        $category = $_POST['category'];
        $ingredients = trim($_POST['ingredients']);
        $instructions = trim($_POST['instructions']);
        $username_author = $_SESSION['user'];
        $post_time = date('Y-m-d H:i', strtotime('+1 hour'));

        if (strlen($title) > 100) {
            $errorMsg = 'Název receptu nesmí přesáhnout 100 znaků.';
        } elseif (strlen($ingredients) > 5000) {
            $errorMsg = 'Seznam surovin nesmí přesáhnout 5000 znaků.';
        } elseif (strlen($instructions) > 5000) {
            $errorMsg = 'Postup přípravy nesmí přesáhnout 5000 znaků.';
        } else {
            if (!empty($_FILES['fotka']['name'])) {
                $allowedExtensions = ['jpg', 'png'];
                $imageExtension = strtolower(pathinfo($_FILES['fotka']['name'], PATHINFO_EXTENSION));

                if (!in_array($imageExtension, $allowedExtensions)) {
                    $errorMsg = "Chyba: Obrázek musí být ve formátu JPG nebo PNG.";
                } else {
                    $file = 'prispevky.json';
                    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
                    $post_id = uniqid('post_');
                    $image_id = $post_id;
                    $imageName = $image_id . '.' . $imageExtension; 
                    $imagePath = 'obrazky/' . $imageName;

                    if (!is_dir('obrazky')) {
                        mkdir('obrazky', 0777, true);
                    }
                    if ($_FILES['fotka']['size'] > 2 * 1024 * 1024) {
                        if ($imageExtension === 'jpg') {
                            $src = @imagecreatefromjpeg($_FILES['fotka']['tmp_name']);
                            if (!$src) {
                                $errorMsg = "Chyba: Nepodařilo se otevřít JPEG soubor.";
                            } else {
                                imagejpeg($src, $imagePath, 80);
                                imagedestroy($src);
                            }
                        } else {
                            $src = @imagecreatefrompng($_FILES['fotka']['tmp_name']);
                            if (!$src) {
                                $errorMsg = "Chyba: Nepodařilo se otevřít PNG soubor.";
                            } else {
                                imagepng($src, $imagePath, 6);
                                imagedestroy($src);
                            }
                        }
                        if (empty($errorMsg) && file_exists($imagePath) && filesize($imagePath) > 2 * 1024 * 1024) {
                            $errorMsg = "Chyba: Ani po kompresi se nepodařilo obrázek uložit pod 2 MB.";
                            unlink($imagePath);
                        }
                    } else {
                        if (!move_uploaded_file($_FILES['fotka']['tmp_name'], $imagePath)) {
                            $errorMsg = "Chyba: Nepodařilo se nahrát obrázek.";
                        }
                    }
                    if (empty($errorMsg)) {
                        $new_post = [
                            'id'         => $post_id,
                            'title'      => $title,
                            'content'    => $instructions,
                            'author'     => $username_author,
                            'category'   => $category,
                            'ingredients'=> $ingredients,
                            'post_time'  => $post_time,
                            'image_path' => $imagePath
                        ];

                        $data[] = $new_post; 
                        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
                        header("Location: index.php");
                        exit;
                    }
                }
            } else {
                $errorMsg = "Chyba: Musíte nahrát obrázek.";
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
    <title>Přidat příspěvek</title>
    <link rel="stylesheet" href="form.css">
    <link rel="stylesheet" href="print.css">
</head>
<body>
<header>
    <nav>
        <ul>
            <li><a href="index.php">Domů</a></li>
            <li><a href="profil.php">Profil</a></li>
        </ul>
    </nav>
</header>

<main>
    <h1>Přidat recept</h1>

    <?php if (!empty($errorMsg)): ?>
        <div class="error">
            <?= htmlspecialchars($errorMsg) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="pridatprispevek.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="form-group">
            <label for="title">*Název receptu:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required maxlength="50">
        </div>
        <div class="form-group">
            <label for="category">*Kategorie:</label>
            <select id="category" name="category" required>
                <option value="" disabled <?= empty($category) ? 'selected' : '' ?>>Vyberte kategorii</option>
                <option value="slané" <?= $category === 'slané' ? 'selected' : '' ?>>Slané</option>
                <option value="sladké" <?= $category === 'sladké' ? 'selected' : '' ?>>Sladké</option>
            </select>
        </div>
        <div class="form-group">
            <label for="ingredients">*Seznam surovin:</label>
            <textarea id="ingredients" name="ingredients" required maxlength="1000"><?= htmlspecialchars($ingredients) ?></textarea>
        </div>
        <div class="form-group">
            <label for="instructions">*Postup přípravy:</label>
            <textarea id="instructions" name="instructions" required maxlength="5000"><?= htmlspecialchars($instructions) ?></textarea>
        </div>
        <div class="form-group">
            <label for="fotka">*Nahrát obrázek (JPG/PNG):</label>
            <input type="file" id="fotka" name="fotka" accept=".jpg, .png" required>
        </div>
        <button type="submit">Přidat recept</button>
    </form>
</main>

<footer>
    <p>&copy; 2025 Cookbook</p>
</footer>
</body>
</html>
