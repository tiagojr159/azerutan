<?php
//projeto.php
require_once 'config/conexao.class.php';
require_once 'config/crud.class.php';
require_once 'config.php';

$con  = new conexao();
$conn = $con->connect();
if (!$conn) {
    die('Erro ao conectar ao banco de dados: ' . $con->getError());
}
$id_projeto = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id_projeto == 0) {
    header('Location: index.php');
    exit;
}
$sql = "SELECT id, nome, categoria FROM projetos WHERE ativo = 1 ORDER BY nome";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Erro na consulta: " . mysqli_error($conn));
}

require_once 'header.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Paixão de Cristo de Igarassu — Azerutan</title>

    <link rel="stylesheet" href="styles-autocomplete.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/jquery.autocomplete.js"></script>
    <script src="js/autocompletar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</head>

<body>



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

    $INSCRICOES_ABERTAS = !empty($projeto['inscricoes_abertas']) ? (int)$projeto['inscricoes_abertas'] : 0;

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



    <div class="container mt-5 pt-4">
        <div class="card-az p-4">
            <div class="text-center mb-4">
                <img width="50%"
                    src="<?= $link_imagem_projeto; ?>../<?= $projeto['link_img']; ?>" />
                <h2 class="mt-2"><?= htmlspecialchars($projeto['nome']); ?></h2>
                <span class="proj-cat"><?= htmlspecialchars($projeto['categoria']); ?></span>
            </div>

            <p><strong>Ano do projeto:</strong> <?= htmlspecialchars($projeto['anoprojeto']); ?></p>
            <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($projeto['descricao'])); ?></p>
            <p><strong>Vagas:</strong> <?= htmlspecialchars($projeto['vagas']); ?></p>

            <?php
            // ====== EDITAIS (PDF) ======
            $editais = [];

            // 1) tenta buscar na tabela editais (novo)
            if ($st = mysqli_prepare($conn, "SELECT id, nome_arquivo, caminho_arquivo FROM editais WHERE projeto_id=? ORDER BY criado_em DESC, id DESC")) {
                mysqli_stmt_bind_param($st, 'i', $projeto['id']);
                mysqli_stmt_execute($st);
                $rs = mysqli_stmt_get_result($st);
                while ($r = mysqli_fetch_assoc($rs)) {
                    $editais[] = [
                        'nome' => $r['nome_arquivo'] ?: ('Edital #' . $r['id']),
                        'url'  => $r['caminho_arquivo']
                    ];
                }
                mysqli_stmt_close($st);
            }

            // 2) fallback: se não achou nada na tabela e existir link_pdf antigo
            if (count($editais) === 0 && !empty($projeto['link_pdf'])) {
                $editais[] = [
                    'nome' => 'Edital (PDF)',
                    'url'  => $projeto['link_pdf']
                ];
            }

            // 3) renderiza somente se tiver algum edital
            if (count($editais) > 0): ?>
                <p><strong>Edital:</strong>
                    <?php foreach ($editais as $i => $e):
                      $url_pdf = preg_replace('#upload_pic/#', '', $e['url'], 1);
                          $link = $link_imagem_projeto.$url_pdf; 
                        ?>
                        <?php if ($i > 0) echo ' | '; ?>
                        <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                            <?= htmlspecialchars($e['nome'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>

            <div class="text-center mt-4">
                <?php if ($INSCRICOES_ABERTAS == 1): ?>
                    <h3 style="color: green;">INSCRIÇÕES ABERTAS</h3>
                    <br><br>
                    <a href="inscricao.php?projeto=<?= urlencode($projeto['id']); ?>"
                        id="btnFazerInscricao"
                        class="btn btn-success btn-lg"
                        data-toggle="modal" data-target="#modalInscricao">
                        Fazer inscrição
                    </a>
                    <a href="inscricao.php?projeto=<?= urlencode($projeto['id']); ?>"
                        id="btn-nova-inscricao"
                        class="btn btn-primary btn-lg"
                        data-toggle="modal" data-target="#modalInscricao">
                        Novo Cadastro
                    </a>
                <?php else: ?>
                    <!-- Inscrições fechadas: mostra somente o texto -->
                    <span class="btn btn-secondary btn-lg disabled" aria-disabled="true">
                        Inscrições encerradas
                    </span>
                <?php endif; ?>





    <div class="modal fade" id="modalCertificado" tabindex="-1" aria-labelledby="modalCertificadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <form id="formCertificado" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCertificadoLabel">Confirmar data de nascimento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idColabCert" name="id_colaborador">
                    <input type="hidden" id="idProjCert" name="id_projeto">
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
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalInscricaoLabel">Fazer matrícula nesse projeto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">

                    <!-- hidden do projeto (AJAX usa isso) -->
                    <input type="hidden" id="id_projeto_modal" value="<?= (int)$projeto['id']; ?>">
                    <!-- hidden do colaborador vindo do autocomplete -->
                    <input type="hidden" id="id_colaborador" value="">

                    <div class="mb-3">
                        <label for="autocomplete-ajax-bairro" class="form-label">Nome</label>
                        <input type="text" id="autocomplete-ajax-bairro" class="form-control"
                            placeholder="Digite seu nome" style="text-transform: uppercase;">
                    </div>

                    <div class="mb-3">
                        <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                        <input type="date" id="data_nascimento" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" id="btn-renovar" class="btn btn-primary">
                        Fazer Inscrição
                    </button>
                </div>

            </div>
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
                        echo listaColaboradores($id_projeto, 'Direção Secundária', 'Direção Secundária', '#4CAF50', '#DCEDC8', 'SIM');
                        echo listaColaboradores($id_projeto, 'Assistente de Direção', 'Assist', '#4CAF50', '#DCEDC8', 'SIM');
                        echo listaColaboradores($id_projeto, 'Produção', 'Prod', '#81C784', '#F1F8E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Elenco', 'Elen', '#66BB6A', '#C8E6C9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Bailarino(a)s', 'Bailarino1', '#A5D6A7', '#E8F5E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Bailarino(a)s - Crianças', 'Bailarino2', '#A5D6A7', '#DCEDC8', 'SIM');
                        echo listaColaboradores($id_projeto, 'Músico(a)s', 'Músico', '#81C784', '#F1F8E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Secundário', 'Secun', '#4CAF50', '#C8E6C9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Figurantes', 'Fig', '#66BB6A', '#E8F5E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Selecionado', 'Selecionad', '#7ee683ff', '#E8F5E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Aluno', 'Alun', '#7ee683ff', '#E8F5E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Professor', 'Professo', '#6f8170ff', '#E8F5E9', 'SIM');
                        echo listaColaboradores($id_projeto, 'Pendente de Autorização', '', '#B0BEC5', '#ECEFF1', 'PENDENTE');
                    } else {
                        echo "<a href='projeto.php?action=modular&id=" . $id . "' class='btn btn-primary mb-3'>Exibir Lista</a>";
                        echo listaDivFuncao($id_projeto, 'Direção', 'Dir', '#A5D6A7', '#E8F5E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Direção Secundária', 'Direção Secundária', '#4CAF50', '#DCEDC8', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Assistente de Direção', 'Assist', '#90cd71ff', '#DCEDC8', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Produção', 'Prod', '#81C784', '#F1F8E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Elenco', 'Elen', '#66BB6A', '#C8E6C9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Bailarino(a)s', 'Bailarino1', '#A5D6A7', '#5ec066ff', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Bailarino(a)s - Crianças', 'Bailarino2', '#A5D6A7', '#DCEDC8', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Músico(a)s', 'Músico', '#81C784', '#F1F8E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Secundário', 'Secun', '#4CAF50', '#C8E6C9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Figurantes', 'Fig', '#66BB6A', '#E8F5E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Selecionado', 'Selecionado', '#7ee683ff', '#E8F5E9', 'SIM');
                        echo listaDivFuncao($id_projeto, 'Aluno', 'Alun', '#7ee683ff', '#E8F5E9', 'SIM');
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
                $sql = "SELECT 
            c.*, 
            a.*, 
            a.cache AS cacheano,
            (SELECT COUNT(*) 
               FROM pend_cad pc 
              WHERE pc.id_colaborador = c.id 
                AND pc.pendencia = 1
            ) AS pendencia
        FROM colaborador c
        JOIN ano_projeto a ON c.id = a.id_colaborador
        WHERE a.id_projeto = '$id_projeto'";

                if ($situacao == 'PENDENTE') {
                    $sql .= " AND (a.situacao = 'PENDENTE' OR (a.situacao = 'SIM' AND a.papel1 IS NULL))";
                } else {
                    $sql .= " AND a.situacao = '$situacao' ";
                }

                if ($tipo_papel != '') {
                    $sql .= " AND (a.papel1 LIKE '$tipo_papel%' OR a.papel2 LIKE '$tipo_papel%' OR a.papel3 LIKE '$tipo_papel%')";
                }

                $sql .= " ORDER BY c.nome ASC LIMIT 1000";

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
                $sql = "SELECT 
            c.*, 
            a.*, 
            a.cache AS cacheano,
            (SELECT COUNT(*) 
               FROM pend_cad pc 
              WHERE pc.id_colaborador = c.id 
                AND pc.pendencia = 1
            ) AS pendencia
        FROM colaborador c
        JOIN ano_projeto a ON c.id = a.id_colaborador
        WHERE a.id_projeto = '$id_projeto'";

                if ($situacao == 'PENDENTE') {
                    // mantém sua regra: pendente OU (sim E papel1 null)
                    $sql .= " AND (a.situacao = 'PENDENTE' OR (a.situacao = 'SIM' AND a.papel1 IS NULL))";
                } else {
                    $sql .= " AND a.situacao = '$situacao' ";
                }

                if ($tipo_papel != '') {
                    $sql .= " AND (a.papel1 LIKE '$tipo_papel%' OR a.papel2 LIKE '$tipo_papel%' OR a.papel3 LIKE '$tipo_papel%')";
                }

                $sql .= " ORDER BY c.nome ASC LIMIT 1000";

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

                    if ($EXIBIR_CERTIFICADO == 1 && $situacao != 'PENDENTE') {
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
                            alert('Selecione seu nome na lista (autocomplete) ou faça uma nova inscrição.');
                            $('#autocomplete-ajax-bairro').focus();
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


                })();



                function setupModalRenovacao() {

                    // 1) Abrir modal pelos botões
                    $(document).on('click', '#btnFazerInscricao, #btnFazerInscricao2', function(e) {
                        e.preventDefault();
                        const modalEl = document.getElementById('modalInscricao');
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    });

                    // 2) Autocomplete (somente 1 vez)
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

                    // 3) Se o usuário digitar e mudar o nome depois, zera o ID (evita renovar com ID errado)
                    $(document).on('input', '#autocomplete-ajax-bairro', function() {
                        $('#id_colaborador').val('');
                    });

                    // 4) Novo Cadastro
                    $(document).on('click', '#btn-nova-inscricao', function() {
                        const idProjeto = ($('#id_projeto_modal').val() || '').trim();
                        window.location.href = 'inscricao.php?novo=1&id_projeto=' + encodeURIComponent(idProjeto);
                    });

                    // 5) Validação
                    function validar() {
                        const nome = ($('#autocomplete-ajax-bairro').val() || '').trim();
                        const nasc = ($('#data_nascimento').val() || '').trim();
                        const idCol = ($('#id_colaborador').val() || '').trim();
                        const idProj = ($('#id_projeto_modal').val() || '').trim();

                        if (!idProj) {
                            alert('Projeto inválido.');
                            return false;
                        }

                        if (!nome) {
                            alert('Informe o Nome.');
                            $('#autocomplete-ajax-bairro').addClass('border-danger').focus();
                            return false;
                        } else {
                            $('#autocomplete-ajax-bairro').removeClass('border-danger');
                        }

                        if (!nasc) {
                            alert('Informe a Data de Nascimento.');
                            $('#data_nascimento').addClass('border-danger').focus();
                            return false;
                        } else {
                            $('#data_nascimento').removeClass('border-danger');
                        }

                        // precisa do ID do autocomplete
                        if (!idCol) {
                            alert('Selecione seu nome na lista (autocomplete). Se não aparecer, faça um novo cadastro.');
                            return false;
                        }

                        return true;
                    }

                    // 6) Renovar (AJAX)
                    $(document).on('click', '#btn-renovar', function(e) {
                        e.preventDefault();
                        if (!validar()) return;

                        const $btn = $('#btn-renovar');
                        $btn.prop('disabled', true).text('Validando...');

                        $.ajax({
                                url: 'crud_consulta.php',
                                method: 'GET',
                                dataType: 'json',
                                data: {
                                    action: 'valida_renovacao',
                                    ajax: 1,
                                    projeto: $('#id_projeto_modal').val(),
                                    id_colaborador: $('#id_colaborador').val(),
                                    data_nascimento: $('#data_nascimento').val(),
                                    nome: $('#autocomplete-ajax-bairro').val()
                                },
                                timeout: 15000
                            })
                            .done(function(resp) {
                                if (resp && resp.ok) {
                                    if (resp.redirect) {
                                        window.location.href = resp.redirect;
                                    } else {
                                        alert(resp.msg || 'Renovação OK.');
                                        location.reload();
                                    }
                                } else {
                                    alert((resp && resp.msg) ? resp.msg : 'Não foi possível validar.');
                                    if (resp && resp.redirect) window.location.href = resp.redirect;
                                }
                            })
                            .fail(function(xhr) {
                                alert('Falha na comunicação. Tente novamente.');
                                console.log(xhr.responseText);
                            })
                            .always(function() {
                                $btn.prop('disabled', false).text('Renovar Inscrição');
                            });
                    });

                    // 7) ENTER na data também renova
                    $(document).on('keydown', '#data_nascimento', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            $('#btn-renovar').click();
                        }
                    });
                }

                // chama 1 vez
                $(function() {
                    setupModalRenovacao();
                });
            </script>

            <script>
                // Abre o modal e preenche os IDs do colaborador/projeto
                $(document).on('click', '.btn-certificado', function() {
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
                    const id_projeto = $('#idProjCert').val();
                    const nascimento = $('#nascimento').val(); // YYYY-MM-DD

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
                    }).done(function(resp) {
                        if (resp.ok) {
                            // Pode incluir nascimento no GET para reforçar a checagem no PHP
                            window.location.href = 'certificado.php?id_colaborador=' + encodeURIComponent(id_colaborador) +
                                '&id_projeto=' + encodeURIComponent(id_projeto) +
                                '&nascimento=' + encodeURIComponent($('#nascimento').val());
                        } else {
                            alert(resp.msg || 'Data de nascimento não confere.');
                        }
                    }).fail(function() {
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