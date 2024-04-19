<?php
session_start();

function redirectIfNotLoggedIn() {
    // Vérifie si la variable de session "user_id" est définie
    if (!isset($_SESSION['user_id'])) {
        // L'utilisateur n'est pas connecté, redirige vers la page de connexion
        header("Location: connexion.php");
        exit(); // Assure que le script s'arrête après la redirection
    }
}

// Utilisation de la fonction pour vérifier si l'utilisateur est connecté
redirectIfNotLoggedIn();
?>
