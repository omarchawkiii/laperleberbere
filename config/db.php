<?php
/**
 * Configuration Base de Données
 * LA PERLE BERBÈRE - Gestion de Rendez-vous
 */

// Paramètres de connexion
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'laperleberbere');
define('DB_CHARSET', 'utf8mb4');

// Créer la connexion
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données: ' . $e->getMessage());
}

/**
 * Fonctions utilitaires
 */

// Sécuriser les données
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Valider un email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Valider un téléphone (accepte plusieurs formats)
function validate_phone($phone) {
    $phone = preg_replace('/[^0-9+\-\s]/', '', $phone);
    return strlen($phone) >= 10;
}

// Formater une date en français
function format_date($date) {
    $dates = [
        'Monday' => 'Lundi',
        'Tuesday' => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday' => 'Jeudi',
        'Friday' => 'Vendredi',
        'Saturday' => 'Samedi',
        'Sunday' => 'Dimanche'
    ];
    
    $months = [
        'January' => 'Janvier',
        'February' => 'Février',
        'March' => 'Mars',
        'April' => 'Avril',
        'May' => 'Mai',
        'June' => 'Juin',
        'July' => 'Juillet',
        'August' => 'Août',
        'September' => 'Septembre',
        'October' => 'Octobre',
        'November' => 'Novembre',
        'December' => 'Décembre'
    ];
    
    $day_name = date('l', strtotime($date));
    $month_name = date('F', strtotime($date));
    
    $day_name = $dates[$day_name] ?? $day_name;
    $month_name = $months[$month_name] ?? $month_name;
    
    return $day_name . ' ' . date('d', strtotime($date)) . ' ' . $month_name . ' ' . date('Y', strtotime($date));
}

// Formater l'heure
function format_time($time) {
    return date('H\hi', strtotime($time));
}
?>
