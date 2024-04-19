<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['post_id'])) {
        $postId = $_POST['post_id'];
        $userId = $_SESSION['user_id'];

        // Vérifier si l'utilisateur a déjà aimé le post
        $stmt = $conn->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $postId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // L'utilisateur a déjà aimé le post, donc supprimer le like (unlike)
                $deleteStmt = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
                if ($deleteStmt) {
                    $deleteStmt->bind_param("ii", $postId, $userId);
                    $deleteStmt->execute();

                    // Mettre à jour le nombre de likes du post
                    $updateLikesStmt = $conn->prepare("UPDATE posts SET likes = likes - 1 WHERE id = ?");
                    if ($updateLikesStmt) {
                        $updateLikesStmt->bind_param("i", $postId);
                        $updateLikesStmt->execute();

                        header("Location: dashboard.php");
                        exit();
                    } else {
                        echo "Erreur lors de la préparation de la requête SQL pour la mise à jour des likes.";
                    }
                } else {
                    echo "Erreur lors de la préparation de la requête SQL pour la suppression du like.";
                }
            } else {
                // L'utilisateur n'a pas encore aimé le post, donc ajouter le like
                $insertStmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
                if ($insertStmt) {
                    $insertStmt->bind_param("ii", $postId, $userId);
                    $insertStmt->execute();

                    // Mettre à jour le nombre de likes du post
                    $updateLikesStmt = $conn->prepare("UPDATE posts SET likes = likes + 1 WHERE id = ?");
                    if ($updateLikesStmt) {
                        $updateLikesStmt->bind_param("i", $postId);
                        $updateLikesStmt->execute();

                        header("Location: dashboard.php");
                        exit();
                    } else {
                        echo "Erreur lors de la préparation de la requête SQL pour la mise à jour des likes.";
                    }
                } else {
                    echo "Erreur lors de la préparation de la requête SQL pour l'insertion du like.";
                }
            }
        } else {
            echo "Erreur lors de la préparation de la requête SQL.";
        }
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
