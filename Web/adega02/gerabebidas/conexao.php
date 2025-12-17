<?php
// Configurações do Banco de Dados Local (XAMPP)
$servidor = "localhost";
$usuario = "root";
$senha = ""; 
$banco = "adega_db";

// Cria a conexão
$conn = new mysqli($servidor, $usuario, $senha, $banco);

// Verifica se deu erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Opcional: Define o charset para evitar problemas com acentos e 'ç'
$conn->set_charset("utf8");

?>
