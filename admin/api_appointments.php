<?php
/**
 * API - ADMIN - Gestion des rendez-vous
 * LA PERLE BERBÈRE
 */

header('Content-Type: application/json');
require_once '../config/db.php';

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Action invalide'];

try {
    switch ($action) {
        case 'get_details':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare('
                SELECT 
                    r.id, 
                    r.nom, 
                    r.prenom, 
                    r.email, 
                    r.telephone, 
                    r.raison, 
                    r.statut,
                    d.date,
                    d.heure,
                    r.created_at
                FROM rendezvous r
                JOIN disponibilites d ON r.disponibilite_id = d.id
                WHERE r.id = ?
            ');
            $stmt->execute([$id]);
            $appointment = $stmt->fetch();
            
            if ($appointment) {
                $response['success'] = true;
                $response['data'] = $appointment;
            } else {
                $response['message'] = 'Rendez-vous non trouvé';
            }
            break;

        case 'validate':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare('UPDATE rendezvous SET statut = ? WHERE id = ?');
            $stmt->execute(['validé', $id]);
            $response['success'] = true;
            $response['message'] = 'Rendez-vous validé!';
            break;

        case 'delete':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare('DELETE FROM rendezvous WHERE id = ?');
            $stmt->execute([$id]);
            $response['success'] = true;
            $response['message'] = 'Rendez-vous supprimé!';
            break;

        case 'release':
            $appointmentId = (int)$_POST['id'];
            
            // Récupérer le disponibilite_id
            $stmt = $pdo->prepare('SELECT disponibilite_id FROM rendezvous WHERE id = ?');
            $stmt->execute([$appointmentId]);
            $apt = $stmt->fetch();
            
            if (!$apt) {
                throw new Exception('Rendez-vous non trouvé');
            }
            
            $slotId = $apt['disponibilite_id'];
            
            // Supprimer le rendez-vous
            $stmt = $pdo->prepare('DELETE FROM rendezvous WHERE id = ?');
            $stmt->execute([$appointmentId]);
            
            // Remettre le créneau en libre
            $stmt = $pdo->prepare('UPDATE disponibilites SET statut = ? WHERE id = ?');
            $stmt->execute(['libre', $slotId]);
            
            $response['success'] = true;
            $response['message'] = 'Rendez-vous supprimé et créneau libéré!';
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
