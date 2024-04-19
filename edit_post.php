<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// Vérifier si l'ID du post à éditer est passé en paramètre d'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID de post non valide";
    header("Location: dashboard.php");
    exit();
}

$post_id = $_GET['id'];

// Récupérer les informations du post à éditer depuis la base de données
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Post non trouvé";
    header("Location: dashboard.php");
    exit();
}

$post = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image = $_FILES['image'];

    // Vérifier que les champs ne sont pas vides
    if (empty($title) || empty($content)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs";
        header("Location: edit_post.php?id=$post_id");
        exit();
    }

    // Vérifier si un nouvel fichier image a été uploadé
    if (!empty($image['name'])) {
        // Vérifier le type de fichier
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_extension = pathinfo($image['name'], PATHINFO_EXTENSION);

        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION['error'] = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés";
            header("Location: edit_post.php?id=$post_id");
            exit();
        }

        // Chemin de stockage des images
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image["name"]);

        // Déplacer le fichier uploadé vers le répertoire de stockage
        if (move_uploaded_file($image["tmp_name"], $target_file)) {
            // Mettre à jour le post avec la nouvelle image
            $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sssi", $title, $content, $target_file, $post_id);
            $stmt->execute();

            $_SESSION['success'] = "Le post a été mis à jour avec succès.";
            header("Location: dashboard.php");
            exit();

            $stmt->close();
            $conn->close();
        } else {
            $_SESSION['error'] = "Une erreur s'est produite lors de l'upload de l'image";
            header("Location: edit_post.php?id=$post_id");
            exit();
        }
    } else {
        // Si aucune nouvelle image n'a été uploadée, mettre à jour les autres champs seulement
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $post_id);
        $stmt->execute();

        $_SESSION['success'] = "Le post a été mis à jour avec succès.";
        header("Location: dashboard.php");
        exit();

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editer un Post avec Image</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Editer le Post</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <form action="edit_post.php?id=<?php echo $post_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Titre:</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo $post['title']; ?>">
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Contenu:</label>
                <textarea class="form-control" id="content" name="content" rows="5"><?php echo $post['content']; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image:</label>
                <input type="file" class="form-control" id="image" name="image">
                <img src="<?php echo $post['image']; ?>" alt="Post Image" class="img-fluid mt-3" style="max-width: 300px;">
            </div>
            <button type="submit" class="btn btn-primary">Mettre à Jour</button>
        </form>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
