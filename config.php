<?php
// Vérifier si la fonction existe déjà
if (!function_exists('est_connecte')) {
    function est_connecte() {
        return isset($_SESSION['user_role']);
    }
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'immobilier');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données
try {
    $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Erreur de connexion à la base de données : '.$e->getMessage());
}

// Démarrer la session
session_start();

// Fonction pour sécuriser les données
function securiser($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Vérifier si l'utilisateur est connecté
function est_connecte() {
    return isset($_SESSION['user_role']);
}
?>