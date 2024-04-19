<?php

if (isset($_GET['id']) and !empty($_GET['id'])) {

    $id = $_GET['id'];

    // Inclure le fichier de connexion à la base de données
    include 'db_connection.php';

    // Récupérer tous les posts depuis la base de données
    $stmt = $conn->prepare("DELETE FROM posts WHERE `posts`.`id` = $id");
    $stmt->execute();
    header("Location: admin.php");
}
