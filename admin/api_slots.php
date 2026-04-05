<?php
/**
 * API - ADMIN - Gestion des créneaux
 * LA PERLE BERBÈRE
 */

header('Content-Type: application/json');
require_once '../config/db.php';

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Action invalide'];

try {
    switch ($action) {
        case 'add':
            $date = $_POST['date'] ?? '';
            $heure = $_POST['heure'] ?? '';
            
            if (empty($date) || empty($heure)) {
                throw new Exception('Date et heure sont obligatoires');
            }
            
            // Vérifier que la date est future
            if (strtotime($date) < strtotime(date('Y-m-d'))) {
                throw new Exception('La date doit être dans le futur');
            }
            
            // Vérifier l'unicité
            $stmt = $pdo->prepare('SELECT id FROM disponibilites WHERE date = ? AND heure = ?');
            $stmt->execute([$date, $heure]);
            
            if ($stmt->fetch()) {
                throw new Exception('Ce créneau existe déjà');
            }
            
            // Ajouter le créneau
            $stmt = $pdo->prepare('INSERT INTO disponibilites (date, heure, statut) VALUES (?, ?, ?)');
            $stmt->execute([$date, $heure, 'libre']);
            
            $response['success'] = true;
            $response['message'] = 'Créneau ajouté avec succès!';
            break;

        case 'delete':
            $id = (int)$_POST['id'];
            
            // Vérifier s'il y a une réservation
            $stmt = $pdo->prepare('SELECT id FROM rendezvous WHERE disponibilite_id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->fetch()) {
                throw new Exception('Ce créneau a une réservation. Libérez-le d\'abord!');
            }
            
            // Supprimer le créneau
            $stmt = $pdo->prepare('DELETE FROM disponibilites WHERE id = ?');
            $stmt->execute([$id]);
            
            $response['success'] = true;
            $response['message'] = 'Créneau supprimé!';
            break;

        case 'release':
            $id = (int)$_POST['id'];
            
            // Récupérer le rendez-vous
            $stmt = $pdo->prepare('SELECT id FROM rendezvous WHERE disponibilite_id = ?');
            $stmt->execute([$id]);
            $appointment = $stmt->fetch();
            
            if ($appointment) {
                // Supprimer le rendez-vous
                $stmt = $pdo->prepare('DELETE FROM rendezvous WHERE disponibilite_id = ?');
                $stmt->execute([$id]);
            }
            
            // Remettre le créneau en libre
            $stmt = $pdo->prepare('UPDATE disponibilites SET statut = ? WHERE id = ?');
            $stmt->execute(['libre', $id]);
            
            $response['success'] = true;
            $response['message'] = 'Créneau libéré!';
            break;

        default:
            $response['message'] = 'Action non reconnue';
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
