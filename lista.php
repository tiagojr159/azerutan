<?php
ob_start();
$nivelUsuario = 0;
if (!empty($_SESSION['nivel'])) {
    $nivelUsuario = $_SESSION['nivel'];
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!class_exists('conexao')) {
    require_once 'config/conexao.class.php';
    require_once 'config/crud.class.php';
}
$con = new conexao();
$con->connect();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Paixão de Cristo de Igarassu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #ECEFF1;
            color: #333;
        }

        .navbar {
            background-color: #2E7D32;
        }

        .navbar-brand,
        .nav-link {
            color: #FFFFFF !important;
        }

        .nav-link:hover {
            color: #A5D6A7 !important;
        }

        .btn-primary {
            background-color: #1976D2;
            border-color: #1976D2;
        }

        .btn-primary:hover {
            background-color: #1565C0;
            border-color: #1565C0;
        }

        .btn-success {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }

        .btn-success:hover {
            background-color: #43A047;
            border-color: #43A047;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            border-bottom: none;
            font-weight: bold;
            text-align: center;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .status-pendente {
            color: #D32F2F;
            font-weight: bold;
        }

        .status-ok {
            color: #4CAF50;
            font-weight: bold;
        }

        .table img {
            max-width: 50px;
            height: 50px;
            border-radius: 5px;
        }

        .table-responsive {
            padding: 20px;
        }

        @media (max-width: 576px) {
            .card-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Azerutan</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav me-auto mb-2 mb-md-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Início</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="dropdown01"
                            data-bs-toggle="dropdown">Opções</a>
                        <ul class="dropdown-menu" aria-labelledby="dropdown01">
                            <li><a class="dropdown-item" href="form_colaborador.php?novo=1">Nova Matrícula</a></li>
                            <li><a class="dropdown-item" href="inscricao_renovar.php">Renovar Matrícula</a></li>
                            <li><a class="dropdown-item"
                                    href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC">Secretaria Azerutan</a></li>
                        </ul>
                    </li>
                </ul>
                <form class="d-flex">
                    <input class="form-control me-2" type="search" placeholder="Pesquisar" aria-label="Pesquisar">
                    <button class="btn btn-outline-light" type="submit">Pesquisar</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container mt-5 pt-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header" style="background-color: #A5D6A7;">Nova Matrícula</div>
                    <div class="card-body">
                        <p>Se você nunca fez parte do grupo Azerutan, clique abaixo para criar sua matrícula.</p>
                        <a href="form_colaborador.php?novo=1" class="btn btn-success">Nova Matrícula</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header" style="background-color: #A5D6A7;">Renovação de Matrícula</div>
                    <div class="card-body">
                        <p>Renove sua matrícula se já participou da Paixão de Cristo com o Azerutan.</p>
                        <a href="inscricao_renovar.php" class="btn btn-success">Renovar Matrícula</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header" style="background-color: #A5D6A7;">Secretaria Azerutan</div>
                    <div class="card-body">
                        <p>Entre no grupo do WhatsApp para dúvidas ou suporte.</p>
                        <a href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC">
                            <img src="../projeto/images/icone/whatsapp-icon-logo-BDC0A8063B-seeklogo.com.png"
                                alt="WhatsApp" width="50">
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 text-center">

            <?php




            mostrarAniversariantesHoje2();









            if (!empty($_GET['action']) && $_GET['action'] == 'modular') {
                echo "<a href='lista.php?action=lista' class='btn btn-primary mb-3'>Exibir Lista</a>";
                echo listaColaboradores('Direção', 'Dir', '#A5D6A7', '#E8F5E9', 'SIM');
                echo listaColaboradores('Direção Secundária', 'Assist', '#4CAF50', '#DCEDC8', 'SIM');
                echo listaColaboradores('Produção', 'Prod', '#81C784', '#F1F8E9', 'SIM');
                echo listaColaboradores('Elenco', 'Elen', '#66BB6A', '#C8E6C9', 'SIM');
                echo listaColaboradores('Bailarino(a)s', 'Bailarino1', '#A5D6A7', '#E8F5E9', 'SIM');
                echo listaColaboradores('Bailarino(a)s - Crianças', 'Bailarino2', '#A5D6A7', '#DCEDC8', 'SIM');
                echo listaColaboradores('Músico(a)s', 'Músico', '#81C784', '#F1F8E9', 'SIM');
                echo listaColaboradores('Secundário', 'Secun', '#4CAF50', '#C8E6C9', 'SIM');
                echo listaColaboradores('Figurantes', 'Fig', '#66BB6A', '#E8F5E9', 'SIM');
                echo listaColaboradores('Pendente de Autorização', '', '#B0BEC5', '#ECEFF1', 'PENDENTE');
            } else {
                echo "<a href='lista.php?action=modular' class='btn btn-primary mb-3'>Exibir Modular</a>";
                echo listaDivFuncao('Direção', 'Dir', '#A5D6A7', '#E8F5E9', 'SIM');
                echo listaDivFuncao('Direção Secundária', 'Assist', '#4CAF50', '#DCEDC8', 'SIM');
                echo listaDivFuncao('Produção', 'Prod', '#81C784', '#F1F8E9', 'SIM');
                echo listaDivFuncao('Elenco', 'Elen', '#66BB6A', '#C8E6C9', 'SIM');
                echo listaDivFuncao('Bailarino(a)s', 'Bailarino1', '#A5D6A7', '#E8F5E9', 'SIM');
                echo listaDivFuncao('Bailarino(a)s - Crianças', 'Bailarino2', '#A5D6A7', '#DCEDC8', 'SIM');
                echo listaDivFuncao('Músico(a)s', 'Músico', '#81C784', '#F1F8E9', 'SIM');
                echo listaDivFuncao('Secundário', 'Secun', '#4CAF50', '#C8E6C9', 'SIM');
                echo listaDivFuncao('Figurantes', 'Fig', '#66BB6A', '#E8F5E9', 'SIM');
                echo listaDivFuncao('Pendente de Autorização', '', '#B0BEC5', '#ECEFF1', 'PENDENTE');
            }
            ?>
        </div>
    </main>

    <footer class="container text-center py-3">
        <p class="text-muted">© Companhia 2017-2025</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php

function listaColaboradores($papel, $tipo_papel, $headerColor, $bodyColor, $situacao)
{
    $con = new conexao();
    $con->connect();
    $anodata = date('Y');
    $retorno = "<div class='card mb-4'><div class='card-header' style='background-color: $headerColor;'>$papel</div><div class='card-body' style='background-color: $bodyColor;'><div class='card-grid'>";
    $quant = 0;
    $nivelUsuario = !empty($_SESSION['nivel']) ? $_SESSION['nivel'] : 0;

    // Consulta principal de colaboradores
    $sql = "SELECT c.*, a.*, a.cache as cacheano, 
            (select count(*) from pend_cad where id_colaborador = c.id and pendencia = 1) as pendencia 
            FROM colaborador c, ano_projeto a 
            WHERE c.id = a.id_colaborador 
            AND a.ano = '$anodata'";
    if ($situacao == 'PENDENTE') {
        $sql .= " AND (a.situacao IN ('PENDENTE') or a.situacao IN ('SIM') && papel1 is null)";

    } else {
        $sql .= "  AND a.situacao IN ('$situacao') ";
    }
    if ($tipo_papel != '') {
        $sql .= " AND (papel1 LIKE '$tipo_papel%' OR papel2 LIKE '$tipo_papel%' OR papel3 LIKE '$tipo_papel%')";
    }
    $sql .= " GROUP BY id ORDER BY nome ASC LIMIT 1000";
    $consulta = mysqli_query($con->connect(), $sql);

    // Total de dias com chamadas no ano
    $totalChamadasQuery = mysqli_query($con->connect(), "SELECT COUNT(DISTINCT SUBSTR(data, 1, 10)) as total FROM chamada WHERE data LIKE '%$anodata%'");
    $totalChamadas = mysqli_fetch_array($totalChamadasQuery)['total'];

    while ($campo = mysqli_fetch_array($consulta)) {
        $quant++;
        $retorno .= "<div class='card'><div class='card-body text-center'>";

        // Foto do colaborador
        $consulta3 = mysqli_query($con->connect(), "SELECT * FROM foto_colaborador WHERE tipo = 'P' AND id_colaborador='" . $campo['id'] . "' ORDER BY id DESC LIMIT 1");
        if (mysqli_num_rows($consulta3) > 0) {
            while ($campo3 = mysqli_fetch_array($consulta3)) {
                $retorno .= "<img src='../projeto/upload_pic/" . $campo3['foto'] . "' class='img-fluid rounded mb-2' style='max-width: 300px; height: 200px;'>";
            }
        } else {
            $retorno .= "<img src='../projeto/images/default-avatar.png' class='img-fluid rounded mb-2' style='max-width: 300px; height: 200px;'>";
        }

        // Nome e papel
        $retorno .= "<h6 class='text-uppercase'>" . strtoupper($campo['nome']) . "</h6>";
        $retorno .= "<p class='mb-1'>" . $campo['papel1'] . "</p>";

        // Status de documentação
        if ($situacao == "SIM") {
            $retorno .= $campo['pendencia'] > 0 ? "<div class='status-pendente'>Falta Documentação</div>" : "<div class='status-ok'>Documentação OK</div>";
        } else {
            $retorno .= "<p class='text-warning'>Aguardando Vaga</p>";
        }

        // Contagem de presenças (apenas uma por dia) e faltas
        $presencasQuery = mysqli_query($con->connect(), "SELECT COUNT(DISTINCT SUBSTR(data, 1, 10)) as presencas FROM chamada WHERE id_colaborador = '" . $campo['id'] . "' AND data LIKE '%$anodata%'");
        $presencas = mysqli_fetch_array($presencasQuery)['presencas'];
        $faltas = $totalChamadas - $presencas;
        $retorno .= "<div class='mt-2'><span style='color: blue; font-weight: bold;'>Presenças: $presencas</span><br>";
        $retorno .= "<span style='color: red; font-weight: bold;'>Faltas: $faltas</span></div>";

        // Botões de ação
        if ($nivelUsuario > 0) {
            $retorno .= "<button class='btn btn-success btn-sm mt-2' onclick='autorizarCadastro(" . $campo['id'] . ")'>Autorizar</button> ";
        }
        $retorno .= "<a href='../inscricao/form_foto_documentacao.php?id=" . $campo['id'] . "' class='btn btn-primary btn-sm mt-2'>Atualizar Dados</a>";
        $retorno .= "</div></div>";
    }
    $retorno .= "</div></div></div>";
    return $retorno;
}

function listaDivFuncao($papel, $tipo_papel, $headerColor, $bodyColor, $situacao)
{
    $con = new conexao();
    $con->connect();
    $anodata = date('Y');
    $retorno = "<div class='card mb-4'><div class='card-header' style='background-color: $headerColor;'>$papel</div><div class='card-body' style='background-color: $bodyColor;'>";
    $retorno .= "<div class='table-responsive'><table class='table table-hover'>";
    $retorno .= "<thead><tr><th>Foto</th><th>Nome/ID</th><th>Status</th><th>Ações</th></tr></thead><tbody>";
    $quant = 0;
    $nivelUsuario = !empty($_SESSION['nivel']) ? $_SESSION['nivel'] : 0;
    $sql = "SELECT c.*, a.*, a.cache as cacheano, 
            (select count(*) from pend_cad where id_colaborador = c.id and pendencia = 1) as pendencia 
            FROM colaborador c, ano_projeto a 
            WHERE c.id = a.id_colaborador 
            AND a.ano = '$anodata'";
    if ($situacao == 'PENDENTE') {
        $sql .= " AND (a.situacao IN ('PENDENTE') or a.situacao IN ('SIM') && papel1 is null)";

    } else {
        $sql .= "  AND a.situacao IN ('$situacao') ";
    }

    if ($tipo_papel != '') {
        $sql .= " AND (papel1 LIKE '$tipo_papel%' OR papel2 LIKE '$tipo_papel%' OR papel3 LIKE '$tipo_papel%')";
    }
    $sql .= " GROUP BY id ORDER BY nome ASC LIMIT 1000";
    $consulta = mysqli_query($con->connect(), $sql);
    while ($campo = mysqli_fetch_array($consulta)) {
        $quant++;
        $retorno .= "<tr>";
        // Coluna Foto
        $retorno .= "<td>";
        $consulta3 = mysqli_query($con->connect(), "SELECT * FROM foto_colaborador WHERE tipo = 'P' AND id_colaborador='" . $campo['id'] . "' ORDER BY id DESC LIMIT 1");
        if (mysqli_num_rows($consulta3) > 0) {
            while ($campo3 = mysqli_fetch_array($consulta3)) {
                $retorno .= "<img src='../projeto/upload_pic/" . $campo3['foto'] . "' alt='Foto'>";
            }
        } else {
            $retorno .= "<img src='../projeto/images/default-avatar.png' alt='Sem Foto'>";
        }
        $retorno .= "</td>";
        // Coluna Nome/ID
        $retorno .= "<td><strong>$quant / " . $campo['id'] . "</strong><br>" . strtoupper($campo['nome']) . "</td>";
        // Coluna Status
        $retorno .= "<td>";
        if ($situacao == "SIM") {
            $retorno .= $campo['pendencia'] > 0 ? "<span class='status-pendente'>Falta Documentação</span>" : "<span class='status-ok'>Documentação OK</span>";
        } else {
            $retorno .= "<span class='text-warning'>Aguardando Vaga</span>";
        }
        $retorno .= "</td>";
        // Coluna Ações
        $retorno .= "<td>";
        if ($nivelUsuario > 0) {
            $retorno .= "<button class='btn btn-success btn-sm me-1' onclick='autorizarCadastro(" . $campo['id'] . ")'>Autorizar</button>";
        }
        $retorno .= "<a href='../inscricao/form_foto_documentacao.php?id=" . $campo['id'] . "' class='btn btn-primary btn-sm'>Atualizar</a>";
        $retorno .= "</td>";
        $retorno .= "</tr>";
    }
    $retorno .= "</tbody></table></div>";
    $retorno .= "</div></div>";
    return $retorno;
}

function mostrarAniversariantesHoje2()
{
    $hoje = date('m-d'); // Dia e mês de hoje, ex: 04-11
    $anoAtual = date('Y'); // Ano atual, ex: 2025
    $con = new conexao();
    $link = $con->connect();

    $sql = "SELECT c.nome, c.nascimento 
            FROM colaborador c
            INNER JOIN ano_projeto ap ON c.id = ap.id_colaborador
            WHERE c.nascimento IS NOT NULL 
            AND ap.ano = '$anoAtual'";
    $resultado = mysqli_query($link, $sql);

    // Verifica se a consulta deu erro
    if (!$resultado) {
        die("Erro na consulta SQL: " . mysqli_error($link));
    }

    $aniversariantes = [];

    while ($linha = mysqli_fetch_assoc($resultado)) {
        $dataNascimento = date('m-d', strtotime($linha['nascimento']));
        if ($dataNascimento === $hoje) {
            $aniversariantes[] = $linha['nome'];
        }
    }

    if (count($aniversariantes) > 0) {
        echo '<p style="color:green; font-size:30px;">🎉 Feliz Aniversário: ' . implode(', ', $aniversariantes) . ' 🎂</p>';
    }

    mysqli_close($link);
}

if (isset($_POST['action']) && $_POST['action'] == 'autorizarcadastro') {
    $id_colaborador = $_POST['id'];
    $anodata = date('Y');
    $crud = new crud('ano_projeto');
    $crud->atualizar("situacao='SIM',papel1='Figurante 1',tipo='C'", "id_colaborador='$id_colaborador' and ano='$anodata'");
}
?>

<script>
    function autorizarCadastro(id) {
        $.ajax({
            url: "../inscricao/lista.php",
            type: "POST",
            data: { id: id, "action": "autorizarcadastro" },
            success: function (data) {
                alert('Autorizado com sucesso');
                location.reload();
            },
            error: function (data) {
                alert('Houve um erro ao enviar o formulário.');
                console.log(data);
            }
        });
    }
</script>