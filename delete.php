<?php

require_once __DIR__ . '/../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        $sql = 'DELETE FROM usuarios WHERE id = :id';

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Usuário deletado com sucesso!";
        } else {
            echo "Nenhum usuário deletado (ID inexistente).";
        }
    } else {
        echo "ID não informado.";
    }
} else {
    echo "Requisição inválida.";
}
