<?php
include 'conexao.php';

// Verifica se os dados do carrinho foram enviados
if (isset($_POST['venda_submit']) && isset($_POST['venda_data']) && isset($_POST['forma_pagamento'])) {
    
    $forma_pagamento = $conn->real_escape_string($_POST['forma_pagamento']);
    $venda_data_json = $_POST['venda_data'];
    $itens_vendidos = json_decode($venda_data_json, true); // Decodifica o JSON para um array PHP

    if (empty($itens_vendidos)) {
        $mensagem = "Erro: O carrinho de vendas está vazio.";
        echo "<script>alert('" . $mensagem . "'); window.location.href='vendas.php';</script>";
        exit;
    }

    $sucesso = true;
    $mensagem_detalhada = "Venda finalizada com sucesso! Itens:";

    // Loop através de cada item no carrinho
    foreach ($itens_vendidos as $item) {
        $id_produto = (int)$item['id'];
        $qtd_vendida = (int)$item['qtd'];

        // 1. Busca o estoque atual e o preço de custo para registrar o lucro (opcional, mas bom)
        $sql_estoque = "SELECT quantidade, valor_pago, valor_venda FROM vinhos WHERE id = '$id_produto'";
        $resultado = $conn->query($sql_estoque);

        if ($resultado->num_rows > 0) {
            $linha = $resultado->fetch_assoc();
            $estoque_atual = $linha['quantidade'];

            // 2. Verifica se há estoque suficiente
            if ($estoque_atual >= $qtd_vendida) {
                
                // 3. Calcula nova quantidade
                $nova_quantidade = $estoque_atual - $qtd_vendida;

                // 4. ATUALIZA A TABELA VINHOS (baixa no estoque)
                $sql_update = "UPDATE vinhos SET quantidade = '$nova_quantidade' WHERE id = '$id_produto'";
                
                // 5. INSERE O REGISTRO NA TABELA VENDAS (histórico de transação)
                $sql_insert_venda = "INSERT INTO vendas (produto_id, quantidade_vendida, forma_pagamento) 
                                     VALUES ('$id_produto', '$qtd_vendida', '$forma_pagamento')";

                if ($conn->query($sql_update) === FALSE || $conn->query($sql_insert_venda) === FALSE) {
                    $sucesso = false;
                    $mensagem_detalhada .= "\nErro ao processar item ID " . $id_produto;
                    // Se houver erro, podemos interromper, mas por simplicidade, apenas registramos o erro
                } else {
                    $mensagem_detalhada .= "\n- ID $id_produto (" . $item['nome'] . "): $qtd_vendida unidade(s)";
                }

            } else {
                $sucesso = false;
                $mensagem_detalhada = "ERRO: Falha na venda! Estoque insuficiente para o item " . $item['nome'] . ".";
                break; // Interrompe o loop se não houver estoque
            }

        } else {
            $sucesso = false;
            $mensagem_detalhada = "ERRO: Produto ID $id_produto não encontrado no estoque.";
            break;
        }
    }
    
    // 6. Feedback final e redirecionamento
    $mensagem_alerta = $sucesso ? $mensagem_detalhada . "\n\nPagamento: " . $forma_pagamento : $mensagem_detalhada;
    echo "<script>alert('" . $mensagem_alerta . "'); window.location.href='vendas.php';</script>";
    
} else {
    // Acesso direto ou faltando dados
    header('Location: vendas.php');
}

$conn->close();
?>