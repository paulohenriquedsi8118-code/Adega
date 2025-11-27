<?php
// 1. Inclui o arquivo de conexão
include 'conexao.php';
session_start();

// ***********************************************
// Lógica para Cálculo da Venda do Dia
// ***********************************************

// 1. Define o dia de hoje no formato YYYY-MM-DD
$hoje = date('Y-m-d');

// 2. Consulta SQL para somar o total de vendas do dia
$sql_vendas_hoje = "
    SELECT SUM(v.quantidade_vendida * v.valor_venda_unitario) AS total_hoje
    FROM vendas v
    WHERE DATE(v.data_venda) = '$hoje'
";

$resultado_hoje = $conn->query($sql_vendas_hoje);
$total_venda_dia = 0; // Valor padrão se não houver vendas

if ($resultado_hoje && $resultado_hoje->num_rows > 0) {
    $linha_hoje = $resultado_hoje->fetch_assoc();
    // Pega o valor, ou zero se for nulo (sem vendas)
    $total_venda_dia = $linha_hoje['total_hoje'] ?? 0;
}

// Formata o valor para exibição (R$ 0,00)
$total_venda_dia_formatado = "R$ " . number_format($total_venda_dia, 2, ',', '.');

// ***********************************************
// Lógica para Cálculo do Caixa Diário
// ***********************************************

// Consulta SQL para vendas em dinheiro do dia
$sql_caixa_dia = "
    SELECT 
        SUM(v.valor_venda_unitario * v.quantidade_vendida) as total_dinheiro,
        SUM(v.troco) as total_troco,
        (SUM(v.valor_venda_unitario * v.quantidade_vendida) - COALESCE(SUM(v.troco), 0)) as caixa_esperado
    FROM vendas v
    WHERE DATE(v.data_venda) = '$hoje' 
    AND v.forma_pagamento = 'Dinheiro'
";

$resultado_caixa = $conn->query($sql_caixa_dia);
$total_dinheiro = 0;
$total_troco = 0;
$caixa_esperado = 0;

if ($resultado_caixa && $resultado_caixa->num_rows > 0) {
    $linha_caixa = $resultado_caixa->fetch_assoc();
    $total_dinheiro = $linha_caixa['total_dinheiro'] ?? 0;
    $total_troco = $linha_caixa['total_troco'] ?? 0;
    $caixa_esperado = $linha_caixa['caixa_esperado'] ?? 0;
}

// Formata os valores
$total_dinheiro_formatado = "R$ " . number_format($total_dinheiro, 2, ',', '.');
$total_troco_formatado = "R$ " . number_format($total_troco, 2, ',', '.');
$caixa_esperado_formatado = "R$ " . number_format($caixa_esperado, 2, ',', '.');

// ***********************************************
// Lógica para Busca
// ***********************************************
$condicao = "";
if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $termo = $conn->real_escape_string($_GET['busca']);
    // Adiciona a cláusula WHERE para buscar em nome, marca OU tipo. O % permite buscar por parte da palavra.
    $condicao = " WHERE nome LIKE '%$termo%' OR marca LIKE '%$termo%' OR tipo LIKE '%$termo%' ";
    // Adicionei a função de notificar que está em busca para o usuário
    $mensagem_busca = "<div class='alert alert-info'>Exibindo resultados para: <strong>" . $termo . "</strong>. <a href='index.php' class='alert-link'>Limpar Busca</a></div>";
} else {
    $mensagem_busca = "";
}

// ***********************************************
// 2. Lógica para RECEBER e SALVAR dados (AGORA COM ESTOQUE MÍNIMO)
// ***********************************************
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['venda_submit'])) {
    // Captura os dados do formulário
    $nome = $conn->real_escape_string($_POST['nome']);
    $marca = $conn->real_escape_string($_POST['marca']);
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $quantidade = $_POST['quantidade'];
    $valor_pago = $_POST['valor_pago'];
    $valor_venda = $_POST['valor_venda'];
    $data_compra = $_POST['data_compra']; // Formato YYYY-MM-DD
    $vencimento = $_POST['vencimento'];   // Formato YYYY-MM-DD
    $estoque_minimo = $_POST['estoque_minimo'] ?? 5; // NOVO CAMPO - Usando 5 como padrão se não for enviado.

    // Monta a query SQL com o novo campo 'estoque_minimo'
    $sql = "INSERT INTO vinhos (nome, marca, tipo, quantidade, valor_pago, valor_venda, data_compra, vencimento, estoque_minimo) 
            VALUES ('$nome', '$marca', '$tipo', '$quantidade', '$valor_pago', '$valor_venda', '$data_compra', '$vencimento', '$estoque_minimo')";

    // Executa a query
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Bebida cadastrada com sucesso!'); window.location.href='index.php';</script>";
    } else {
        echo "Erro ao cadastrar: " . $conn->error;
    }
}

// 3. Lógica para EXCLUIR dados (mantida)
if (isset($_GET['acao']) && $_GET['acao'] == 'excluir' && isset($_GET['id'])) {
    $id_excluir = $conn->real_escape_string($_GET['id']);
    // NOTA: É uma boa prática excluir primeiro as vendas associadas (vendas.produto_id)
    // Se a chave estrangeira foi configurada com ON DELETE CASCADE, esta etapa é desnecessária.
    // Caso contrário, seria necessário: DELETE FROM vendas WHERE produto_id = '$id_excluir';
    
    $sql_delete = "DELETE FROM vinhos WHERE id = '$id_excluir'";
    
    if ($conn->query($sql_delete) === TRUE) {
        echo "<script>alert('Bebida excluída com sucesso!'); window.location.href='index.php';</script>";
    } else {
        echo "Erro ao excluir: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gera Bebidas - Controle de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .venda-form input[type="number"] {
            width: 70px;
        }
        .card-caixa {
            border-left: 4px solid #28a745 !important;
        }
    </style>
    <!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Seu Sistema</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SEU CSS CUSTOMIZADO -->
    <link rel="stylesheet" href="styles.css">
</head>
</head>
<body class="bg-light">

<?php
// Mostrar mensagens de sucesso/erro
if (isset($_SESSION['mensagem_sucesso'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . $_SESSION['mensagem_sucesso'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['mensagem_sucesso']);
}

if (isset($_SESSION['mensagem_erro'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . $_SESSION['mensagem_erro'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['mensagem_erro']);
}
?>

<div class="container mt-5">
    <h1 class="text-center mb-4">🥂 Controle de Estoque (Gera Bebidas)</h1>
    
    <!-- BOTÕES DE NAVEGAÇÃO -->
    <div class="mb-4 d-flex gap-3 flex-wrap">
        <a href="vendas.php" class="btn btn-warning btn-lg">Realizar Nova Venda</a>
        <a href="relatorio_vendas.php" class="btn btn-primary btn-lg">Visualizar Relatório de Vendas</a>
        <a href="reposicao.php" class="btn btn-danger btn-lg">Lista de Reposição</a>
        <a href="configuracoes.php" class="btn btn-info btn-lg">⚙️ Configurações</a> 
    </div>

    <!-- CARDS: MÉTRICAS DO DIA -->
    <div class="row mb-5">
        <!-- Card: Vendas do Dia -->
        <div class="col-md-4">
            <div class="card text-white bg-info shadow-lg">
                <div class="card-body">
                    <h5 class="card-title">Vendas Brutas de Hoje (<?php echo date('d/m'); ?>)</h5>
                    <p class="card-text fs-2">
                        <strong><?php echo $total_venda_dia_formatado; ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- NOVO CARD: Caixa Diário -->
        <div class="col-md-4">
            <div class="card text-white bg-success shadow-lg card-caixa">
                <div class="card-body">
                    <h5 class="card-title">💵 Caixa Diário (Dinheiro)</h5>
                    <p class="card-text fs-2 mb-1">
                        <strong><?php echo $caixa_esperado_formatado; ?></strong>
                    </p>
                    <small class="opacity-75">
                        Vendas: <?php echo $total_dinheiro_formatado; ?><br>
                        Troco: <?php echo $total_troco_formatado; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Card para futuras métricas -->
        <div class="col-md-4">
            <div class="card text-white bg-warning shadow-lg">
                <div class="card-body">
                    <h5 class="card-title">Outras Métricas</h5>
                    <p class="card-text fs-2">
                        <strong>Em Breve</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-4 mb-5 shadow-sm">
        <h4>Adicionar Nova Bebida</h4>
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-5 mb-3"><input type="text" name="nome" class="form-control" placeholder="Nome da Bebida" required></div>
                <div class="col-md-4 mb-3"><input type="text" name="marca" class="form-control" placeholder="Marca/Fabricante" required></div>
                <div class="col-md-3 mb-3"><input type="text" name="tipo" class="form-control" placeholder="Tipo (Vinho/Cerveja/Destilado)"></div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label visually-hidden">Qtd</label>
                    <input type="number" name="quantidade" class="form-control" placeholder="Quantidade" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Valor Pago (Custo)</label>
                    <input type="number" step="0.01" name="valor_pago" class="form-control" placeholder="R$ Custo" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Valor de Venda</label>
                    <input type="number" step="0.01" name="valor_venda" class="form-control" placeholder="R$ Venda" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Data da Compra</label>
                    <input type="date" name="data_compra" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Data de Vencimento</label>
                    <input type="date" name="vencimento" class="form-control">
                </div>
                <!-- NOVO CAMPO: ESTOQUE MÍNIMO -->
                <div class="col-md-4 mb-3"> 
                    <label class="form-label">Estoque Mínimo</label>
                    <input type="number" name="estoque_minimo" class="form-control" value="5">
                </div>
                
                <div class="col-md-4 mb-3 pt-4">
                    <button type="submit" class="btn btn-success w-100">Salvar no Estoque</button>
                </div>
            </div>
        </form>
    </div>

    <hr>

    <div class="card p-4 mb-3 shadow-sm">
        <form method="GET" action="index.php" class="row g-2 align-items-center">
            <div class="col-md-10">
                <input type="text" name="busca" class="form-control" placeholder="Pesquisar por Nome, Marca ou Tipo..." value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </div>
        </form>
    </div>
    
    <?php echo $mensagem_busca; ?> 

    <div class="card p-4 shadow-sm">
        <h4 class="mb-3">Estoque Atual Registrado</h4>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Marca</th>
                        <th>Tipo</th>
                        <th>Qtd</th>
                        <th>Custo</th>
                        <th>Venda</th>
                        <th>Compra</th>
                        <th>Venc.</th>
                        <th>Lucro Total</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // ************* MUDANÇA NA QUERY: Adiciona a Condição de Busca *************
                    $sql_listar = "SELECT *, (valor_venda - valor_pago) * quantidade AS lucro_total FROM vinhos" . $condicao . " ORDER BY id DESC";
                    // **************************************************************************
                    $resultado = $conn->query($sql_listar);

                    if ($resultado->num_rows > 0) {
                        while($linha = $resultado->fetch_assoc()) {
                            // Formata as datas para o padrão brasileiro
                            $data_compra_br = date('d/m/Y', strtotime($linha["data_compra"]));
                            $vencimento_br = date('d/m/Y', strtotime($linha["vencimento"]));
                            
                            // ============ LÓGICA DE ALERTA DE ESTOQUE E VENCIMENTO ============
                            $classe_alerta = "";
                            $hoje_timestamp = time(); // Data e hora atual
                            
                            // 1. Alerta de Vencimento (Checa se tem data e se está vencido ou próximo)
                            if ($linha["vencimento"] != '0000-00-00' && !empty($linha["vencimento"])) {
                                $vencimento_timestamp = strtotime($linha["vencimento"]);
                                // Calcula a diferença em dias
                                $dias_para_vencer = ($vencimento_timestamp - $hoje_timestamp) / (60 * 60 * 24);

                                if ($dias_para_vencer < 0) {
                                    // Vencido (Vermelho)
                                    $classe_alerta = "table-danger";
                                } elseif ($dias_para_vencer <= 30) {
                                    // Vencimento Próximo (30 dias - Amarelo)
                                    $classe_alerta = "table-warning";
                                }
                            }

                            // 2. Alerta de Estoque Baixo (Se a quantidade for menor ou igual ao estoque mínimo)
                            if ($linha["quantidade"] <= $linha["estoque_minimo"]) {
                                // Estoque baixo tem prioridade, então sempre define como vermelho (table-danger)
                                $classe_alerta = "table-danger"; 
                            }
                            // ============ FIM DA LÓGICA DE ALERTAS ============
                            
                            // Aplica a classe de alerta na linha da tabela
                            echo "<tr class='{$classe_alerta}'>
                                    <td>" . $linha["id"] . "</td>
                                    <td>" . $linha["nome"] . "</td>
                                    <td>" . $linha["marca"] . "</td>
                                    <td>" . $linha["tipo"] . "</td>
                                    <td>" . $linha["quantidade"] . "</td>
                                    <td>R$ " . number_format($linha["valor_pago"], 2, ',', '.') . "</td>
                                    <td>R$ " . number_format($linha["valor_venda"], 2, ',', '.') . "</td>
                                    <td>" . $data_compra_br . "</td>
                                    <td>" . ($vencimento_br == '01/01/1970' ? 'N/A' : $vencimento_br) . "</td>
                                    <td>R$ " . number_format($linha["lucro_total"], 2, ',', '.') . "</td>
                                    <td>
                                        <!-- Botão de Edição AGORA FUNCIONAL -->
                                        <a href='editar.php?id=" . $linha["id"] . "' class='btn btn-info btn-sm mb-1 w-100'>Editar</a> 
                                        
                                        <a href='index.php?acao=excluir&id=" . $linha["id"] . "' 
                                           class='btn btn-danger btn-sm w-100' 
                                           onclick=\"return confirm('Tem certeza que deseja excluir o item ID " . $linha["id"] . "?');\">
                                            Excluir
                                        </a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11' class='text-center'>Nenhum produto cadastrado ainda.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
</body>
</html>