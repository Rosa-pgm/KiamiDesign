<?php
// includes/obra_estado.php

function atualizarEstadoObra(PDO $pdo, int $obra_id, string $estado_nome) {
    $stmt = $pdo->prepare("
        UPDATE obra 
        SET estado_id = (
            SELECT id FROM estado_obra WHERE nome = ?
        )
        WHERE id = ?
    ");
    $stmt->execute([$estado_nome, $obra_id]);
}
