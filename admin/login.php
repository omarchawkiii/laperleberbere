<?php
/**
 * ADMIN - Page de connexion
 * LA PERLE BERBÈRE
 */

session_start();

// Si déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($email === 'momoh@gmail.com' && $password === 'momoh@2026') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email']     = $email;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Email ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - LA PERLE BERBÈRE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex align-items-center" style="min-height: 100vh; background: linear-gradient(135deg, #2c3e50, #e74c3c);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <img src="../images/logo.jpg" alt="LA PERLE BERBÈRE" class="mb-3" style="height: 70px; border-radius: 8px;">
                    <h4>LA PERLE BERBÈRE</h4>
                    <p class="text-muted small">Espace Administration</p>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4 text-center"><i class="bi bi-lock"></i> Connexion</h5>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : '') ?>"
                                       required autofocus>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Se connecter
                            </button>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="../" class="text-muted small"><i class="bi bi-arrow-left"></i> Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
