<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
/* O admin só entra se: 
   - existir sessão
   - tiver tipo 'admin'
*/
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'cliente') {
    header("Location: ../login.php");
    exit;
}
