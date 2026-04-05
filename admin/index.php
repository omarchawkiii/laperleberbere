<?php
/**
 * ADMIN - Tableau de bord des rendez-vous
 * LA PERLE BERBÈRE
 */

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/db.php';

$page    = isset($_GET['page'])    ? max(1, (int)$_GET['page'])    : 1;
$search  = isset($_GET['search'])  ? sanitize($_GET['search'])      : '';
$archive = isset($_GET['archive']) && $_GET['archive'] === '1';
$per_page = 10;

// Construire les conditions
$date_condition = $archive ? 'd.date < CURDATE()' : 'd.date >= CURDATE()';
$conditions = [$date_condition];
$params = [];

if (!empty($search)) {
    $conditions[] = '(r.nom LIKE ? OR r.prenom LIKE ? OR r.email LIKE ? OR r.telephone LIKE ?)';
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

$where = 'WHERE ' . implode(' AND ', $conditions);

// Compter le total
$count_query = 'SELECT COUNT(*) as total FROM rendezvous r JOIN disponibilites d ON r.disponibilite_id = d.id ' . $where;
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total = $count_stmt->fetch()['total'];
$total_pages = max(1, ceil($total / $per_page));

// Récupérer les rendez-vous
$offset = ($page - 1) * $per_page;
$query = '
    SELECT
        r.id,
        r.nom,
        r.prenom,
        r.email,
        r.telephone,
        r.raison,
        r.statut,
        r.disponibilite_id,
        d.date,
        d.heure,
        r.created_at
    FROM rendezvous r
    JOIN disponibilites d ON r.disponibilite_id = d.id
    ' . $where . '
    ORDER BY d.date ' . ($archive ? 'DESC' : 'ASC') . ', d.heure ASC
    LIMIT ? OFFSET ?
';

$stmt = $pdo->prepare($query);
$params_full = array_merge($params, [$per_page, $offset]);
$stmt->execute($params_full);
$appointments = $stmt->fetchAll();

// Compteurs pour les stats
$upcoming_count_stmt = $pdo->query("SELECT COUNT(*) FROM rendezvous r JOIN disponibilites d ON r.disponibilite_id = d.id WHERE d.date >= CURDATE()");
$upcoming_count = $upcoming_count_stmt->fetchColumn();

$pending_count_stmt = $pdo->query("SELECT COUNT(*) FROM rendezvous r JOIN disponibilites d ON r.disponibilite_id = d.id WHERE d.date >= CURDATE() AND r.statut = 'en attente'");
$pending_count = $pending_count_stmt->fetchColumn();

$validated_count_stmt = $pdo->query("SELECT COUNT(*) FROM rendezvous r JOIN disponibilites d ON r.disponibilite_id = d.id WHERE d.date >= CURDATE() AND r.statut = 'validé'");
$validated_count = $validated_count_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - LA PERLE BERBÈRE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="../">
                <img src="../images/logo.jpg" alt="LA PERLE BERBÈRE" class="me-2" style="height: 38px; border-radius: 4px;">
                <span>LA PERLE BERBÈRE</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_slots.php"><i class="bi bi-clock"></i> Créneaux</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4">

        <!-- Titre -->
        <div class="mb-4">
            <h1 class="h3 mb-0"><i class="bi bi-calendar-check text-danger"></i> Gestion des Rendez-vous</h1>
            <p class="text-muted small mb-0">Tableau de bord — LA PERLE BERBÈRE</p>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon red"><i class="bi bi-calendar2-week"></i></div>
                    <div>
                        <div class="fw-bold fs-4"><?= $upcoming_count ?></div>
                        <div class="text-muted small">À venir</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <div class="fw-bold fs-4"><?= $pending_count ?></div>
                        <div class="text-muted small">En attente</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <div class="fw-bold fs-4"><?= $validated_count ?></div>
                        <div class="text-muted small">Validés</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zone messages -->
        <div id="message-zone"></div>

        <!-- Filtres & Recherche -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <form method="GET" class="d-flex gap-2">
                            <?php if ($archive): ?>
                                <input type="hidden" name="archive" value="1">
                            <?php endif; ?>
                            <input type="text" class="form-control" name="search"
                                   placeholder="Nom, email, téléphone..."
                                   value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="index.php<?= $archive ? '?archive=1' : '' ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-x"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-12 col-md-6 d-flex justify-content-md-end gap-2">
                        <?php if (!$archive): ?>
                            <a href="index.php?archive=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-archive"></i> Archives
                            </a>
                        <?php else: ?>
                            <a href="index.php<?= !empty($search) ? '?search=' . urlencode($search) : '' ?>"
                               class="btn btn-primary">
                                <i class="bi bi-calendar-check"></i> À venir
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des rendez-vous -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center"
                 style="background-color: <?= $archive ? '#6c757d' : 'var(--accent-color)' ?>; color: white;">
                <span>
                    <i class="bi bi-<?= $archive ? 'archive' : 'calendar-check' ?>"></i>
                    <?= $archive ? 'Archives' : 'Rendez-vous à venir' ?> —
                    <strong><?= $total ?></strong> résultat<?= $total > 1 ? 's' : '' ?>
                </span>
                <?php if ($archive): ?>
                    <span class="badge bg-light text-dark">Vue archive</span>
                <?php else: ?>
                    <span class="badge bg-light text-danger">Vue courante</span>
                <?php endif; ?>
            </div>

            <?php if (empty($appointments)): ?>
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-<?= $archive ? 'archive' : 'calendar2-x' ?>"
                       style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3 mb-0">
                        <?= $archive ? 'Aucun rendez-vous archivé.' : 'Aucun rendez-vous à venir.' ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date & Heure</th>
                                <th>Client</th>
                                <th class="d-none d-md-table-cell">Email</th>
                                <th class="d-none d-lg-table-cell">Téléphone</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $apt): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold" style="color: var(--accent-color); font-size: 0.85rem;">
                                            <?= format_date($apt['date']) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="bi bi-clock"></i> <?= format_time($apt['heure']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($apt['prenom'] . ' ' . $apt['nom']) ?></div>
                                        <div class="text-muted small d-md-none"><?= htmlspecialchars($apt['email']) ?></div>
                                    </td>
                                    <td class="d-none d-md-table-cell text-muted small"><?= htmlspecialchars($apt['email']) ?></td>
                                    <td class="d-none d-lg-table-cell text-muted small"><?= htmlspecialchars($apt['telephone']) ?></td>
                                    <td>
                                        <?php if ($apt['statut'] === 'validé'): ?>
                                            <span class="badge bg-success">Validé</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">En attente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <button type="button" class="btn btn-sm btn-info"
                                                    data-bs-toggle="modal" data-bs-target="#detailModal"
                                                    onclick="loadDetails(<?= $apt['id'] ?>)"
                                                    title="Voir les détails">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <?php if ($apt['statut'] !== 'validé'): ?>
                                                <button type="button" class="btn btn-sm btn-success"
                                                        onclick="validateAppointment(<?= $apt['id'] ?>)"
                                                        title="Valider">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="deleteAppointment(<?= $apt['id'] ?>, <?= $apt['disponibilite_id'] ?>)"
                                                    title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white py-3">
                        <nav aria-label="Pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=1<?= $archive ? '&archive=1' : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                        <i class="bi bi-chevron-double-left"></i>
                                    </a>
                                </li>
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page - 1 ?><?= $archive ? '&archive=1' : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= $archive ? '&archive=1' : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?><?= $archive ? '&archive=1' : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $total_pages ?><?= $archive ? '&archive=1' : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                        <i class="bi bi-chevron-double-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <p class="text-center text-muted small mt-2 mb-0">
                            Page <?= $page ?> sur <?= $total_pages ?>
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Détails -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--accent-color); color: white;">
                    <h5 class="modal-title"><i class="bi bi-person-lines-fill"></i> Détails du rendez-vous</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <p class="text-center text-muted py-3"><i class="bi bi-hourglass"></i> Chargement...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
