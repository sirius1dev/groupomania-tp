<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image = $_FILES['image'];

    // Vérifier que les champs ne sont pas vides
    if (empty($title) || empty($content) || empty($image['name'])) {
        $_SESSION['error'] = "Veuillez remplir tous les champs";
        header("Location: add_post.php");
        exit();
    }

    // Vérifier le type de fichier
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    $file_extension = pathinfo($image['name'], PATHINFO_EXTENSION);

    if (!in_array($file_extension, $allowed_types)) {
        $_SESSION['error'] = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés";
        header("Location: add_post.php");
        exit();
    }

    // Chemin de stockage des images
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image["name"]);

    // Déplacer le fichier uploadé vers le répertoire de stockage
    if (move_uploaded_file($image["tmp_name"], $target_file)) {
        // Insertion du nouveau post dans la base de données
        // Date actuelle pour created_at
        $created_at = date("Y-m-d H:i:s");
        $stmt = $conn->prepare("INSERT INTO posts (title, content, image,created_at) VALUES (?, ?, ?,NOW())");
        $stmt->bind_param("sss", $title, $content, $target_file);
        $stmt->execute();

        $_SESSION['success'] = "Le post a été ajouté avec succès.";
        header("Location: dashboard.php");
        exit();

        $stmt->close();
        $conn->close();
    } else {
        $_SESSION['error'] = "Une erreur s'est produite lors de l'upload de l'image";
        header("Location: add_post.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Post avec Image</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Ajouter un Post avec Image</h2>
        <?php if (isset($_SESSION['error'])) : ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <form action="add_post.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Titre:</label>
                <input type="text" class="form-control" id="title" name="title">
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Contenu:</label>
                <textarea class="form-control" id="content" name="content" rows="5"></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image:</label>
                <input type="file" class="form-control" id="image" name="image">
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>