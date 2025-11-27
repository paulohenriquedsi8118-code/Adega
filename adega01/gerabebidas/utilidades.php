<?php
// Arquivo de funções de utilidade

/**
 * Busca uma configuração específica do banco de dados.
 * @param mysqli $conn A conexão ativa com o banco de dados.
 * @param string $nome O nome da configuração (ex: 'taxa_debito').
 * @return float O valor da configuração (ou 0 se não for encontrado).
 */
function getConfig(mysqli $conn, $nome) {
    // Escapa o nome para segurança, embora seja uma tabela de configuração interna
    $nome_escapado = $conn->real_escape_string($nome);
    $sql = "SELECT valor_config FROM configuracoes WHERE nome_config = '$nome_escapado'";
    $resultado = $conn->query($sql);
    
    if ($resultado && $resultado->num_rows > 0) {
        // Retorna o valor como float
        return (float) $resultado->fetch_assoc()['valor_config'];
    }
    return 0.0; // Retorna 0.0 se não encontrar
}

/**
 * Calcula o valor da taxa a ser descontado de um valor bruto.
 * @param float $valor_bruto O valor total da transação.
 * @param float $taxa_percentual A taxa em porcentagem (ex: 2.00 para 2%).
 * @return float O valor da taxa (desconto).
 */
function calcularValorTaxa($valor_bruto, $taxa_percentual) {
    if ($taxa_percentual <= 0) {
        return 0.0;
    }
    // (Valor * Taxa) / 100
    return ($valor_bruto * $taxa_percentual) / 100.0;
}
?>