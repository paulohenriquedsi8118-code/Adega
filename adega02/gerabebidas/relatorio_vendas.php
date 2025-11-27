<?php
include 'conexao.php';
session_start();

// Define datas padrão (últimos 30 dias)
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

// Consulta para obter todas as vendas com informações de produtos (com filtro de datas)
$sql = "
    SELECT 
        v.*,
        p.nome as produto_nome,
        p.marca as produto_marca,
        (v.quantidade_vendida * v.valor_venda_unitario) as total_venda,
        (v.quantidade_vendida * v.desconto_aplicado) as total_desconto,
        (v.quantidade_vendida * v.valor_original) as total_original
    FROM vendas v
    LEFT JOIN vinhos p ON v.produto_id = p.id
    WHERE DATE(v.data_venda) BETWEEN ? AND ?
    ORDER BY v.data_venda DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $data_inicio, $data_fim);
$stmt->execute();
$resultado = $stmt->get_result();

// Calcula totais gerais (com filtro de datas)
$sql_totais = "
    SELECT 
        SUM(quantidade_vendida * valor_venda_unitario) as total_vendido,
        SUM(quantidade_vendida * desconto_aplicado) as total_descontos,
        SUM(quantidade_vendida * valor_original) as total_original
    FROM vendas
    WHERE DATE(data_venda) BETWEEN ? AND ?
";

$stmt_totais = $conn->prepare($sql_totais);
$stmt_totais->bind_param("ss", $data_inicio, $data_fim);
$stmt_totais->execute();
$resultado_totais = $stmt_totais->get_result();
$totais = $resultado_totais->fetch_assoc();

$total_vendido = $totais['total_vendido'] ?? 0;
$total_descontos = $totais['total_descontos'] ?? 0;
$total_original = $totais['total_original'] ?? 0;

// Calcula lucro líquido
$lucro_liquido = $total_vendido - $total_original;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas - Gera Bebidas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-total {
            border-left: 4px solid #0d6efd;
        }
        .card-desconto {
            border-left: 4px solid #dc3545;
        }
        .card-original {
            border-left: 4px solid #ffc107;
        }
        .card-lucro {
            border-left: 4px solid #198754;
        }
        .badge-desconto {
            background-color: #dc3545;
        }
        .preco-original {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9em;
        }
        .filter-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .periodo-display {
            background-color: #0b023f96;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📊 Relatório de Vendas</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar ao Estoque
        </a>
    </div>

    <!-- Filtro por Data -->
    <div class="card shadow-sm mb-4 filter-card">
        <div class="card-body">
            <h5 class="card-title text-white">
                <i class="bi bi-funnel"></i> Filtrar por Período
            </h5>
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="data_inicio" class="form-label text-white">Data Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                           value="<?php echo $data_inicio; ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="data_fim" class="form-label text-white">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" 
                           value="<?php echo $data_fim; ?>" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-light me-2">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="relatorio_vendas.php" class="btn btn-outline-light">
                        <i class="bi bi-arrow-clockwise"></i> Limpar
                    </a>
                </div>
            </form>
            
            <!-- Display do período selecionado -->
            <div class="periodo-display mt-3">
                <strong>Período selecionado:</strong> 
                <?php echo date('d/m/Y', strtotime($data_inicio)); ?> 
                até 
                <?php echo date('d/m/Y', strtotime($data_fim)); ?>
                
                <?php 
                $dias_periodo = (strtotime($data_fim) - strtotime($data_inicio)) / (60 * 60 * 24) + 1;
                echo "<span class='badge bg-primary ms-2'>$dias_periodo dia(s)</span>";
                ?>
            </div>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card card-total h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Vendido</h5>
                    <h3 class="card-text text-primary">R$ <?php echo number_format($total_vendido, 2, ',', '.'); ?></h3>
                    <small class="text-muted">Receita total no período</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-desconto h-100">
                <div class="card-body">
                    <h5 class="card-title">Total em Descontos</h5>
                    <h3 class="card-text text-danger">-R$ <?php echo number_format($total_descontos, 2, ',', '.'); ?></h3>
                    <small class="text-muted">Descontos aplicados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-original h-100">
                <div class="card-body">
                    <h5 class="card-title">Valor Original</h5>
                    <h3 class="card-text text-warning">R$ <?php echo number_format($total_original, 2, ',', '.'); ?></h3>
                    <small class="text-muted">Valor sem descontos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-lucro h-100">
                <div class="card-body">
                    <h5 class="card-title">Lucro Líquido</h5>
                    <h3 class="card-text text-success">R$ <?php echo number_format($lucro_liquido, 2, ',', '.'); ?></h3>
                    <small class="text-muted">Total Vendido - Valor Original</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Vendas Detalhadas -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Vendas Detalhadas</h5>
            <span class="badge bg-primary">
                <?php echo $resultado->num_rows; ?> venda(s) encontrada(s)
            </span>
        </div>
        <div class="card-body">
            <?php if ($resultado->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Produto</th>
                                <th>Marca</th>
                                <th>Qtd</th>
                                <th>Preço Original</th>
                                <th>Desconto</th>
                                <th>Preço Final</th>
                                <th>Total Item</th>
                                <th>Pagamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($venda = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></td>
                                    <td><?php echo htmlspecialchars($venda['produto_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($venda['produto_marca']); ?></td>
                                    <td><?php echo $venda['quantidade_vendida']; ?></td>
                                    <td>
                                        R$ <?php echo number_format($venda['valor_original'], 2, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <?php if ($venda['desconto_aplicado'] > 0): ?>
                                            <span class="badge badge-desconto text-white">
                                                <?php if (isset($venda['tipo_desconto']) && $venda['tipo_desconto'] == 'percentual'): ?>
                                                    <?php echo number_format(($venda['desconto_aplicado'] / $venda['valor_original']) * 100, 1); ?>%
                                                <?php else: ?>
                                                    -R$ <?php echo number_format($venda['desconto_aplicado'], 2, ',', '.'); ?>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($venda['desconto_aplicado'] > 0): ?>
                                            <span class="text-success fw-bold">
                                                R$ <?php echo number_format($venda['valor_venda_unitario'], 2, ',', '.'); ?>
                                            </span>
                                        <?php else: ?>
                                            R$ <?php echo number_format($venda['valor_venda_unitario'], 2, ',', '.'); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>R$ <?php echo number_format($venda['total_venda'], 2, ',', '.'); ?></strong>
                                        <?php if ($venda['total_desconto'] > 0): ?>
                                            <br>
                                            <small class="text-muted preco-original">
                                                R$ <?php echo number_format($venda['total_original'], 2, ',', '.'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $venda['forma_pagamento']; ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">Nenhuma venda encontrada</h4>
                    <p class="text-muted">Não há vendas registradas no período selecionado.</p>
                    <a href="relatorio_vendas.php" class="btn btn-primary">
                        <i class="bi bi-arrow-clockwise"></i> Ver todos os registros
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Define a data máxima como hoje
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('data_fim').max = today;
        document.getElementById('data_inicio').max = today;
        
        // Validação para data início não ser maior que data fim
        document.getElementById('data_inicio').addEventListener('change', function() {
            const dataFim = document.getElementById('data_fim');
            if (this.value > dataFim.value) {
                dataFim.value = this.value;
            }
        });
        
        document.getElementById('data_fim').addEventListener('change', function() {
            const dataInicio = document.getElementById('data_inicio');
            if (this.value < dataInicio.value) {
                dataInicio.value = this.value;
            }
        });
    });
</script>

<?php 
$stmt->close();
$stmt_totais->close();
$conn->close(); 
?>
</body>
</html>