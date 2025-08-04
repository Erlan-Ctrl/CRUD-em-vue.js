<?php

require_once __DIR__ . '/../conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $_name = trim($_POST['name'] ?? '');
    $_username = trim($_POST['username'] ?? '');
    $_password = $_POST['password'] ?? '';

    if (empty($_name) || empty($_username) || empty($_password)) {
        echo "Todos os campos são obrigatórios.";
        exit;
    }

    $sqlCheck = "SELECT COUNT(*) FROM usuarios WHERE username = :username";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindParam(':username', $_username);
    $stmtCheck->execute();
    $count = $stmtCheck->fetchColumn();

    if ($count > 0) {
        echo "Erro: Usuário já existe.";
        exit;
    }

    $sql = 'INSERT INTO usuarios (name, username, password) VALUES (:name, :username, :password)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $_name);
    $stmt->bindParam(':username', $_username);
    $stmt->bindParam(':password', $_password);

    
    if ($stmt->execute()) {
        echo "Usuário inserido com sucesso!";
    } else {
        echo "Erro ao inserir usuário.";
    }
}
?>
