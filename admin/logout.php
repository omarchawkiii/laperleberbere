<?php
/**
 * ADMIN - Déconnexion
 * LA PERLE BERBÈRE
 */

session_start();
session_destroy();
header('Location: login.php');
exit;
