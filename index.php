<?php
/**
 * Page d'accueil
 * LA PERLE BERBÈRE - Gestion de rendez-vous
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LA PERLE BERBÈRE - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="./">
                <img src="images/logo.jpg" alt="LA PERLE BERBÈRE" class="me-2" style="height: 40px; border-radius: 4px;">
                <span>LA PERLE BERBÈRE</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="./"><i class="bi bi-house"></i> Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="client/"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero mb-5">
        <div class="container py-5">
            <img src="images/logo.jpg" alt="LA PERLE BERBÈRE" class="hero-logo">
            <h1>LA PERLE BERBÈRE</h1>
            <p class="lead mb-0">Bienvenue dans votre espace de réservation de rendez-vous</p>
        </div>
    </section>

    <!-- Contenu principal -->
    <main class="container my-5">
        <div class="row g-4 mb-5 justify-content-center">
            <!-- Prise de rendez-vous -->
            <div class="col-md-6">
                <div class="card feature-box h-100">
                    <div class="feature-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5>Prendre un rendez-vous</h5>
                    <p class="text-muted small mb-3">
                        Consultez la disponibilité et réservez un créneau qui vous convient.
                        Simple, rapide et en ligne.
                    </p>
                    <a href="client/" class="btn btn-primary mt-auto">
                        <i class="bi bi-arrow-right"></i> Réserver maintenant
                    </a>
                </div>
            </div>

        </div>

        <!-- Informations -->
        <section class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div style="font-size: 2.5rem; color: #3498db; margin-bottom: 1rem;">
                            <i class="bi bi-clock"></i>
                        </div>
                        <h6>Accès 24/7</h6>
                        <p class="small text-muted">Réservez vos rendez-vous à tout moment, jour et nuit.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div style="font-size: 2.5rem; color: #27ae60; margin-bottom: 1rem;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h6>Sécurisé</h6>
                        <p class="small text-muted">Vos données sont protégées et traitées en toute confiance.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div style="font-size: 2.5rem; color: #e74c3c; margin-bottom: 1rem;">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <h6>Rapide</h6>
                        <p class="small text-muted">Confirmez votre rendez-vous en quelques clics seulement.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container py-4">
            <p class="mb-0"><strong>LA PERLE BERBÈRE</strong> &copy; <?= date('Y') ?></p>
            <small>Système de gestion de rendez-vous - Tous droits réservés</small>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
