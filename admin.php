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

// Récupérer tous les posts depuis la base de données
$stmt = $conn->prepare("SELECT * FROM posts");
$stmt->execute();
$posts = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .sidebar {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .post {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 10px;
        }

        .post-title {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .post-content {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .post-actions .btn {
            margin-right: 10px;
        }

        .no-posts {
            margin-top: 20px;
            font-size: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="add_post.php">
                            <i class="fas fa-plus"></i> Ajouter un Post
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

    <div class="container-fluid mt-5">
        <div class="row">
            <!-- Contenu principal -->
            <div class="col-md-12">
                <h1>Liste des Posts</h1>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Content</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $posts->fetch_assoc()) : ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['title']; ?></td>
                                    <td><?php echo $row['content']; ?></td>
                                    <td>
                                        <!-- <a href="edit_post.php?id=<?php echo $row['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Modifier</a> -->
                                        <a class="btn btn-danger" href="delete.php?id=<?php echo $row['id'] ?>">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Afficher un message si aucun post n'est disponible -->
                <?php if ($posts->num_rows === 0) : ?>
                    <p class="no-posts">Aucun post disponible.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Custom Script -->
    <script>
        $(document).ready(function () {
            // Fonction pour gérer la suppression d'un post
            $('.delete-post').click(function (e) {
                e.preventDefault();
                var postId = $(this).data('post-id');
                if (confirm("Êtes-vous sûr de vouloir supprimer ce post ?")) {
                    $.ajax({
                        type: 'POST',
                        url: 'delete_post.php',
                        data: {
                            post_id: postId
                        },
                        success: function (response) {
                            if (response === 'success') {
                                // Suppression réussie, actualiser la page
                                location.reload();
                            } else {
                                // Erreur lors de la suppression, afficher un message d'erreur
                                alert('Erreur lors de la suppression du post.');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
