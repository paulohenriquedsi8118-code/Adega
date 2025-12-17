<?php
include 'conexao.php';

$mensagem = "";

// Função para buscar uma configuração específica
function getConfig(mysqli $conn, $nome) {
    $sql = "SELECT valor_config FROM configuracoes WHERE nome_config = '$nome'";
    $resultado = $conn->query($sql);
    if ($resultado && $resultado->num_rows > 0) {
        return $resultado->fetch_assoc()['valor_config'];
    }
    return 0; // Retorna 0% se não encontrar
}

// Lógica para salvar as configurações
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $taxa_debito = $_POST['taxa_debito'];
    $taxa_credito = $_POST['taxa_credito'];
    
    // Atualiza taxa de débito
    $sql_debito = "UPDATE configuracoes SET valor_config = '$taxa_debito' WHERE nome_config = 'taxa_debito'";
    $conn->query($sql_debito);
    
    // Atualiza taxa de crédito
    $sql_credito = "UPDATE configuracoes SET valor_config = '$taxa_credito' WHERE nome_config = 'taxa_credito'";
    $conn->query($sql_credito);
    
    $mensagem = "<div class='alert alert-success'>Configurações de taxas salvas com sucesso!</div>";
}

// Carrega os valores atuais para exibição
$taxa_debito_atual = getConfig($conn, 'taxa_debito');
$taxa_credito_atual = getConfig($conn, 'taxa_credito');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configurações do Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-center mb-4">⚙️ Configurações do Sistema</h1>
    
    <div class="mb-4">
        <a href="index.php" class="btn btn-secondary me-2">Voltar ao Estoque</a>
    </div>

    <?php echo $mensagem; ?>

    <div class="card p-4 shadow-sm">
        <h4 class="mb-3">Definir Taxas de Cartão (%)</h4>
        <form method="POST" action="configuracoes.php">
            <p class="text-muted">Essas taxas serão usadas para calcular o Lucro Líquido Real no Relatório de Vendas.</p>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Taxa de Débito (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="taxa_debito" class="form-control" value="<?php echo htmlspecialchars($taxa_debito_atual); ?>" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Taxa de Crédito (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="taxa_credito" class="form-control" value="<?php echo htmlspecialchars($taxa_credito_atual); ?>" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-success btn-lg w-100">Salvar Taxas</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php $conn->close(); ?>
</body>
</html>