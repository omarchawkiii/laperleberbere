/**
 * LA PERLE BERBÈRE - Admin JavaScript
 */

// Afficher un message
function showMessage(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    const messageZone = document.getElementById('message-zone');
    if (messageZone) {
        messageZone.innerHTML = alertHtml;
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Charger les détails d'un rendez-vous
function loadDetails(appointmentId) {
    $.post('api_appointments.php',
        { action: 'get_details', id: appointmentId },
        function(response) {
            if (response.success) {
                const apt = response.data;
                let html = `
                    <dl class="row">
                        <dt class="col-sm-4">Nom Prénom:</dt>
                        <dd class="col-sm-8"><strong>${apt.prenom} ${apt.nom}</strong></dd>

                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8"><a href="mailto:${apt.email}">${apt.email}</a></dd>

                        <dt class="col-sm-4">Téléphone:</dt>
                        <dd class="col-sm-8"><a href="tel:${apt.telephone}">${apt.telephone}</a></dd>

                        <dt class="col-sm-4">Date et Heure:</dt>
                        <dd class="col-sm-8"><strong>${formatDate(apt.date)} à ${formatTime(apt.heure)}</strong></dd>

                        <dt class="col-sm-4">Statut:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-${apt.statut === 'validé' ? 'success' : 'warning'} text-${apt.statut === 'validé' ? 'white' : 'dark'}">
                                ${apt.statut.charAt(0).toUpperCase() + apt.statut.slice(1)}
                            </span>
                        </dd>
                `;
                
                if (apt.raison) {
                    html += `
                        <dt class="col-sm-4">Raison:</dt>
                        <dd class="col-sm-8">${apt.raison}</dd>
                    `;
                }

                html += `
                        <dt class="col-sm-4">Date de création:</dt>
                        <dd class="col-sm-8"><small class="text-muted">${apt.created_at}</small></dd>
                    </dl>
                `;
                document.getElementById('detailContent').innerHTML = html;
            } else {
                document.getElementById('detailContent').innerHTML = '<p class="text-danger">Erreur: ' + response.message + '</p>';
            }
        },
        'json'
    );
}

// Valider un rendez-vous
function validateAppointment(appointmentId) {
    if (confirm('Valider ce rendez-vous?')) {
        $.post('api_appointments.php',
            { action: 'validate', id: appointmentId },
            function(response) {
                if (response.success) {
                    showMessage('✓ ' + response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showMessage('✗ Erreur: ' + response.message, 'danger');
                }
            },
            'json'
        );
    }
}

// Supprimer un rendez-vous
function deleteAppointment(appointmentId, slotId) {
    const msg = confirm('Êtes-vous sûr de vouloir supprimer ce rendez-vous?\n\nLe créneau sera libéré.');
    if (msg) {
        $.post('api_appointments.php',
            { action: 'release', id: appointmentId },
            function(response) {
                if (response.success) {
                    showMessage('✓ ' + response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showMessage('✗ Erreur: ' + response.message, 'danger');
                }
            },
            'json'
        );
    }
}

// Formater une date
function formatDate(dateStr) {
    const date = new Date(dateStr + 'T00:00:00');
    const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    const days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    return days[date.getDay()] + ' ' + date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
}

// Formater une heure
function formatTime(timeStr) {
    return timeStr.substring(0, 5) + 'h';
}
