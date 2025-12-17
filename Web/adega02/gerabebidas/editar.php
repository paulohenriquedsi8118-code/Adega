<?php
include 'conexao.php';

// 1. Lógica para PROCESSAR A ATUALIZAÇÃO (se o formulário foi enviado)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_produto'])) {
    
    // Captura os dados do formulário
    $id = $conn->real_escape_string($_POST['id_produto']);
    $nome = $conn->real_escape_string($_POST['nome']);
    $marca = $conn->real_escape_string($_POST['marca']);
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $quantidade = $_POST['quantidade'];
    $valor_pago = $_POST['valor_pago'];
    $valor_venda = $_POST['valor_venda'];
    $data_compra = $_POST['data_compra'];
    $vencimento = $_POST['vencimento'];

    // Monta a query SQL de UPDATE
    $sql_update = "UPDATE vinhos SET 
                   nome = '$nome', 
                   marca = '$marca', 
                   tipo = '$tipo', 
                   quantidade = '$quantidade', 
                   valor_pago = '$valor_pago', 
                   valor_venda = '$valor_venda', 
                   data_compra = '$data_compra', 
                   vencimento = '$vencimento'
                   WHERE id = '$id'";

    // Executa a query
    if ($conn->query($sql_update) === TRUE) {
        echo "<script>alert('Bebida ID $id atualizada com sucesso!'); window.location.href='index.php';</script>";
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }

} else if (isset($_GET['id'])) {
    
    // 2. Lógica para CARREGAR DADOS EXISTENTES (ao abrir a página)
    $id_produto = $conn->real_escape_string($_GET['id']);
    
    $sql_busca = "SELECT * FROM vinhos WHERE id = '$id_produto'";
    $resultado = $conn->query($sql_busca);

    if ($resultado->num_rows > 0) {
        $linha = $resultado->fetch_assoc();
        // Os dados estão em $linha e serão usados para preencher o formulário
    } else {
        echo "<script>alert('Produto não encontrado!'); window.location.href='index.php';</script>";
        exit;
    }
} else {
    // Se não houver ID, volta para a página principal
    header('Location: index.php');
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto - ID <?php echo $linha['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-center mb-4">✍️ Editar Produto (ID: <?php echo $linha['id']; ?>)</h1>
    
    <div class="mb-4">
        <a href="index.php" class="btn btn-secondary">Voltar ao Estoque</a>
    </div>

    <div class="card p-4 mb-5 shadow-sm">
        <h4>Atualizar Dados</h4>
        <form method="POST" action="editar.php">
            <input type="hidden" name="id_produto" value="<?php echo $linha['id']; ?>">
            
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label class="form-label">Nome da Bebida</label>
                    <input type="text" name="nome" class="form-control" value="<?php echo $linha['nome']; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Marca/Fabricante</label>
                    <input type="text" name="marca" class="form-control" value="<?php echo $linha['marca']; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipo</label>
                    <input type="text" name="tipo" class="form-control" value="<?php echo $linha['tipo']; ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Quantidade em Estoque</label>
                    <input type="number" name="quantidade" class="form-control" value="<?php echo $linha['quantidade']; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Valor Pago (Custo)</label>
                    <input type="number" step="0.01" name="valor_pago" class="form-control" value="<?php echo $linha['valor_pago']; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Valor de Venda</label>
                    <input type="number" step="0.01" name="valor_venda" class="form-control" value="<?php echo $linha['valor_venda']; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Data da Compra</label>
                    <input type="date" name="data_compra" class="form-control" value="<?php echo $linha['data_compra']; ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Data de Vencimento</label>
                    <input type="date" name="vencimento" class="form-control" value="<?php echo $linha['vencimento']; ?>">
                </div>
                <div class="col-md-8 mb-3 pt-4">
                    <button type="submit" class="btn btn-info w-100">Salvar Alterações</button>
                </div>
            </div>
        </form>
    </div>
</div>

</body>
</html>