<?php
// Inclui o arquivo de conexão e inicia a sessão para o carrinho
include 'conexao.php';
session_start();

// Função para buscar configurações
function getConfig($conn, $nome) {
    $sql = "SELECT valor_config FROM configuracoes WHERE nome_config = '$nome'";
    $resultado = $conn->query($sql);
    if ($resultado && $resultado->num_rows > 0) {
        return floatval($resultado->fetch_assoc()['valor_config']);
    }
    return 0;
}

// --- Lógica de Inicialização do Carrinho e Descontos ---
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
if (!isset($_SESSION['descontos'])) {
    $_SESSION['descontos'] = [
        'carrinho' => ['tipo' => null, 'valor' => 0],
        'produtos' => []
    ];
}

// Carrega taxas do banco de dados
$taxa_debito = getConfig($conn, 'taxa_debito');
$taxa_credito = getConfig($conn, 'taxa_credito');

// --- Lógica de Ordenação e Busca ---
$ordem_coluna = 'id';
$ordem_tipo = 'DESC';
$termo_busca = "";
$condicao_busca = "";

if (isset($_GET['ordenar_por'])) {
    switch ($_GET['ordenar_por']) {
        case 'nome_asc': $ordem_coluna = 'nome'; $ordem_tipo = 'ASC'; break;
        case 'nome_desc': $ordem_coluna = 'nome'; $ordem_tipo = 'DESC'; break;
        default: $ordem_coluna = 'id'; $ordem_tipo = 'DESC'; break;
    }
}

if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $termo_busca = $conn->real_escape_string($_GET['busca']);
    $condicao_busca = " WHERE nome LIKE '%$termo_busca%' OR marca LIKE '%$termo_busca%' OR tipo LIKE '%$termo_busca%' ";
}

// Query SQL para listar
$sql_produtos = "
    SELECT id, nome, marca, valor_venda, quantidade
    FROM vinhos
    " . $condicao_busca . "
    ORDER BY " . $ordem_coluna . " " . $ordem_tipo;

$resultado_produtos = $conn->query($sql_produtos);

// Cálculo do carrinho com descontos
$subtotal = 0.00;
$desconto_total = 0.00;
$taxa = 0.00;
$total = 0.00;
$itens_carrinho = [];

if (!empty($_SESSION['carrinho'])) {
    $ids = implode(',', array_keys($_SESSION['carrinho']));
    $sql_carrinho = "SELECT id, nome, valor_venda FROM vinhos WHERE id IN ($ids)";
    $resultado_carrinho = $conn->query($sql_carrinho);

    if ($resultado_carrinho) {
        while ($item = $resultado_carrinho->fetch_assoc()) {
            $qtd = $_SESSION['carrinho'][$item['id']];
            $preco_original = $item['valor_venda'];
            $preco_com_desconto = $preco_original;
            $desconto_item = 0;
            
            // Aplica desconto específico do produto se existir
            if (isset($_SESSION['descontos']['produtos'][$item['id']])) {
                $desconto_prod = $_SESSION['descontos']['produtos'][$item['id']];
                if ($desconto_prod['tipo'] == 'percentual') {
                    $desconto_item = ($preco_original * $desconto_prod['valor']) / 100;
                } else { // valor_fixo
                    $desconto_item = $desconto_prod['valor'];
                }
                $preco_com_desconto = $preco_original - $desconto_item;
            }
            
            $total_item = $preco_com_desconto * $qtd;
            $subtotal += $preco_original * $qtd;
            $desconto_total += $desconto_item * $qtd;
            
            $item['quantidade'] = $qtd;
            $item['preco_original'] = $preco_original;
            $item['preco_com_desconto'] = $preco_com_desconto;
            $item['desconto_item'] = $desconto_item;
            $item['total_item'] = $total_item;
            $itens_carrinho[] = $item;
        }
    }
    
    // Aplica desconto no carrinho se existir
    if ($_SESSION['descontos']['carrinho']['tipo']) {
        $desconto_carrinho = $_SESSION['descontos']['carrinho'];
        $subtotal_com_desconto_produtos = $subtotal - $desconto_total;
        
        if ($desconto_carrinho['tipo'] == 'percentual') {
            $desconto_carrinho_valor = ($subtotal_com_desconto_produtos * $desconto_carrinho['valor']) / 100;
        } else { // valor_fixo
            $desconto_carrinho_valor = $desconto_carrinho['valor'];
        }
        
        $desconto_total += $desconto_carrinho_valor;
    }
    
    $total = $subtotal - $desconto_total + $taxa;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Tela de Vendas - Gera Bebidas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .venda-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        @media (max-width: 992px) {
            .venda-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .btn-remover-carrinho {
            width: 40px !important;
            height: 40px !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-weight: bold !important;
            font-size: 16px !important;
            padding: 0 !important;
            margin-left: 10px !important;
            cursor: pointer !important;
            border: 2px solid #dc3545 !important;
            transition: all 0.2s ease !important;
        }
        
        .btn-remover-carrinho:hover {
            background-color: #dc3545 !important;
            color: white !important;
            transform: scale(1.05);
        }
        
        .item-carrinho {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 12px 8px !important;
        }
        
        .info-produto {
            flex: 1;
        }
        
        .acoes-item {
            display: flex !important;
            align-items: center !important;
            gap: 10px;
        }
        
        .preco-item {
            font-weight: bold;
            min-width: 80px;
            text-align: right;
        }
        
        .btn-voltar-estoque {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .preco-original {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.8em;
        }
        
        .preco-com-desconto {
            color: #dc3545;
            font-weight: bold;
        }
        
        .badge-desconto {
            background-color: #28a745;
            color: white;
            font-size: 0.7em;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
        }
        
        .btn-desconto {
            padding: 2px 6px !important;
            font-size: 0.8em !important;
        }
        
        .taxa-info {
            font-size: 0.8em;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">

<!-- BOTÃO VOLTAR AO ESTOQUE -->
<a href="index.php" class="btn btn-secondary btn-voltar-estoque">
    ← Voltar ao Estoque
</a>

<div class="container-fluid pt-5">
    <h1 class="text-center mb-5">🛒 Tela de Vendas</h1>
    
    <div class="venda-grid">
        
        <div class="card p-4 shadow-sm">
            <h4 class="mb-3">Selecione os Produtos</h4>
            
            <form method="GET" action="vendas.php" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-7 mb-2">
                        <input type="text" name="busca" class="form-control" placeholder="Pesquisar por Nome, Marca ou Tipo..." value="<?php echo htmlspecialchars($termo_busca); ?>">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-select" name="ordenar_por" onchange="this.form.submit()">
                            <option value="recente" <?php echo ($ordem_coluna == 'id' && $ordem_tipo == 'DESC') ? 'selected' : ''; ?>>Mais Recente</option>
                            <option value="nome_asc" <?php echo ($ordem_coluna == 'nome' && $ordem_tipo == 'ASC') ? 'selected' : ''; ?>>Nome (A-Z)</option>
                            <option value="nome_desc" <?php echo ($ordem_coluna == 'nome' && $ordem_tipo == 'DESC') ? 'selected' : ''; ?>>Nome (Z-A)</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-primary w-100">Buscar</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Marca</th>
                            <th>Preço Venda</th>
                            <th>Qtd. Atual</th>
                            <th style="width: 120px;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado_produtos && $resultado_produtos->num_rows > 0): ?>
                            <?php while ($linha = $resultado_produtos->fetch_assoc()): 
                                if ($linha['quantidade'] <= 0) continue;
                                $id_produto = $linha['id'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($linha['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($linha['marca']); ?></td>
                                    <td>R$ <?php echo number_format($linha['valor_venda'], 2, ',', '.'); ?></td>
                                    <td><?php echo $linha['quantidade']; ?></td>
                                    <td>
                                        <button class="btn btn-success btn-sm add-carrinho"
                                                data-id="<?php echo $id_produto; ?>"
                                                data-estoque="<?php echo $linha['quantidade']; ?>">
                                            +
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Nenhum produto em estoque ou na busca.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card p-4 shadow-lg h-100">
            <h4 class="mb-4">Finalizar Venda</h4>
            
            <!-- BOTÕES DE DESCONTO -->
            <?php if (!empty($itens_carrinho)): ?>
            <div class="mb-3">
                <button type="button" class="btn btn-outline-warning btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalDescontoCarrinho">
                    🎁 Desconto em Todo Carrinho
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removerTodosDescontos()">
                    ❌ Remover Todos Descontos
                </button>
            </div>
            <?php endif; ?>
            
            <div id="carrinho-lista">
                <ul class="list-group mb-3 list-group-flush" id="itens-carrinho-ul">
                    <?php if (empty($itens_carrinho)): ?>
                        <li class="list-group-item text-center text-muted">Carrinho Vazio.</li>
                    <?php else: ?>
                        <?php foreach ($itens_carrinho as $item): ?>
                            <li class="list-group-item item-carrinho">
                                <div class="info-produto">
                                    <small class="text-muted"><?php echo htmlspecialchars($item['nome']); ?></small><br>
                                    <strong><?php echo $item['quantidade']; ?>x</strong> 
                                    <?php if ($item['desconto_item'] > 0): ?>
                                        <span class="preco-original">(R$ <?php echo number_format($item['preco_original'], 2, ',', '.'); ?>)</span>
                                        <span class="preco-com-desconto">R$ <?php echo number_format($item['preco_com_desconto'], 2, ',', '.'); ?></span>
                                        <span class="badge-desconto">-R$ <?php echo number_format($item['desconto_item'], 2, ',', '.'); ?></span>
                                    <?php else: ?>
                                        (R$ <?php echo number_format($item['preco_original'], 2, ',', '.'); ?>)
                                    <?php endif; ?>
                                </div>
                                <div class="acoes-item">
                                    <span class="preco-item">R$ <?php echo number_format($item['total_item'], 2, ',', '.'); ?></span>
                                    <button class="btn btn-outline-warning btn-sm btn-desconto add-desconto"
                                            data-id="<?php echo $item['id']; ?>"
                                            data-nome="<?php echo htmlspecialchars($item['nome']); ?>"
                                            data-preco="<?php echo $item['preco_original']; ?>">
                                        💰
                                    </button>
                                    <button class="btn btn-outline-danger btn-remover-carrinho remover-carrinho" 
                                            data-id="<?php echo $item['id']; ?>" 
                                            title="Remover item">
                                        ×
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <ul class="list-group mb-4">
                <li class="list-group-item d-flex justify-content-between">
                    <span>Subtotal</span>
                    <strong id="subtotal_valor">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></strong>
                </li>
                <?php if ($desconto_total > 0): ?>
                <li class="list-group-item d-flex justify-content-between text-success">
                    <span>Desconto</span>
                    <strong id="desconto_valor">-R$ <?php echo number_format($desconto_total, 2, ',', '.'); ?></strong>
                </li>
                <?php endif; ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span>
                        Taxa 
                        <span class="taxa-info" id="taxa_info">(0.0%)</span>
                    </span>
                    <strong id="taxa_valor">R$ <?php echo number_format($taxa, 2, ',', '.'); ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between bg-light fs-5">
                    <span>**TOTAL**</span>
                    <strong id="total_valor" class="text-success">R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
                </li>
            </ul>

            <form id="form-finalizar-venda" action="finalizar_venda.php" method="POST">
                <div class="mb-3">
                    <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                    <select class="form-select" id="forma_pagamento" name="forma_pagamento" onchange="calcularTaxa()">
                        <option value="PIX">PIX</option>
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="Débito">Débito</option>
                        <option value="Cartão de Crédito">Cartão de Crédito</option>
                    </select>
                    <div class="taxa-info mt-1">
                        <small>
                            Taxas: Débito (<?php echo $taxa_debito; ?>%) | Crédito (<?php echo $taxa_credito; ?>%)
                        </small>
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-lg w-100" <?php echo ($total == 0) ? 'disabled' : ''; ?>>
                    Finalizar Venda
                </button>
                <button type="button" class="btn btn-outline-secondary w-100 mt-2" onclick="limparCarrinho()">Limpar Carrinho</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Desconto Carrinho -->
<div class="modal fade" id="modalDescontoCarrinho" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🎁 Desconto em Todo Carrinho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tipo de Desconto</label>
                    <select class="form-select" id="tipo_desconto_carrinho">
                        <option value="percentual">Percentual (%)</option>
                        <option value="valor_fixo">Valor Fixo (R$)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" id="label_valor_desconto_carrinho">Valor do Desconto (%)</label>
                    <input type="number" step="0.01" class="form-control" id="valor_desconto_carrinho" placeholder="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="aplicarDescontoCarrinho()">Aplicar Desconto</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Desconto Produto -->
<div class="modal fade" id="modalDescontoProduto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">💰 Desconto no Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="produto_id_desconto">
                <p id="nome_produto_desconto" class="fw-bold"></p>
                <p>Preço: R$ <span id="preco_produto_desconto"></span></p>
                
                <div class="mb-3">
                    <label class="form-label">Tipo de Desconto</label>
                    <select class="form-select" id="tipo_desconto_produto">
                        <option value="percentual">Percentual (%)</option>
                        <option value="valor_fixo">Valor Fixo (R$)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" id="label_valor_desconto_produto">Valor do Desconto (%)</label>
                    <input type="number" step="0.01" class="form-control" id="valor_desconto_produto" placeholder="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="aplicarDescontoProduto()">Aplicar Desconto</button>
            </div>
        </div>
    </div>
</div>

<?php $conn->close(); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Taxas configuradas (valores do PHP)
const TAXA_DEBITO = <?php echo $taxa_debito; ?>;
const TAXA_CREDITO = <?php echo $taxa_credito; ?>;

// ===============================================
// JAVASCRIPT/AJAX PARA ATUALIZAÇÃO IMEDIATA
// ===============================================

document.addEventListener('DOMContentLoaded', () => {
    // Event listeners para adicionar ao carrinho
    document.querySelectorAll('.add-carrinho').forEach(button => {
        button.addEventListener('click', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            const estoque = parseInt(e.currentTarget.getAttribute('data-estoque'));
            processarAcaoCarrinho('adicionar', id, 1, estoque);
        });
    });

    // Event listeners para desconto em produtos (usando event delegation)
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('add-desconto')) {
            const id = e.target.getAttribute('data-id');
            const nome = e.target.getAttribute('data-nome');
            const preco = e.target.getAttribute('data-preco');
            
            document.getElementById('produto_id_desconto').value = id;
            document.getElementById('nome_produto_desconto').textContent = nome;
            document.getElementById('preco_produto_desconto').textContent = parseFloat(preco).toFixed(2);
            
            // Reset modal
            document.getElementById('valor_desconto_produto').value = '';
            document.getElementById('tipo_desconto_produto').value = 'percentual';
            document.getElementById('label_valor_desconto_produto').textContent = 'Valor do Desconto (%)';
            
            const modal = new bootstrap.Modal(document.getElementById('modalDescontoProduto'));
            modal.show();
        }
    });

    // Event listener para remover itens
    document.getElementById('itens-carrinho-ul').addEventListener('click', (e) => {
        if (e.target.classList.contains('remover-carrinho')) {
            const id = e.target.getAttribute('data-id');
            processarAcaoCarrinho('remover', id);
        }
    });

    // Event listeners para mudança de tipo de desconto
    document.getElementById('tipo_desconto_carrinho').addEventListener('change', function() {
        const label = document.getElementById('label_valor_desconto_carrinho');
        label.textContent = this.value === 'percentual' ? 'Valor do Desconto (%)' : 'Valor do Desconto (R$)';
    });

    document.getElementById('tipo_desconto_produto').addEventListener('change', function() {
        const label = document.getElementById('label_valor_desconto_produto');
        label.textContent = this.value === 'percentual' ? 'Valor do Desconto (%)' : 'Valor do Desconto (R$)';
    });
});

// Função para calcular taxa baseada na forma de pagamento
function calcularTaxa() {
    const formaPagamento = document.getElementById('forma_pagamento').value;
    const subtotal = parseFloat(document.getElementById('subtotal_valor').textContent.replace('R$ ', '').replace('.', '').replace(',', '.'));
    const descontoElement = document.getElementById('desconto_valor');
    const desconto = descontoElement ? parseFloat(descontoElement.textContent.replace('-R$ ', '').replace('.', '').replace(',', '.')) : 0;
    
    const subtotalComDesconto = subtotal - desconto;
    let taxa = 0;
    let percentualTaxa = 0;

    switch(formaPagamento) {
        case 'Débito':
            percentualTaxa = TAXA_DEBITO;
            taxa = (subtotalComDesconto * TAXA_DEBITO) / 100;
            break;
        case 'Cartão de Crédito':
            percentualTaxa = TAXA_CREDITO;
            taxa = (subtotalComDesconto * TAXA_CREDITO) / 100;
            break;
        case 'PIX':
        case 'Dinheiro':
        default:
            percentualTaxa = 0;
            taxa = 0;
            break;
    }

    // Atualiza a exibição da taxa
    document.getElementById('taxa_info').textContent = `(${percentualTaxa}%)`;
    document.getElementById('taxa_valor').textContent = `R$ ${taxa.toFixed(2).replace('.', ',')}`;
    
    // Recalcula o total
    const total = subtotalComDesconto + taxa;
    document.getElementById('total_valor').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

function aplicarDescontoCarrinho() {
    const tipo = document.getElementById('tipo_desconto_carrinho').value;
    const valor = parseFloat(document.getElementById('valor_desconto_carrinho').value);
    
    if (isNaN(valor) || valor <= 0) {
        alert('Por favor, insira um valor válido para o desconto.');
        return;
    }
    
    if (tipo === 'percentual' && valor > 100) {
        alert('O desconto percentual não pode ser maior que 100%.');
        return;
    }
    
    processarAcaoCarrinho('desconto_carrinho', 0, valor, 0, tipo);
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalDescontoCarrinho'));
    modal.hide();
}

function aplicarDescontoProduto() {
    const produtoId = document.getElementById('produto_id_desconto').value;
    const tipo = document.getElementById('tipo_desconto_produto').value;
    const valor = parseFloat(document.getElementById('valor_desconto_produto').value);
    
    if (isNaN(valor) || valor <= 0) {
        alert('Por favor, insira um valor válido para o desconto.');
        return;
    }
    
    if (tipo === 'percentual' && valor > 100) {
        alert('O desconto percentual não pode ser maior que 100%.');
        return;
    }
    
    processarAcaoCarrinho('desconto_produto', produtoId, valor, 0, tipo);
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalDescontoProduto'));
    modal.hide();
}

function removerTodosDescontos() {
    if (confirm('Tem certeza que deseja remover todos os descontos?')) {
        processarAcaoCarrinho('remover_descontos', 0);
    }
}

function limparCarrinho() {
    if (confirm("Tem certeza que deseja limpar o carrinho?")) {
        processarAcaoCarrinho('limpar', 0);
    }
}

/**
 * Função principal para enviar a requisição AJAX
 */
function processarAcaoCarrinho(acao, id, quantidade = 1, estoque = 0, tipo_desconto = null) {
    
    if (acao === 'adicionar' && quantidade > estoque) {
        alert('Erro: Quantidade solicitada excede o estoque disponível (' + estoque + ').');
        return;
    }
    
    // Mostra estado de carregamento
    document.getElementById('total_valor').textContent = '...Calculando...';

    // Prepara os dados
    const dados = {
        acao: acao,
        produto_id: id,
        quantidade: quantidade
    };
    
    if (tipo_desconto) {
        dados.tipo_desconto = tipo_desconto;
    }

    // Envia requisição AJAX
    fetch('processar_carrinho.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(dados)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na rede: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'sucesso') {
            // Atualiza os totais
            atualizarTotais(data);
            
            // Atualiza a lista de itens
            document.getElementById('itens-carrinho-ul').innerHTML = data.itens_html;
            
            // Recalcula a taxa após atualizar o carrinho
            calcularTaxa();
            
        } else {
            alert('Erro: ' + data.mensagem);
        }
    })
    .catch(error => {
        console.error('Erro na comunicação AJAX:', error);
        alert('Erro de rede. Verifique sua conexão e tente novamente.');
        document.getElementById('total_valor').textContent = 'R$ 0,00';
    });
}

function atualizarTotais(data) {
    document.getElementById('subtotal_valor').textContent = data.subtotal_formatado;
    document.getElementById('taxa_valor').textContent = data.taxa_formatado;
    document.getElementById('total_valor').textContent = data.total_formatado;

    // Atualiza ou cria linha de desconto
    let descontoElement = document.getElementById('desconto_valor');
    if (data.desconto_total > 0) {
        if (!descontoElement) {
            // Cria a linha de desconto se não existir
            const subtotalLi = document.querySelector('.list-group-item:first-child');
            const descontoLi = document.createElement('li');
            descontoLi.className = 'list-group-item d-flex justify-content-between text-success';
            descontoLi.innerHTML = `<span>Desconto</span><strong id="desconto_valor">${data.desconto_formatado}</strong>`;
            subtotalLi.after(descontoLi);
        } else {
            descontoElement.textContent = data.desconto_formatado;
        }
    } else if (descontoElement) {
        // Remove a linha de desconto se não houver desconto
        descontoElement.closest('li').remove();
    }

    // Ativa/desativa botão de finalizar
    const btnFinalizar = document.querySelector('#form-finalizar-venda button[type="submit"]');
    if (data.total > 0) {
        btnFinalizar.removeAttribute('disabled');
    } else {
        btnFinalizar.setAttribute('disabled', 'disabled');
    }
}
</script>
</body>
</html>