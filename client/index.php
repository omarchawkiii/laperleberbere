<?php
/**
 * CLIENT - Prise de rendez-vous
 * LA PERLE BERBÈRE
 */

require_once '../config/db.php';

// Récupérer les créneaux disponibles par date
$query = '
    SELECT
        DATE_FORMAT(d.date, "%Y-%m-%d") as date_key,
        d.id,
        d.date,
        d.heure,
        d.statut
    FROM disponibilites d
    WHERE d.date >= CURDATE() AND d.statut = "libre"
    ORDER BY d.date ASC, d.heure ASC
';

$stmt = $pdo->query($query);
$slots = $stmt->fetchAll();

// Grouper par date
$slotsByDate = [];
foreach ($slots as $slot) {
    $slotsByDate[$slot['date_key']][] = $slot;
}

$available_dates = array_keys($slotsByDate);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prendre un rendez-vous - LA PERLE BERBÈRE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.14.0/themes/base/jquery-ui.css">
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
                        <a class="nav-link" href="../"><i class="bi bi-house"></i> Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4" style="max-width: 960px;">

        <!-- En-tête -->
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold" style="color: var(--accent-color);">
                <i class="bi bi-calendar-heart"></i> Prendre un rendez-vous
            </h1>
            <p class="text-muted mb-0">Choisissez une date, un créneau, puis remplissez vos informations.</p>
        </div>

        <!-- Indicateur d'étapes -->
        <div class="steps-indicator mb-4" id="stepsIndicator">
            <div class="step active" id="step1">
                <div class="step-number">1</div>
                <div class="step-label">Date</div>
            </div>
            <div class="step-line" id="line1"></div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <div class="step-label">Créneau</div>
            </div>
            <div class="step-line" id="line2"></div>
            <div class="step" id="step3">
                <div class="step-number">3</div>
                <div class="step-label">Vos infos</div>
            </div>
        </div>

        <?php if (empty($available_dates)): ?>
            <div class="alert alert-warning text-center py-4">
                <i class="bi bi-calendar-x" style="font-size: 2.5rem;"></i>
                <p class="mt-2 mb-1 fw-bold">Aucun créneau disponible pour le moment.</p>
                <p class="small mb-0">Veuillez revenir ultérieurement ou nous contacter directement.</p>
            </div>
        <?php else: ?>

        <div class="row g-4">
            <!-- Colonne gauche : calendrier + créneaux -->
            <div class="col-lg-5">

                <!-- Calendrier -->
                <div class="card mb-3">
                    <div class="card-header" style="background: var(--accent-color); color: white;">
                        <i class="bi bi-calendar3"></i> Choisir une date
                    </div>
                    <div class="card-body p-2">
                        <div id="datepicker"></div>
                    </div>
                </div>

                <!-- Créneaux -->
                <div class="card" id="slotsCard">
                    <div class="card-header d-flex justify-content-between align-items-center"
                         style="background: var(--accent-color); color: white;">
                        <span><i class="bi bi-clock"></i> Créneaux disponibles</span>
                        <span id="selectedDateLabel" class="badge bg-light text-dark fw-normal" style="display:none;"></span>
                    </div>
                    <div class="card-body">
                        <div id="slots-container">
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-arrow-up-circle" style="font-size: 1.8rem; color: var(--accent-color); opacity:0.5;"></i>
                                <p class="mt-2 mb-0 small">Sélectionnez une date<br>pour voir les créneaux</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Colonne droite : formulaire -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header" style="background: var(--accent-color); color: white;">
                        <i class="bi bi-person-check"></i> Vos informations
                    </div>
                    <div class="card-body p-4">

                        <form id="bookingForm">
                            <input type="hidden" id="slotId" name="slot_id" value="">

                            <!-- Zone messages -->
                            <div id="message-zone"></div>

                            <!-- Créneau sélectionné -->
                            <div id="selectedSlotDisplay" style="display: none;" class="selected-slot-badge mb-3">
                                <i class="bi bi-check-circle-fill"></i>
                                <div>
                                    <div class="small opacity-75">Créneau sélectionné</div>
                                    <div class="fw-bold" id="slotDisplayText"></div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="prenom" name="prenom"
                                           placeholder="Lilla" required>
                                </div>
                                <div class="col-sm-6">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom" name="nom"
                                           placeholder="Ait Larbi" required>
                                </div>
                                <div class="col-12">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email"
                                               placeholder="Email@email.com" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                        <input type="tel" class="form-control" id="telephone" name="telephone"
                                               placeholder="06 12 34 56 78" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="raison" class="form-label">
                                        Raison de la visite
                                        <span class="text-muted fw-normal">(optionnel)</span>
                                    </label>
                                    <textarea class="form-control" id="raison" name="raison" rows="3"
                                              placeholder="Décrivez brièvement l'objet de votre visite..."></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-4 py-3" id="submitBtn"
                                    style="font-size: 1rem; font-weight: 600;">
                                <i class="bi bi-check-lg"></i> Confirmer le rendez-vous
                            </button>

                            <p class="text-muted text-center small mt-3 mb-0">
                                <i class="bi bi-shield-check"></i> Vos données sont traitées en toute confidentialité.
                            </p>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </main>

    <!-- Modal de confirmation -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--success-color); color: white; border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title"><i class="bi bi-check-circle"></i> Rendez-vous confirmé !</h5>
                </div>
                <div class="modal-body p-4" id="successContent"></div>
                <div class="modal-footer">
                    <a href="../" class="btn btn-outline-secondary">
                        <i class="bi bi-house"></i> Accueil
                    </a>
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouveau rendez-vous
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/client.js"></script>
    <script>
    const slotsByDate = <?= json_encode($slotsByDate, JSON_HEX_APOS) ?>;

    // ---- Étapes ----
    function setStep(n) {
        [1, 2, 3].forEach(i => {
            const el = document.getElementById('step' + i);
            el.classList.remove('active', 'done');
            if (i < n) el.classList.add('done');
            else if (i === n) el.classList.add('active');
        });
        [1, 2].forEach(i => {
            const line = document.getElementById('line' + i);
            if (line) line.classList.toggle('done', i < n);
        });
    }

    // ---- Datepicker ----
    $('#datepicker').datepicker({
        minDate: 0,
        inline: true,
        dateFormat: 'yy-mm-dd',
        dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
        monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
        beforeShowDay: function(date) {
            const dateStr = $.datepicker.formatDate('yy-mm-dd', date);
            const isAvailable = slotsByDate.hasOwnProperty(dateStr) && slotsByDate[dateStr].length > 0;
            return [isAvailable, isAvailable ? 'available-date' : ''];
        },
        onSelect: function(dateText) {
            displaySlots(dateText);
            setStep(2);
        }
    });

    // ---- Affichage des créneaux ----
    function displaySlots(dateStr) {
        const slots = slotsByDate[dateStr] || [];
        const container = document.getElementById('slots-container');
        const label = document.getElementById('selectedDateLabel');

        // Mise à jour du label de date
        const parts = dateStr.split('-');
        const months = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
        label.textContent = parseInt(parts[2]) + ' ' + months[parseInt(parts[1]) - 1] + ' ' + parts[0];
        label.style.display = 'inline-block';

        if (slots.length === 0) {
            container.innerHTML = `
                <div class="text-center text-warning py-3">
                    <i class="bi bi-exclamation-circle" style="font-size:1.8rem;"></i>
                    <p class="mt-2 mb-0 small">Aucun créneau disponible<br>pour cette date.</p>
                </div>`;
            return;
        }

        let html = '<div class="slots-grid">';
        slots.forEach(slot => {
            const t = slot.heure.substring(0, 5);
            html += `
                <button type="button" class="slot-button"
                        data-slot-id="${slot.id}"
                        data-date="${slot.date}"
                        data-time="${slot.heure}">
                    <span class="slot-icon"><i class="bi bi-clock"></i></span>
                    <span class="slot-time">${t}h</span>
                </button>`;
        });
        html += '</div>';
        container.innerHTML = html;

        document.querySelectorAll('.slot-button').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.slot-button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectSlot(this.dataset.slotId, this.dataset.date, this.dataset.time);
                setStep(3);
                // Scroll vers le formulaire sur mobile
                if (window.innerWidth < 992) {
                    document.getElementById('bookingForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    function selectSlot(slotId, date, time) {
        document.getElementById('slotId').value = slotId;
        const t = time.substring(0, 5);
        const parts = date.split('-');
        const months = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        const days = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        const d = new Date(date + 'T00:00:00');
        const dateFormatted = days[d.getDay()] + ' ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        document.getElementById('slotDisplayText').textContent = dateFormatted + ' à ' + t + 'h';
        document.getElementById('selectedSlotDisplay').style.display = 'flex';
    }
    </script>
</body>
</html>
