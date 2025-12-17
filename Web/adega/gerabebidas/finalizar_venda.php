<?php
session_start();
include 'conexao.php';

// Verifica se há itens no carrinho
if (empty($_SESSION['carrinho'])) {
    $_SESSION['mensagem_erro'] = "Carrinho vazio!";
    header("Location: vendas.php");
    exit;
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $forma_pagamento = $conn->real_escape_string($_POST['forma_pagamento']);
    $data_venda = date('Y-m-d H:i:s');
    
    try {
        // Inicia transação
        $conn->begin_transaction();
        $total_venda = 0;
        
        foreach ($_SESSION['carrinho'] as $produto_id => $quantidade_vendida) {
            // Busca dados do produto
            $sql_produto = "SELECT nome, valor_venda, quantidade FROM vinhos WHERE id = ?";
            $stmt_produto = $conn->prepare($sql_produto);
            $stmt_produto->bind_param("i", $produto_id);
            $stmt_produto->execute();
            $result_produto = $stmt_produto->get_result();
            
            if ($result_produto->num_rows > 0) {
                $produto = $result_produto->fetch_assoc();
                $valor_original = $produto['valor_venda'];
                $estoque_atual = $produto['quantidade'];
                $nome_produto = $produto['nome'];
                
                // Verifica estoque
                if ($estoque_atual < $quantidade_vendida) {
                    throw new Exception("Estoque insuficiente para $nome_produto. Estoque: $estoque_atual, Solicitado: $quantidade_vendida");
                }
                
                // Calcula desconto do produto
                $desconto_produto = 0;
                $tipo_desconto_produto = null;
                $valor_com_desconto = $valor_original;
                
                if (isset($_SESSION['descontos']['produtos'][$produto_id])) {
                    $desconto_info = $_SESSION['descontos']['produtos'][$produto_id];
                    $tipo_desconto_produto = $desconto_info['tipo'];
                    
                    if ($desconto_info['tipo'] == 'percentual') {
                        $desconto_produto = ($valor_original * $desconto_info['valor']) / 100;
                    } else {
                        $desconto_produto = $desconto_info['valor'];
                    }
                    $valor_com_desconto = $valor_original - $desconto_produto;
                }
                
                // Insere na tabela vendas com informações de desconto
                $sql_venda = "INSERT INTO vendas (produto_id, quantidade_vendida, valor_venda_unitario, valor_original, desconto_aplicado, tipo_desconto, data_venda, forma_pagamento) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_venda = $conn->prepare($sql_venda);
                $stmt_venda->bind_param("iidddsss", $produto_id, $quantidade_vendida, $valor_com_desconto, $valor_original, $desconto_produto, $tipo_desconto_produto, $data_venda, $forma_pagamento);
                
                if (!$stmt_venda->execute()) {
                    throw new Exception("Erro ao executar venda: " . $stmt_venda->error);
                }
                
                $stmt_venda->close();
                
                // Atualiza estoque
                $sql_estoque = "UPDATE vinhos SET quantidade = quantidade - ? WHERE id = ?";
                $stmt_estoque = $conn->prepare($sql_estoque);
                $stmt_estoque->bind_param("ii", $quantidade_vendida, $produto_id);
                
                if (!$stmt_estoque->execute()) {
                    throw new Exception("Erro ao atualizar estoque: " . $stmt_estoque->error);
                }
                
                $stmt_estoque->close();
                $total_venda += ($valor_com_desconto * $quantidade_vendida);
                
            } else {
                throw new Exception("Produto ID $produto_id não encontrado!");
            }
            
            $stmt_produto->close();
        }
        
        // Confirma transação
        $conn->commit();
        
        // Limpa carrinho e descontos
        $_SESSION['carrinho'] = [];
        $_SESSION['descontos'] = [
            'carrinho' => ['tipo' => null, 'valor' => 0],
            'produtos' => []
        ];
        
        $_SESSION['mensagem_sucesso'] = "Venda finalizada com sucesso! Total: R$ " . number_format($total_venda, 2, ',', '.');
        
        header("Location: index.php");
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['mensagem_erro'] = "Erro ao finalizar venda: " . $e->getMessage();
        header("Location: vendas.php");
        exit;
    }
} else {
    $_SESSION['mensagem_erro'] = "Método inválido!";
    header("Location: vendas.php");
    exit;
}
?>