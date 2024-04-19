<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Inclure le fichier de connexion à la base de données
include 'db_connection.php';

// Récupérer l'utilisateur depuis la base de données
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Pagination
$posts_per_page = 6;
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM posts");
$stmt->execute();
$total_result = $stmt->get_result();
$row = $total_result->fetch_assoc();
$total_posts = $row['total'];
$total_pages = ceil($total_posts / $posts_per_page);

$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($current_page - 1) * $posts_per_page;

// Requête pour récupérer les posts en ordre récent de création avec pagination
$stmt = $conn->prepare("SELECT * FROM posts ORDER BY created_at DESC LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $posts_per_page);
$stmt->execute();
$posts = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .sidebar {
            background-color: #FFD7D7;
            padding: 20px;
        }

        .post {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Tableau de bord</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="add_post.php">
                            <i class="fas fa-plus"></i> Publier un Post
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Déconnecter
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar">
                <h3>Mon Profil</h3>
                <p><strong>Nom d'utilisateur:</strong> <?php echo $user['name']; ?></p>
                <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
            </div>

            <!-- Contenu principal -->
            <div class="col-md-9">
                <h1>Mes Posts</h1>
                <div class="row">
                    <?php while ($row = $posts->fetch_assoc()) : ?>
                        <div class="col-sm-4">

                            <div class="post">
                                <h3><?php echo $row['title']; ?></h3>
                                <p><?php echo $row['content']; ?></p>
                                <?php if (!empty($row['image'])) : ?>
                                    <!-- Chemin d'accès à l'image -->
                                    <?php $imagePath = $row['image']; ?>

                                    <!-- Vérifier si l'image existe -->
                                    <?php if (file_exists($imagePath)) : ?>
                                        <img src="<?php echo $imagePath; ?>" alt="Image" class="img-fluid">
                                    <?php else : ?>
                                        <p>Image not found: <?php echo $row['image']; ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Afficher le nombre de likes -->
                                <p>Nombre de Likes: <?php echo $row['likes']; ?></p>

                                <!-- Bouton de Like -->
                                <form id="likeForm_<?php echo $row['id']; ?>" action="likes.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
                                    <?php
                                    // Vérifier si l'utilisateur a déjà aimé le post
                                    $stmt = $conn->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
                                    $stmt->bind_param("ii", $row['id'], $_SESSION['user_id']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    if ($result->num_rows > 0) {
                                        // L'utilisateur a déjà aimé le post, donc afficher le bouton "Unlike"
                                        echo '<button type="submit" class="btn btn-link text-danger"><i class="fas fa-thumbs-down"></i> Unlike</button>';
                                    } else {
                                        // L'utilisateur n'a pas encore aimé le post, donc afficher le bouton "Like"
                                        echo '<button type="submit" class="btn btn-link text-success"><i class="fas fa-thumbs-up"></i> Like</button>';
                                    }
                                    ?>
                                </form>

                                <!-- Modifier un post -->
                                <a href="edit_post.php?id=<?php echo $row['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Modifier</a>
                            </div>

                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Afficher un message si aucun post n'est disponible -->
                <?php if ($posts->num_rows === 0) : ?>
                    <p>Aucun post disponible.</p>
                <?php endif; ?>

                <!-- Pagination -->
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-center">
                        <?php if ($current_page > 1) : ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Précédent">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages) : ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Suivant">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
