<?php
/**
 * ADMIN - Gestion des créneaux disponibles
 * LA PERLE BERBÈRE
 */

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/db.php';

$query = '
    SELECT 
        d.id,
        d.date,
        d.heure,
        d.statut,
        COUNT(r.id) as has_appointment,
        MAX(r.nom) as nom,
        MAX(r.prenom) as prenom
    FROM disponibilites d
    LEFT JOIN rendezvous r ON d.id = r.disponibilite_id
    WHERE d.date >= CURDATE()
    GROUP BY d.id, d.date, d.heure, d.statut
    ORDER BY d.date ASC, d.heure ASC
';

$stmt = $pdo->query($query);
$slots = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Créneaux - LA PERLE BERBÈRE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="../">
                <img src="../images/logo.jpg" alt="LA PERLE BERBÈRE" class="me-2" style="height: 40px; border-radius: 4px;">
                <span>LA PERLE BERBÈRE</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_slots.php"><i class="bi bi-clock"></i> Créneaux</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><i class="bi bi-clock"></i> Gestion des Créneaux</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSlotModal">
                <i class="bi bi-plus-circle"></i> Ajouter un créneau
            </button>
        </div>

        <!-- Zone messages -->
        <div id="message-zone"></div>

        <!-- Tableau des créneaux -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <strong>Créneaux disponibles: <?= count($slots) ?></strong>
            </div>
            
            <?php if (empty($slots)): ?>
                <div class="card-body text-center text-muted py-4">
                    <p><i class="bi bi-calendar-x" style="font-size: 3rem; opacity: 0.5;"></i></p>
                    <p>Aucun créneau disponible. Créez-en un!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Statut</th>
                                <th>Réservation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($slots as $slot): ?>
                                <tr>
                                    <td><strong><?= format_date($slot['date']) ?></strong></td>
                                    <td><?= format_time($slot['heure']) ?></td>
                                    <td>
                                        <?php if ($slot['statut'] === 'réservé'): ?>
                                            <span class="badge bg-danger">Réservé</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Libre</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($slot['has_appointment'] > 0): ?>
                                            <small><?= htmlspecialchars($slot['prenom'] . ' ' . $slot['nom']) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($slot['has_appointment'] > 0): ?>
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    onclick="releaseSlot(<?= $slot['id'] ?>)">
                                                <i class="bi bi-arrow-repeat"></i> Libérer
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteSlot(<?= $slot['id'] ?>)">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Ajouter un créneau -->
    <div class="modal fade" id="addSlotModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un créneau disponible</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addSlotForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="slotDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="slotDate" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="slotTime" class="form-label">Heure</label>
                            <input type="time" class="form-control" id="slotTime" name="heure" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
    // Définir la date minimum d'aujourd'hui
    document.getElementById('slotDate').min = new Date().toISOString().split('T')[0];

    document.getElementById('addSlotForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const date = document.getElementById('slotDate').value;
        const heure = document.getElementById('slotTime').value;
        
        $.post('../admin/api_slots.php', 
            { action: 'add', date: date, heure: heure },
            function(response) {
                if (response.success) {
                    showMessage('Créneau ajouté avec succès!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showMessage(response.message || 'Erreur lors de l\'ajout', 'danger');
                }
            },
            'json'
        );
    });

    function deleteSlot(slotId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce créneau?')) {
            $.post('../admin/api_slots.php',
                { action: 'delete', id: slotId },
                function(response) {
                    if (response.success) {
                        showMessage('Créneau supprimé!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showMessage(response.message || 'Erreur', 'danger');
                    }
                },
                'json'
            );
        }
    }

    function releaseSlot(slotId) {
        if (confirm('Êtes-vous sûr de vouloir libérer ce créneau?')) {
            $.post('../admin/api_slots.php',
                { action: 'release', id: slotId },
                function(response) {
                    if (response.success) {
                        showMessage('Créneau libéré!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showMessage(response.message || 'Erreur', 'danger');
                    }
                },
                'json'
            );
        }
    }

    function showMessage(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.getElementById('message-zone').innerHTML = alertHtml;
    }
    </script>
</body>
</html>
