<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexao.php';

if (!isset($_GET['id'])) {
  echo json_encode(['erro' => 'ID não fornecido']);
  exit;
}

$id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT id, name, username FROM usuarios WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo json_encode($usuario);
    } else {
        echo json_encode(['erro' => 'Usuário não encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>
