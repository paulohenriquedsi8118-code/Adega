<?php
include 'conexao.php';
session_start();

// Consulta para obter todas as vendas com informações de produtos
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
    ORDER BY v.data_venda DESC
";

$resultado = $conn->query($sql);

// Calcula totais gerais
$sql_totais = "
    SELECT 
        SUM(quantidade_vendida * valor_venda_unitario) as total_vendido,
        SUM(quantidade_vendida * desconto_aplicado) as total_descontos,
        SUM(quantidade_vendida * valor_original) as total_original
    FROM vendas
";

$resultado_totais = $conn->query($sql_totais);
$totais = $resultado_totais->fetch_assoc();

$total_vendido = $totais['total_vendido'] ?? 0;
$total_descontos = $totais['total_descontos'] ?? 0;
$total_original = $totais['total_original'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas - Gera Bebidas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-total {
            border-left: 4px solid #0d6efd;
        }
        .card-desconto {
            border-left: 4px solid #dc3545;
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
    </style>
</head>
<body class="bg-light">
<div class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📊 Relatório de Vendas</h1>
        <a href="index.php" class="btn btn-secondary">← Voltar ao Estoque</a>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-total">
                <div class="card-body">
                    <h5 class="card-title">Total Vendido</h5>
                    <h3 class="card-text text-primary">R$ <?php echo number_format($total_vendido, 2, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-desconto">
                <div class="card-body">
                    <h5 class="card-title">Total em Descontos</h5>
                    <h3 class="card-text text-danger">-R$ <?php echo number_format($total_descontos, 2, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-lucro">
                <div class="card-body">
                    <h5 class="card-title">Valor Original</h5>
                    <h3 class="card-text text-success">R$ <?php echo number_format($total_original, 2, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Vendas Detalhadas -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Vendas Detalhadas</h5>
        </div>
        <div class="card-body">
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
                        <?php if ($resultado->num_rows > 0): ?>
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
                                                <?php if ($venda['tipo_desconto'] == 'percentual'): ?>
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
                                            <small class="text-muted">
                                                <s>R$ <?php echo number_format($venda['total_original'], 2, ',', '.'); ?></s>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $venda['forma_pagamento']; ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">Nenhuma venda registrada ainda.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
</body>
</html>