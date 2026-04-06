/**
 * LA PERLE BERBÈRE - Client JavaScript
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
    }
}

// Validation du formulaire côté client
function validateForm() {
    const prenom = document.getElementById('prenom').value.trim();
    const nom = document.getElementById('nom').value.trim();
    const email = document.getElementById('email').value.trim();
    const telephone = document.getElementById('telephone').value.trim();
    const slotId = document.getElementById('slotId').value;

    let errors = [];

    if (!prenom) {
        errors.push('Le prénom est obligatoire');
    }
    if (!nom) {
        errors.push('Le nom est obligatoire');
    }
    if (email && !isValidEmail(email)) {
        errors.push('L\'email n\'est pas valide');
    }
    if (!telephone) {
        errors.push('Le téléphone est obligatoire');
    } else if (!isValidPhone(telephone)) {
        errors.push('Le téléphone doit contenir au moins 10 chiffres');
    }
    if (!slotId) {
        errors.push('Veuillez sélectionner un créneau disponible');
    }

    if (errors.length > 0) {
        showMessage(errors.join('; '), 'danger');
        return false;
    }

    return true;
}

// Valider un email
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Valider un téléphone
function isValidPhone(phone) {
    const digits = phone.replace(/[^\d]/g, '');
    return digits.length >= 10;
}

// Soumettre le formulaire de réservation
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!validateForm()) {
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass"></i> Traitement...';

    const formData = {
        action: 'book',
        slot_id: document.getElementById('slotId').value,
        prenom: document.getElementById('prenom').value,
        nom: document.getElementById('nom').value,
        email: document.getElementById('email').value,
        telephone: document.getElementById('telephone').value,
        raison: document.getElementById('raison').value
    };

    $.post('../client/api_booking.php', formData, function(response) {
        if (response.success) {
            showSuccessMessage(response.data);
            document.getElementById('bookingForm').reset();
            document.getElementById('selectedSlotDisplay').style.display = 'none';
            document.getElementById('slots-container').innerHTML = '<p class="text-muted text-center"><i class="bi bi-info-circle"></i> Veuillez sélectionner une autre date et un créneau</p>';
        } else {
            showMessage(response.message, 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmer le rendez-vous';
        }
    }, 'json').fail(function() {
        showMessage('Erreur de communication avec le serveur', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-lg"></i> Confirmer le rendez-vous';
    });
});

// Afficher le message de succès
function showSuccessMessage(data) {
    let html = `
        <div class="text-center mb-3">
            <i class="bi bi-check-circle" style="font-size: 3rem; color: #27ae60;"></i>
        </div>
        <h6>Votre rendez-vous a été réservé avec succès!</h6>
        <p class="small">Un email de confirmation a été envoyé à une adresse.</p>
        <hr>
        <dl class="row small">
            <dt class="col-sm-5"><strong>Nom:</strong></dt>
            <dd class="col-sm-7">${data.prenom} ${data.nom}</dd>
            <dt class="col-sm-5"><strong>Email:</strong></dt>
            <dd class="col-sm-7">${data.email}</dd>
            <dt class="col-sm-5"><strong>Téléphone:</strong></dt>
            <dd class="col-sm-7">${data.telephone}</dd>
        </dl>
        <p class="small text-muted mt-3">
            <i class="bi bi-info-circle"></i> Statut: <strong>En attente de validation</strong>
        </p>
    `;
    document.getElementById('successContent').innerHTML = html;
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
}
