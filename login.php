<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db_connection.php';

    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs";
        $_SESSION['old_values'] = $_POST;
        header("Location: login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($user['email']  === 'mylanhermes@gmail.com') {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['success'] = "Connexion réussie";
                header("Location: admin.php");
                exit();
            } else {
                $_SESSION['error'] = "Mot de passe incorrect";
                $_SESSION['old_values'] = $_POST;
                header("Location: login.php");
                exit();
            }
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['success'] = "Connexion réussie";
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Mot de passe incorrect";
            $_SESSION['old_values'] = $_POST;
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Utilisateur non trouvé";
        $_SESSION['old_values'] = $_POST;
        header("Location: login.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<style>
        body {
            background-color: #f8f9fa;
        }

        /* .container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
            padding-top: 100px;
        } */

        h1 {
            color: #4e5166;
            font-weight: bold;
            margin-bottom: 20px;
        }

        p {
            color: #4e5166;
            font-size: 18px;
            margin-bottom: 40px;
        }

        .btn-primary {
            background-color:  #fd2d01;
            border-color: #4e5166;
            padding: 10px 40px;
            font-size: 20px;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #3c3e4d;
            border-color: #3c3e4d;
        }
    </style>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title text-center">Connexion</h3>
                        <?php if (isset($_SESSION['error'])) : ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_SESSION['old_values']['email']) ? $_SESSION['old_values']['email'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </form>
                    </div>
                    <!-- Bouton d'inscription -->
                    <div class="text-center mt-3 text=secondary">
                        <a href="register.php" class="btn btn-link">S'inscrire</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<?php
unset($_SESSION['old_values']);
?>