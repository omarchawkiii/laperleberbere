<?php
/**
 * API - CLIENT - Réservation de rendez-vous
 * LA PERLE BERBÈRE
 */

header('Content-Type: application/json');
require_once '../config/db.php';

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Erreur'];

try {
    if ($action === 'book') {
        // Récupérer les données
        $slot_id = (int)($_POST['slot_id'] ?? 0);
        $prenom = sanitize($_POST['prenom'] ?? '');
        $nom = sanitize($_POST['nom'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $telephone = sanitize($_POST['telephone'] ?? '');
        $raison = sanitize($_POST['raison'] ?? '');

        // Validation
        $errors = [];

        if (empty($prenom)) {
            $errors[] = 'Le prénom est obligatoire';
        }
        if (empty($nom)) {
            $errors[] = 'Le nom est obligatoire';
        }
        if (empty($email)) {
            $errors[] = 'L\'email est obligatoire';
        } elseif (!validate_email($email)) {
            $errors[] = 'L\'email n\'est pas valide';
        }
        if (empty($telephone)) {
            $errors[] = 'Le téléphone est obligatoire';
        } elseif (!validate_phone($telephone)) {
            $errors[] = 'Le téléphone n\'est pas valide (minimum 10 chiffres)';
        }
        if ($slot_id <= 0) {
            $errors[] = 'Veuillez sélectionner un créneau';
        }

        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }

        // Vérifier que le créneau existe et est libre
        $stmt = $pdo->prepare('SELECT id, statut FROM disponibilites WHERE id = ?');
        $stmt->execute([$slot_id]);
        $slot = $stmt->fetch();

        if (!$slot) {
            throw new Exception('Créneau invalide');
        }

        if ($slot['statut'] !== 'libre') {
            throw new Exception('Ce créneau n\'est plus disponible');
        }

        // Commencer une transaction
        $pdo->beginTransaction();

        try {
            // Ajouter le rendez-vous
            $stmt = $pdo->prepare('
                INSERT INTO rendezvous (nom, prenom, email, telephone, raison, disponibilite_id, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([$nom, $prenom, $email, $telephone, $raison, $slot_id, 'en attente']);
            $appointment_id = $pdo->lastInsertId();

            // Marquer le créneau comme réservé
            $stmt = $pdo->prepare('UPDATE disponibilites SET statut = ? WHERE id = ?');
            $stmt->execute(['réservé', $slot_id]);

            $pdo->commit();

            $response['success'] = true;
            $response['message'] = 'Rendez-vous confirmé!';
            $response['appointment_id'] = $appointment_id;
            $response['data'] = [
                'prenom' => $prenom,
                'nom' => $nom,
                'email' => $email,
                'telephone' => $telephone
            ];
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    } else {
        throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
