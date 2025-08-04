<?php 
require_once __DIR__ . '/../conexao.php';

if (!isset($_POST['id'], $_POST['name'], $_POST['username'])) {
    echo "Dados incompletos.";
    exit;
}

$id = (int) $_POST['id'];
$novoNome = trim($_POST['name']);
$novoUsername = trim($_POST['username']);
$novaSenha = trim($_POST['password'] ?? '');

if (empty($novoNome) || empty($novoUsername)) {
    echo "Nome e usuário são obrigatórios.";
    exit;
}
$sqlCheck = "SELECT COUNT(*) FROM usuarios WHERE username = :username AND id != :id";
$stmtCheck = $pdo->prepare($sqlCheck);
$stmtCheck->bindParam(':username', $novoUsername);
$stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
$stmtCheck->execute();
$count = $stmtCheck->fetchColumn();

if ($count > 0) {
    echo "Erro: Usuário já existe.";
    exit;
}

if (!empty($novaSenha)) {
    $sql = 'UPDATE usuarios SET name = :name, username = :username, password = :password WHERE id = :id';
} else {
    $sql = 'UPDATE usuarios SET name = :name, username = :username WHERE id = :id';
}

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':name', $novoNome);
$stmt->bindParam(':username', $novoUsername);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

if (!empty($novaSenha)) {
    $stmt->bindParam(':password', $novaSenha);
}

if ($stmt->execute()) {
    echo "Usuário atualizado com sucesso.";
} else {
    echo "Erro ao atualizar usuário.";
}
