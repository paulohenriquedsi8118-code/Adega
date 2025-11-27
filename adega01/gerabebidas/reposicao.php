<?php
include 'conexao.php';

// ***********************************************
// Lógica para Listar Produtos Abaixo do Mínimo
// ***********************************************

$sql_reposicao = "
    SELECT 
        id, 
        nome, 
        marca, 
        tipo, 
        quantidade, 
        estoque_minimo,
        (estoque_minimo - quantidade) AS quantidade_sugerida
    FROM vinhos
    WHERE quantidade < estoque_minimo
    ORDER BY quantidade_sugerida DESC;
";

$resultado = $conn->query($sql_reposicao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Reposição de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-center mb-4">🛒 Lista de Reposição (Estoque Mínimo)</h1>
    
    <div class="mb-4">
        <a href="index.php" class="btn btn-secondary me-2">Voltar ao Estoque</a>
    </div>

    <div class="alert alert-info" role="alert">
      Esta lista exibe todos os produtos onde a **quantidade em estoque é menor** que o **Estoque Mínimo** definido.
    </div>

    <div class="card p-4 shadow-sm">
        <h4 class="mb-3">Produtos para Comprar</h4>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Marca</th>
                        <th>Tipo</th>
                        <th>Qtd Atual</th>
                        <th>Estoque Mínimo</th>
                        <th class="text-primary">Qtd Sugerida para Reposição</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultado->num_rows > 0) {
                        while($linha = $resultado->fetch_assoc()) {
                            // Define a classe da linha para chamar a atenção
                            $classe_linha = 'table-warning';
                            
                            echo "<tr class='$classe_linha'>";
                            echo "<td>" . $linha["id"] . "</td>";
                            echo "<td>" . $linha["nome"] . "</td>";
                            echo "<td>" . $linha["marca"] . "</td>";
                            echo "<td>" . $linha["tipo"] . "</td>";
                            echo "<td class='text-danger'>**" . $linha["quantidade"] . "**</td>";
                            echo "<td>" . $linha["estoque_minimo"] . "</td>";
                            echo "<td class='text-primary fw-bold'>" . $linha["quantidade_sugerida"] . "</td>";
                            echo "<td>
                                    <a href='editar.php?id=" . $linha["id"] . "' class='btn btn-info btn-sm'>Editar</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>🎉 Nenhum produto abaixo do estoque mínimo. Seu estoque está cheio!</td></tr>";
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