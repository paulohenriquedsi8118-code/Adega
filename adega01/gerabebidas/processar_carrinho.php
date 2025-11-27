<?php
session_start();
include 'conexao.php';

// Inicializa carrinho e descontos se não existirem
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
if (!isset($_SESSION['descontos'])) {
    $_SESSION['descontos'] = [
        'carrinho' => ['tipo' => null, 'valor' => 0],
        'produtos' => []
    ];
}

$response = ['status' => 'erro', 'mensagem' => 'Ação não reconhecida.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'] ?? '';
    $produto_id = intval($_POST['produto_id'] ?? 0);
    $quantidade = intval($_POST['quantidade'] ?? 1);
    $tipo_desconto = $_POST['tipo_desconto'] ?? null;

    try {
        switch ($acao) {
            case 'adicionar':
                // Adiciona produto ao carrinho
                if (!isset($_SESSION['carrinho'][$produto_id])) {
                    $_SESSION['carrinho'][$produto_id] = 0;
                }
                $_SESSION['carrinho'][$produto_id] += $quantidade;
                $response = ['status' => 'sucesso', 'mensagem' => 'Produto adicionado ao carrinho.'];
                break;

            case 'remover':
                // Remove produto do carrinho
                if (isset($_SESSION['carrinho'][$produto_id])) {
                    unset($_SESSION['carrinho'][$produto_id]);
                    // Remove também desconto específico do produto se existir
                    if (isset($_SESSION['descontos']['produtos'][$produto_id])) {
                        unset($_SESSION['descontos']['produtos'][$produto_id]);
                    }
                }
                $response = ['status' => 'sucesso', 'mensagem' => 'Produto removido do carrinho.'];
                break;

            case 'limpar':
                // Limpa carrinho e descontos
                $_SESSION['carrinho'] = [];
                $_SESSION['descontos'] = [
                    'carrinho' => ['tipo' => null, 'valor' => 0],
                    'produtos' => []
                ];
                $response = ['status' => 'sucesso', 'mensagem' => 'Carrinho limpo.'];
                break;

            case 'desconto_carrinho':
                // Aplica desconto no carrinho inteiro
                $_SESSION['descontos']['carrinho'] = [
                    'tipo' => $tipo_desconto,
                    'valor' => floatval($quantidade)
                ];
                $response = ['status' => 'sucesso', 'mensagem' => 'Desconto aplicado ao carrinho.'];
                break;

            case 'desconto_produto':
                // Aplica desconto em produto específico (só se estiver no carrinho)
                if (isset($_SESSION['carrinho'][$produto_id])) {
                    $_SESSION['descontos']['produtos'][$produto_id] = [
                        'tipo' => $tipo_desconto,
                        'valor' => floatval($quantidade)
                    ];
                    $response = ['status' => 'sucesso', 'mensagem' => 'Desconto aplicado ao produto.'];
                } else {
                    $response = ['status' => 'erro', 'mensagem' => 'Produto não está no carrinho.'];
                }
                break;

            case 'remover_descontos':
                // Remove todos os descontos
                $_SESSION['descontos'] = [
                    'carrinho' => ['tipo' => null, 'valor' => 0],
                    'produtos' => []
                ];
                $response = ['status' => 'sucesso', 'mensagem' => 'Todos os descontos removidos.'];
                break;

            default:
                $response = ['status' => 'erro', 'mensagem' => 'Ação não reconhecida: ' . $acao];
                break;
        }

        // Calcula os totais atualizados
        $totais = calcularTotaisCarrinho($conn);
        $response = array_merge($response, $totais);

    } catch (Exception $e) {
        $response = ['status' => 'erro', 'mensagem' => 'Erro: ' . $e->getMessage()];
    }
}

// Retorna resposta em JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;

/**
 * Função para calcular totais do carrinho
 */
function calcularTotaisCarrinho($conn) {
    $subtotal = 0.00;
    $desconto_total = 0.00;
    $taxa = 0.00;
    $total = 0.00;
    $itens_html = '';
    
    if (empty($_SESSION['carrinho'])) {
        $itens_html = '<li class="list-group-item text-center text-muted">Carrinho Vazio.</li>';
    } else {
        $ids = implode(',', array_keys($_SESSION['carrinho']));
        $sql = "SELECT id, nome, valor_venda FROM vinhos WHERE id IN ($ids)";
        $result = $conn->query($sql);

        if ($result) {
            while ($item = $result->fetch_assoc()) {
                $qtd = $_SESSION['carrinho'][$item['id']];
                $preco_original = $item['valor_venda'];
                $preco_com_desconto = $preco_original;
                $desconto_item = 0;
                
                // Aplica desconto específico do produto se existir
                if (isset($_SESSION['descontos']['produtos'][$item['id']])) {
                    $desconto_prod = $_SESSION['descontos']['produtos'][$item['id']];
                    if ($desconto_prod['tipo'] == 'percentual') {
                        $desconto_item = ($preco_original * $desconto_prod['valor']) / 100;
                    } else {
                        $desconto_item = $desconto_prod['valor'];
                    }
                    $preco_com_desconto = $preco_original - $desconto_item;
                }
                
                $total_item = $preco_com_desconto * $qtd;
                $subtotal += $preco_original * $qtd;
                $desconto_total += $desconto_item * $qtd;
                
                // HTML do item
                $itens_html .= '
                <li class="list-group-item item-carrinho">
                    <div class="info-produto">
                        <small class="text-muted">' . htmlspecialchars($item['nome']) . '</small><br>
                        <strong>' . $qtd . 'x</strong> ';
                
                if ($desconto_item > 0) {
                    $itens_html .= '<span class="preco-original">(R$ ' . number_format($preco_original, 2, ',', '.') . ')</span>
                                    <span class="preco-com-desconto">R$ ' . number_format($preco_com_desconto, 2, ',', '.') . '</span>
                                    <span class="badge-desconto">-R$ ' . number_format($desconto_item, 2, ',', '.') . '</span>';
                } else {
                    $itens_html .= '(R$ ' . number_format($preco_original, 2, ',', '.') . ')';
                }
                
                $itens_html .= '</div>
                    <div class="acoes-item">
                        <span class="preco-item">R$ ' . number_format($total_item, 2, ',', '.') . '</span>
                        <button class="btn btn-outline-warning btn-sm btn-desconto add-desconto"
                                data-id="' . $item['id'] . '"
                                data-nome="' . htmlspecialchars($item['nome']) . '"
                                data-preco="' . $preco_original . '">
                            💰
                        </button>
                        <button class="btn btn-outline-danger btn-remover-carrinho remover-carrinho" 
                                data-id="' . $item['id'] . '" 
                                title="Remover item">
                            ×
                        </button>
                    </div>
                </li>';
            }
        }
    }
    
    // Aplica desconto no carrinho se existir
    $desconto_carrinho_valor = 0;
    if ($_SESSION['descontos']['carrinho']['tipo']) {
        $desconto_carrinho = $_SESSION['descontos']['carrinho'];
        $subtotal_com_desconto_produtos = $subtotal - $desconto_total;
        
        if ($desconto_carrinho['tipo'] == 'percentual') {
            $desconto_carrinho_valor = ($subtotal_com_desconto_produtos * $desconto_carrinho['valor']) / 100;
        } else {
            $desconto_carrinho_valor = $desconto_carrinho['valor'];
        }
        
        $desconto_total += $desconto_carrinho_valor;
    }
    
    $total = $subtotal - $desconto_total + $taxa;
    
    // Formata valores para exibição
    return [
        'subtotal' => $subtotal,
        'subtotal_formatado' => 'R$ ' . number_format($subtotal, 2, ',', '.'),
        'desconto_total' => $desconto_total,
        'desconto_formatado' => '-R$ ' . number_format($desconto_total, 2, ',', '.'),
        'taxa' => $taxa,
        'taxa_formatado' => 'R$ ' . number_format($taxa, 2, ',', '.'),
        'total' => $total,
        'total_formatado' => 'R$ ' . number_format($total, 2, ',', '.'),
        'itens_html' => $itens_html
    ];
}
?>