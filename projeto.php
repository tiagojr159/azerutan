<?php
require_once 'config/conexao.class.php';
require_once 'config/crud.class.php';
require_once 'config.php';

$con  = new conexao();
$conn = $con->connect();
if (!$conn) {
    die('Erro ao conectar ao banco de dados: ' . $con->getError());
}
$id_projeto = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$sql = "SELECT id, nome, categoria FROM projetos WHERE ativo = 1 ORDER BY nome";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Erro na consulta: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Paixão de Cristo de Igarassu — Azerutan</title>

    <!-- Bootstrap -->

    <!-- (NOVO) CSS do autocomplete -->
    <link rel="stylesheet" href="styles-autocomplete.css">



    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/jquery.autocomplete.js"></script>
    <script src="js/autocompletar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>














    <!-- Estilo do layout (paleta reaproveitada) -->
    <style>
        :root {
            --primary: #20b2aa;
            /* lightseagreen */
            --primary-700: #17928b;
            --bg: #f4f7f8;
            --card: #ffffff;
            --text: #1b1f23;
            --muted: #6c757d;
            --ok: #32cd32;
            /* limegreen */
        }

        html,
        body {
            height: 100%
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
        }

        .navbar {
            background: var(--primary);
        }

        .navbar .nav-link,
        .navbar-brand {
            font-weight: 600
        }

        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-700) 100%);
            color: #fff;
            border-radius: 1rem;
            padding: 2rem;
            margin-top: 5rem;
        }

        .hero h1 {
            font-weight: 700;
            letter-spacing: .2px
        }

        .hero p {
            opacity: .95
        }

        .section-title {
            font-size: clamp(1.25rem, 1.2rem + .6vw, 1.75rem);
            font-weight: 700;
            margin: 1.5rem 0 .75rem;
            color: var(--primary-700);
        }

        .card-az {
            background: var(--card);
            border: 1px solid #e8ecee;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .04);
            height: 100%;
        }

        .card-az .card-header {
            background: transparent;
            border-bottom: 1px solid #eef2f3;
            font-weight: 700;
            color: var(--primary-700);
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-700);
            border-color: var(--primary-700);
        }

        .status-chip {
            font-size: clamp(.95rem, .9rem + .2vw, 1.15rem);
            color: #fff;
            background: var(--ok);
            border-radius: .6rem;
            padding: .25rem .6rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* grade dos projetos (responsivo) */
        .proj-card {
            transition: transform .18s ease, box-shadow .18s ease;
            text-align: center;
            padding: 1.25rem 1rem;
        }

        .proj-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 26px rgba(0, 0, 0, .08);
        }

        .proj-icon {
            width: 72px;
            height: 72px;
            object-fit: contain;
            margin-bottom: .5rem;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, .12));
        }

        .proj-name {
            font-weight: 700;
            margin: .25rem 0 0;
            font-size: 1.05rem;
        }

        .proj-cat {
            font-size: .85rem;
            color: var(--muted);
        }

        /* utilidades */
        .gap-12 {
            gap: 12px
        }

        .mt-32 {
            margin-top: 32px
        }

        /* ajustes mobile */
        @media (max-width: 575.98px) {
            .navbar-brand {
                font-size: 1.05rem
            }

            .hero {
                padding: 1.25rem;
                margin-top: 4rem
            }

            .card-az {
                border-radius: .9rem
            }

            .proj-icon {
                width: 64px;
                height: 64px
            }
        }

    </style>

    <!-- (NOVO) jQuery COMPLETO (necessário para autocomplete/AJAX) -->
    <!-- (NOVO) plugin de autocomplete -->
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-md navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Azerutan</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMain"
                aria-controls="navMain" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="index.php">Início</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="drop01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Secretaria
                        </a>
                        <div class="dropdown-menu" aria-labelledby="drop01">
                            <a class="dropdown-item" href="inscricao.php?novo=1">Nova Matrícula</a>
                            <a class="dropdown-item" href="inscricao_renovar.php">Renovar Matrícula</a>
                            <a class="dropdown-item" href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC" target="_blank" rel="noopener">WhatsApp — Secretaria</a>
                        </div>
                    </li>
                </ul>
                <form class="form-inline my-2 my-lg-0">
                    <input class="form-control mr-2" type="text" placeholder="Pesquisar" aria-label="Pesquisar" />
                    <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Buscar</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <div class="container">
        <section class="hero">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="mb-2">Associação Cultural Azerutan</h1>
                    <p class="mb-0">
                        Inscrições, renovação e lista de candidatos do grupo de teatro <strong>Azerutan</strong>.
                    </p>
                </div>
                <div class="col-lg-5 text-lg-right mt-3 mt-lg-0">
                    <span class="status-chip">INSCRIÇÕES ABERTAS</span>
                </div>
            </div>
        </section>
    </div>

    <?php


    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $sql = "SELECT * FROM projetos WHERE id = $id LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $projeto = mysqli_fetch_assoc($result);

    if (!$projeto) {
        die("Projeto não encontrado.");
    }

    // ===========================
    // [AJUSTE MÍNIMO] SINALIZADORES
    // ===========================
    $EXIBIR_LISTA_SELECIONADOS = !empty($projeto['exibir_lista_selecionados']) ? (int)$projeto['exibir_lista_selecionados'] : 0;
    $EXIBIR_CERTIFICADO        = !empty($projeto['exibir_certificado']) ? (int)$projeto['exibir_certificado'] : 0;
    // ===========================

    $cat = strtolower(preg_replace('/[^a-z0-9_-]+/i', '', $projeto['categoria'] ?? 'projeto'));
    $icon = $link_imagem_projeto . "" . $projeto['link_img'];
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">


    <!-- Navbar -->
    <nav class="navbar navbar-expand-md navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Azerutan</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-4">
        <div class="card-az p-4">
            <div class="text-center mb-4">
                <img width="50%"
                    src="<?= $link_imagem_projeto; ?><?= $projeto['link_img']; ?>" />
                <h2 class="mt-2"><?= htmlspecialchars($projeto['nome']); ?></h2>
                <span class="proj-cat"><?= htmlspecialchars($projeto['categoria']); ?></span>
            </div>

            <p><strong>Ano do projeto:</strong> <?= htmlspecialchars($projeto['anoprojeto']); ?></p>
            <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($projeto['descricao'])); ?></p>
            <p><strong>Vagas:</strong> <?= htmlspecialchars($projeto['vagas']); ?></p>

            <div class="text-center mt-4">
                <!-- (AJUSTE) Abre modal, mantendo href como fallback -->
                <a href="inscricao.php?projeto=<?= urlencode($projeto['id']); ?>" id="btnFazerInscricao" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#modalInscricao">
                    Fazer inscrição
                </a>
            </div>


        </div>
    </div>






        <!-- [AJUSTE MÍNIMO] Modal Certificado -->
 <!-- Modal Certificado (único, responsivo) -->
<!-- Modal Certificado (único, responsivo) -->
<div class="modal fade" id="modalCertificado" tabindex="-1" aria-labelledby="modalCertificadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <form id="formCertificado" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCertificadoLabel">Confirmar data de nascimento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="idColabCert" name="id_colaborador">
        <input type="hidden" id="idProjCert"  name="id_projeto">
        <label for="nascimento" class="form-label">Data de nascimento</label>
        <input type="date" class="form-control" id="nascimento" name="nascimento" required>
        <small class="text-muted">Informe sua data para liberar o certificado.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Continuar</button>
      </div>
    </form>
  </div>
</div>



    <!-- (NOVO) Modal INSCRIÇÃO/RENOVAÇÃO – autocomplete + data de nascimento -->
    <div class="modal fade" id="modalInscricao" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form id="form-renovacao" class="modal-content" method="get" action="cer.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInscricaoLabel">Fazer matrícula nesse projeto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>

                </div>
                <div class="modal-body">
                    <!-- mesmo fluxo da tela Renovar Inscrição -->
                    <input type="hidden" name="action" value="valida_renovacao">
                    <input type="hidden" name="projeto" value="<?= (int)$projeto['id']; ?>">
                    <input type="hidden" id="id_colaborador" name="id_colaborador">



                    <div class="modal-footer d-flex justify-content-between flex-wrap">

                        <form action="crud_consulta.php" method="get" name="dados" id="form-inscricao" class="mt-4">
                            <input type="hidden" name="action" value="valida_renovacao">
                            <input type="hidden" name="id_colaborador" id="id_colaborador">

                            <div class="">
                                <label for="nome">Nome:</label>
                                <input type="text" name="nome" id="autocomplete-ajax-bairro" class="form-control"
                                    placeholder="Digite seu nome" style="text-transform: uppercase;">
                            </div>

                            <div class="">
                                <label for="data_nascimento">Data de Nascimento:</label>
                                <input type="date" name="data_nascimento" id="data_nascimento" class="form-control">
                            </div>

                        </form>
                    </div>





                    <div class="modal-footer d-flex justify-content-between flex-wrap">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                        <div class="ml-auto">
                            <button type="button" id="btn-nova-inscricao" class="btn btn-success d-none">Nova Inscrição</button>
                            <button type="submit" id="btn-renovar" class="btn btn-primary">Renovar Inscrição</button>
                        </div>
                    </div>
            </form>
        </div>
    </div>












    </div>
    </div>
    <div class="container mt-5 pt-4">
        <div class="card-az p-4">


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

            </head>




            <main class="container mt-5 pt-5">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header" style="background-color: #A5D6A7;">Nova Matrícula</div>
                            <div class="card-body">
                                <p>Se você nunca fez parte do grupo Azerutan, clique abaixo para criar sua matrícula.</p>
                                <a href="inscricao.php?novo=1&id_projeto=<?= $_GET['id'] ?>" class="btn btn-success">Nova Matrícula</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header" style="background-color: #A5D6A7;">Renovação de Matrícula</div>
                            <div class="card-body">
                                <p>Renove sua matrícula se já participou da Paixão de Cristo com o Azerutan.</p>
                                <a href="inscricao.php?projeto=<?= urlencode($projeto['id']); ?>" id="btnFazerInscricao2" class="btn btn-success">Renovar Matrícula</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header" style="background-color: #A5D6A7;">Secretaria Azerutan</div>
                            <div class="card-body">
                                <p>Entre no grupo do WhatsApp para dúvidas ou suporte.</p>
                                <a href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC">
                                    <img src="images/icone/whatsapp.png"
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
                        echo "<a href='projeto.php?action=lista&id=" . $id . "' class='btn btn-primary mb-3'>Exibir Lista</a>";
                        echo listaColaboradores($id_projeto, 'Direção', 'Dir', '#A5D6A7', '#E8F5E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Direção Secundária', 'Assist', '#4CAF50', '#DCEDC8', 'SIM');
                        echo listaColaboradores($id_projeto, 'Produção', 'Prod', '#81C784', '#F1F8E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Elenco', 'Elen', '#66BB6A', '#C8E6C9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Bailarino(a)s', 'Bailarino1', '#A5D6A7', '#E8F5E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Bailarino(a)s - Crianças', 'Bailarino2', '#A5D6A7', '#DCEDC8', 'SIM');
                        echo listaColaboradores($id_projeto, 'Músico(a)s', 'Músico', '#81C784', '#F1F8E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Secundário', 'Secun', '#4CAF50', '#C8E6C9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Figurantes', 'Fig', '#66BB6A', '#E8F5E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Selecionado', 'Fig', '#7ee683ff', '#E8F5E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Professor', 'Professor', '#6f8170ff', '#E8F5E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Pendente de Autorização', '', '#B0BEC5', '#ECEFF1', 'PENDENTE');
                    } else {
                        echo "<a href='projeto.php?action=modular&id=" . $id . "' class='btn btn-primary mb-3'>Exibir Lista</a>";
                        echo listaDivFuncao($id_projeto, 'Direção', 'Dir', '#A5D6A7', '#E8F5E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Direção Secundária', 'Assist', '#4CAF50', '#DCEDC8', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Produção', 'Prod', '#81C784', '#F1F8E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Elenco', 'Elen', '#66BB6A', '#C8E6C9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Bailarino(a)s', 'Bailarino1', '#A5D6A7', '#E8F5E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Bailarino(a)s - Crianças', 'Bailarino2', '#A5D6A7', '#DCEDC8', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Músico(a)s', 'Músico', '#81C784', '#F1F8E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Secundário', 'Secun', '#4CAF50', '#C8E6C9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Figurantes', 'Fig', '#66BB6A', '#E8F5E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Selecionado', 'Selecionado', '#7ee683ff', '#E8F5E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Professor', 'Professor', '#6f8170ff', '#E8F5E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Pendente de Autorização', '', '#B0BEC5', '#ECEFF1', 'PENDENTE');
                    }
                    ?>
                </div>
            </main>



            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


            <?php

            function listaColaboradores($id_projeto, $papel, $tipo_papel, $headerColor, $bodyColor, $situacao)
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
            AND a.id_projeto = '$id_projeto'";
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
                            $retorno .= "<img src='https://paixaodecristodeigarassu.ki6.com.br/projeto/upload_pic/" . $campo3['foto'] . "' class='img-fluid rounded mb-2' style='max-width: 300px; height: 100px;'>";
                        }
                    } else {
                        $retorno .= "<img src='https://paixaodecristodeigarassu.ki6.com.br/projeto/images/default-avatar.png' class='img-fluid rounded mb-2' style='max-width: 150px; height: 100px;'>";
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
                    $retorno .= "<a href='form_foto_documentacao.php?id=" . $campo['id'] . "' class='btn btn-primary btn-sm mt-2'>Atualizar Dados</a>";
                    $retorno .= "</div></div>";
                }
                $retorno .= "</div></div></div>";
                return $retorno;
            }

            function listaDivFuncao($id_projeto, $papel, $tipo_papel, $headerColor, $bodyColor, $situacao)
            {
                // ===========================
                // [AJUSTE MÍNIMO] acessar flag do projeto
                // ===========================
                global $EXIBIR_CERTIFICADO;

                $con = new conexao();
                $con->connect();

                $listaPreenchida = false;

                $anodata = date('Y');
                $retorno = "<div class='card mb-4'><div class='card-header' style='background-color: $headerColor;'>$papel</div><div class='card-body' style='background-color: $bodyColor;'>";
                $retorno .= "<div class='table-responsive'><table class='table table-hover'>";
                $retorno .= "<thead><tr><th>Foto</th><th>Nome</th><th>Status</th><th>Ações</th></tr></thead><tbody>";
                $quant = 0;
                $nivelUsuario = !empty($_SESSION['nivel']) ? $_SESSION['nivel'] : 0;
                $sql = "SELECT c.*, a.*, a.cache as cacheano, 
            (select count(*) from pend_cad where id_colaborador = c.id and pendencia = 1) as pendencia 
            FROM colaborador c, ano_projeto a 
            WHERE c.id = a.id_colaborador 
            AND a.id_projeto = '$id_projeto'";
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
                $qtde = mysqli_num_rows($consulta); // conta linhas retornadas por esse SELECT
                if ($qtde > 0) {
                    $listaPreenchida = true;
                }
                while ($campo = mysqli_fetch_array($consulta)) {
                    $quant++;
                    $retorno .= "<tr>";
                    // Coluna Foto
                    $retorno .= "<td>";
                    $consulta3 = mysqli_query($con->connect(), "SELECT * FROM foto_colaborador WHERE tipo = 'P' AND id_colaborador='" . $campo['id'] . "' ORDER BY id DESC LIMIT 1");
                    if (mysqli_num_rows($consulta3) > 0) {
                        while ($campo3 = mysqli_fetch_array($consulta3)) {
                            $retorno .= "<img src='https://paixaodecristodeigarassu.ki6.com.br/projeto/upload_pic/" . $campo3['foto'] . "' alt='Foto' style='max-width: 300px; height: 100px;'>";
                        }
                    } else {
                        $retorno .= "<img src='https://paixaodecristodeigarassu.ki6.com.br/projeto/images/default-avatar.png' alt='Sem Foto' style='max-width: 300px; height: 100px;'>";
                    }
                    $retorno .= "</td>";
                    // Coluna Nome/ID
                    $retorno .= "<td><strong>$quant - </strong>" . strtoupper($campo['nome']) . "</td>";
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
                    $retorno .= "<a href='form_foto_documentacao.php?id=" . $campo['id'] . "' class='btn btn-primary btn-sm me-1'>Atualizar</a>";


                    if ($EXIBIR_CERTIFICADO == 1) {
                        $retorno .= "<button type='button' class='btn btn-info btn-sm btn-certificado'
  data-idcol='" . $campo['id'] . "'
  data-idproj='" . (int)$id_projeto . "'
  data-nome=\"" . htmlspecialchars($campo['nome'], ENT_QUOTES) . "\"
  data-bs-toggle='modal' data-bs-target='#modalCertificado'>Certificado</button>";
                    }

                    $retorno .= "</td>";
                    $retorno .= "</tr>";
                }
                $retorno .= "</tbody></table></div>";
                $retorno .= "</div></div>";
                if ($listaPreenchida == false) {
                    $retorno = "";
                }
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
                $(function() {
                    // Botão "Fazer inscrição" abre o modal
                    $('#btnFazerInscricao').on('click', function(e) {
                        e.preventDefault();
                        $('#modalInscricao').modal('show');
                    });
                    $('#btnFazerInscricao2').on('click', function(e) {
                        e.preventDefault();
                        $('#modalInscricao').modal('show');
                    });


                    // Ir para nova inscrição
                    $('#btn-nova-inscricao').on('click', function() {
                        window.location.href = 'inscricao.php?projeto=<?= (int)$projeto['id'] ?>';
                    });

                    // Validação do submit (renovação)
                    $('#form-renovacao').on('submit', function(e) {
                        const idCol = $('#id_colaborador').val().trim();
                        const dt = $('#data_nascimento').val().trim();
                        if (!idCol) {
                            e.preventDefault();
                            alert('Selecione seu nome na lista ou crie uma cadastro novo');
                            $('#autocomplete-nome').focus();
                        }
                        if (!dt) {
                            e.preventDefault();
                            alert('Informe a data de nascimento.');
                            $('#data_nascimento').focus();
                        }
                    });
                });
            </script>



            <script>
                function autorizarCadastro(id) {
                    $.ajax({
                        url: "../inscricao/lista.php",
                        type: "POST",
                        data: {
                            id: id,
                            "action": "autorizarcadastro"
                        },
                        success: function(data) {
                            alert('Autorizado com sucesso');
                            location.reload();
                        },
                        error: function(data) {
                            alert('Houve um erro ao enviar o formulário.');
                            console.log(data);
                        }
                    });
                }
            </script>
            <script>
                $(document).ready(function() {
                    $("#form-inscricao").submit(function(e) {
                        var nome = $("#autocomplete-ajax-bairro").val().trim();
                        var dataNascimento = $("#data_nascimento").val().trim();

                        if (nome === "") {
                            alert("Por favor, escolha seu Nome no campo nome antes de enviar o formulário.");
                            $("#autocomplete-ajax-bairro").addClass("border-danger");
                            e.preventDefault();
                        }

                        if (dataNascimento === "") {
                            alert("Por favor, preencha a Data de Nascimento antes de enviar o formulário.");
                            $("#data_nascimento").addClass("border-danger");
                            e.preventDefault();
                        } else {
                            $("#data_nascimento").removeClass("border-danger");
                        }
                    });

                    $('#autocomplete-ajax-bairro').autocomplete({
                        serviceUrl: 'crud_consulta.php?action=consultaNome',
                        dataType: 'json',
                        onSelect: function(suggestion) {
                            $('#id_colaborador').val(suggestion.data);
                        }
                    });
                });
            </script>



            <script>
                (function() {

                    $('#autocomplete-ajax-bairro').autocomplete({
                        serviceUrl: 'crud_consulta.php?action=consultaNome',
                        dataType: 'json',
                        minChars: 2,
                        deferRequestBy: 200,
                        onSelect: function(sug) {
                            $('#id_colaborador').val(sug.data);
                            $('#autocomplete-ajax-bairro').removeClass('border-danger');
                        }
                    });

                    // ====== Validação simples (mesma lógica que você usa) ======
                    function validarCampos() {
                        var nome = $('#autocomplete-ajax-bairro').val().trim();
                        var nasc = $('#data_nascimento').val().trim();
                        var idCol = $('#id_colaborador').val().trim();
                        var ok = true;

                        if (!nome) {
                            alert('Por favor, informe o Nome.');
                            $('#autocomplete-ajax-bairro').addClass('border-danger').focus();
                            ok = false;
                        } else {
                            $('#autocomplete-ajax-bairro').removeClass('border-danger');
                        }

                        if (!nasc) {
                            alert('Por favor, informe a Data de Nascimento.');
                            $('#data_nascimento').addClass('border-danger').focus();
                            ok = false;
                        } else {
                            $('#data_nascimento').removeClass('border-danger');
                        }

                        if (!idCol) {

                            alert('Selecione seu nome na lista (autocomplete) para renovar a matrícula, caso contrario, crie um cadastro novo.');
                            ok = false;
                        }

                        return ok;
                    }

                    // ====== Função AJAX da renovação ======
                    function enviarRenovacaoAJAX() {
                        var $btn = $('#btn-renovar');
                        var params = {
                            action: 'valida_renovacao',
                            projeto: $('input[name="projeto"]').val(),
                            id_colaborador: $('#id_colaborador').val(),
                            data_nascimento: $('#data_nascimento').val(),
                            ajax: 1 // se o backend entender isso e devolver JSON, melhor
                        };

                        $btn.prop('disabled', true).text('Enviando...');

                        $.ajax({
                                url: 'crud_consulta.php',
                                method: 'GET',
                                data: params,
                                dataType: 'json',
                                timeout: 15000
                            })
                            .done(function(resp) {
                                if (resp && resp.ok) {
                                    if (resp.redirect) {
                                        window.location.href = resp.redirect;
                                    } else {
                                        alert('Renovação validada com sucesso!');
                                        $('#modalInscricao').modal('hide');
                                    }
                                } else {
                                    var msg = (resp && resp.msg) ? resp.msg : 'Não foi possível validar seus dados.';
                                    alert(msg);
                                }
                            })
                            .fail(function() {
                                $('#form-inscricao')[0].submit();
                            })
                            .always(function() {
                                $btn.prop('disabled', false).text('Renovar Inscrição');
                            });
                    }

                    $('#form-inscricao').on('submit', function(e) {
                        e.preventDefault(); // evita navegação
                        if (!validarCampos()) return;
                        enviarRenovacaoAJAX();
                    });

                    $('#btn-renovar').on('click', function(e) {
                        if (this.type === 'button') {
                            e.preventDefault();
                            if (!validarCampos()) return;
                            enviarRenovacaoAJAX();
                        }
                    });

                })();
            </script>

           <script>
// Abre o modal e preenche os IDs do colaborador/projeto
$(document).on('click', '.btn-certificado', function () {
  $('#idColabCert').val($(this).data('idcol'));
  $('#idProjCert').val($(this).data('idproj'));
  $('#nascimento').val('');

  // Se precisar abrir programaticamente (além do data-bs-toggle):
  // const m = new bootstrap.Modal(document.getElementById('modalCertificado'));
  // m.show();
});

// Intercepta o submit do modal de certificado
$('#formCertificado').on('submit', function(e) {
  e.preventDefault(); // ← evita submit para projeto.php

  const id_colaborador = $('#idColabCert').val();
  const id_projeto     = $('#idProjCert').val();
  const nascimento     = $('#nascimento').val(); // YYYY-MM-DD

  if (!nascimento) {
    alert('Informe a data de nascimento.');
    $('#nascimento').focus();
    return;
  }

  // Validação AJAX no próprio certificado.php
$.getJSON('certificado.php', {
  action: 'confirmaNascimento',
  id_colaborador: id_colaborador,
  id_projeto: id_projeto,
  nascimento: $('#nascimento').val() // YYYY-MM-DD
}).done(function(resp){
  if (resp.ok) {
    // Pode incluir nascimento no GET para reforçar a checagem no PHP
    window.location.href = 'certificado.php?id_colaborador=' + encodeURIComponent(id_colaborador)
                         + '&id_projeto=' + encodeURIComponent(id_projeto)
                         + '&nascimento=' + encodeURIComponent($('#nascimento').val());
  } else {
    alert(resp.msg || 'Data de nascimento não confere.');
  }
}).fail(function(){
  alert('Falha ao validar a data. Tente novamente.');
});

});
</script>













            <!-- Realização -->
            <div class="container mt-32">
                <div class="card-az">
                    <div class="card-body text-center">
                        <h3 class="mb-3" style="color:var(--primary-700); font-weight:700">Realização</h3>
                        <img src="images/AssocAzerutan.png" style="max-width:520px; width:100%; height:auto;" alt="Associação Azerutan" />
                    </div>
                </div>
            </div>

            <footer class="container mt-32 mb-4 text-center" style="color:var(--muted)">
                © Azerutan 2017–<?php echo date('Y'); ?>
            </footer>

            <script src="./template/popper.min.js"></script>
</body>

</html>