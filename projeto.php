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

    <style>
        .project-carousel {
            max-width: 720px;
            margin: 0 auto 12px;
            border-radius: 18px;
            background: #f5f7f2;
            box-shadow: 0 10px 28px rgba(0, 0, 0, .08);
            padding: 14px;
            position: relative;
        }

        .project-carousel-viewport {
            overflow: hidden;
            border-radius: 14px;
        }

        .project-carousel-track {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .project-carousel-track.is-sliding-next {
            animation: projectCarouselNext .45s ease;
        }

        .project-carousel-track.is-sliding-prev {
            animation: projectCarouselPrev .45s ease;
        }

        .project-carousel-frame {
            border: 0;
            padding: 0;
            width: 100%;
            border-radius: 14px;
            overflow: hidden;
            background: #dfe7db;
            cursor: pointer;
        }

        .project-carousel-frame img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
        }

        .project-carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 999px;
            background: rgba(0, 0, 0, .58);
            color: #fff;
            font-size: 28px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
        }

        .project-carousel-nav.prev {
            left: 18px;
        }

        .project-carousel-nav.next {
            right: 18px;
        }

        .project-tabs {
            border-bottom: 0;
            gap: 10px;
            justify-content: center;
            margin-bottom: 18px;
        }

        .project-tabs .nav-link {
            border: 0;
            border-radius: 999px;
            background: #e7efe3;
            color: #35513a;
            font-weight: 700;
            padding: 10px 18px;
        }

        .project-tabs .nav-link.active {
            background: #198754;
            color: #fff;
        }

        .project-cover-image {
            width: min(100%, 720px);
            max-width: 78%;
            height: auto;
            display: block;
            margin: 0 auto 18px;
            border-radius: 16px;
            box-shadow: 0 10px 28px rgba(0, 0, 0, .12);
            object-fit: cover;
        }

        .project-gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .project-gallery-item {
            border: 0;
            padding: 0;
            background: transparent;
            border-radius: 14px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
        }

        .project-gallery-item img {
            width: 100%;
            height: 210px;
            object-fit: cover;
            display: block;
        }

        .project-gallery-empty {
            text-align: center;
            color: #6c757d;
            padding: 24px 0;
        }

        .project-photo-modal .modal-content {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
        }

        .modal {
            z-index: 2000 !important;
        }

        .modal-backdrop {
            z-index: 1990 !important;
        }

        #modalCertificado,
        #modalCertificado .modal-dialog,
        #modalCertificado .modal-content {
            z-index: 2001 !important;
        }

        .project-photo-modal .modal-body {
            padding: 0;
            background: #111;
        }

        .project-photo-modal img {
            width: 100%;
            max-height: 78vh;
            object-fit: contain;
            display: block;
            background: #111;
        }

        .birthdate-input-group {
            display: flex;
            align-items: stretch;
            gap: 8px;
            width: 100%;
        }

        .birthdate-input-group .form-control {
            flex: 1 1 auto;
            width: auto;
            min-width: 0;
        }

        .birthdate-calendar-btn {
            flex: 0 0 46px;
            width: 46px;
            min-width: 46px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0;
        }

        .birthdate-calendar-btn::before,
        .birthdate-calendar-icon {
            content: "\1F4C5";
            font-size: 18px;
            line-height: 1;
        }

        .birthdate-picker-native {
            position: fixed;
            left: -9999px;
            top: -9999px;
            opacity: 0;
            pointer-events: none;
            width: 1px;
            height: 1px;
            border: 0;
            padding: 0;
            margin: 0;
        }

        .download-loading-indicator {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 2100;
        }

        .download-loading-indicator.is-visible {
            display: flex;
        }

        .download-loading-card {
            min-width: 180px;
            padding: 18px 22px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .05);
            backdrop-filter: blur(6px);
            text-align: center;
            color: #fff;
        }

        .download-loading-spinner {
            width: 54px;
            height: 54px;
            margin: 0 auto 10px;
            border-radius: 999px;
            border: 4px solid rgba(255, 255, 255, .22);
            border-top-color: #198754;
            animation: downloadLoadingSpin .9s linear infinite;
        }

        .download-loading-text {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .01em;
        }

        @keyframes downloadLoadingSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes projectCarouselNext {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-70px);
            }
        }

        @keyframes projectCarouselPrev {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(70px);
            }
        }

        @media (max-width: 767px) {
            .project-cover-image {
                max-width: 100%;
                margin-bottom: 14px;
            }

            .project-carousel {
                padding: 10px;
                border-radius: 16px;
            }

            .project-carousel-viewport {
                border-radius: 12px;
            }

            .project-carousel-track {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .project-carousel-frame {
                border-radius: 12px;
            }

            .project-carousel-frame img {
                height: min(58vw, 260px);
            }

            .project-carousel-nav {
                width: 38px;
                height: 38px;
                font-size: 24px;
                background: rgba(0, 0, 0, .5);
            }

            .project-carousel-nav.prev {
                left: 14px;
            }

            .project-carousel-nav.next {
                right: 14px;
            }

            .project-gallery-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            .project-carousel-track {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .project-gallery-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

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

    $fotosProjeto = [];
    $tipoProjetoFotos = 'pj=' . (int)$projeto['id'];
    if ($stmtFotosProjeto = mysqli_prepare($conn, "SELECT foto FROM foto_colaborador WHERE tipo = ? ORDER BY id DESC LIMIT 20")) {
        mysqli_stmt_bind_param($stmtFotosProjeto, 's', $tipoProjetoFotos);
        mysqli_stmt_execute($stmtFotosProjeto);
        $resultFotosProjeto = mysqli_stmt_get_result($stmtFotosProjeto);
        while ($rowFotoProjeto = mysqli_fetch_assoc($resultFotosProjeto)) {
            $fotoRelativa = trim((string)($rowFotoProjeto['foto'] ?? ''));
            if ($fotoRelativa === '') {
                continue;
            }

            $fotosProjeto[] = [
                'thumb' => $link_imagem_projeto . ltrim($fotoRelativa, '/'),
                'full' => $link_imagem_projeto . ltrim(str_replace('thumbnail', 'resize', $fotoRelativa), '/'),
            ];
        }
        mysqli_stmt_close($stmtFotosProjeto);
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
            <ul class="nav nav-tabs project-tabs" id="projectTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-detalhes" data-bs-toggle="tab" data-bs-target="#pane-detalhes" type="button" role="tab" aria-controls="pane-detalhes" aria-selected="true">Detalhes</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-fotos" data-bs-toggle="tab" data-bs-target="#pane-fotos" type="button" role="tab" aria-controls="pane-fotos" aria-selected="false">Mais fotos</button>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="pane-detalhes" role="tabpanel" aria-labelledby="tab-detalhes" tabindex="0">
                    <div class="text-center mb-4">
                        <h2 class="mt-2"><?= htmlspecialchars($projeto['nome']); ?></h2>
                        <p ><?= htmlspecialchars($projeto['categoria']); ?></p>
                        
                        <img class="project-cover-image"
                            src="<?= $link_imagem_projeto; ?>../<?= $projeto['link_img']; ?>"
                            alt="<?= htmlspecialchars($projeto['nome']); ?>" />
                <?php if (!empty($fotosProjeto)) : ?>
                    <div id="carouselProjetoFotos" class="project-carousel"
                        data-fotos='<?= htmlspecialchars(json_encode($fotosProjeto, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8"); ?>'>
                        <button type="button" class="project-carousel-nav prev" data-action="prev" aria-label="Voltar fotos">&#8249;</button>
                        <div class="project-carousel-viewport">
                            <div class="project-carousel-track">
                                <button type="button" class="project-carousel-frame js-open-photo-modal"><img data-slot="0" src="" alt="Foto do projeto 1"></button>
                                <button type="button" class="project-carousel-frame js-open-photo-modal"><img data-slot="1" src="" alt="Foto do projeto 2"></button>
                                <button type="button" class="project-carousel-frame js-open-photo-modal"><img data-slot="2" src="" alt="Foto do projeto 3"></button>
                            </div>
                        </div>
                        <button type="button" class="project-carousel-nav next" data-action="next" aria-label="Avançar fotos">&#8250;</button>
                    </div>
                <?php endif; ?>
                    </div>

                </div>
                <div class="tab-pane fade" id="pane-fotos" role="tabpanel" aria-labelledby="tab-fotos" tabindex="0">
                    <?php if (!empty($fotosProjeto)) : ?>
                        <div class="project-gallery-grid mt-3">
                            <?php foreach ($fotosProjeto as $index => $fotoProjeto) : ?>
                                <button type="button"
                                    class="project-gallery-item js-open-photo-modal"
                                    data-full="<?= htmlspecialchars($fotoProjeto['full'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-thumb="<?= htmlspecialchars($fotoProjeto['thumb'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-index="<?= $index + 1; ?>">
                                    <img src="<?= htmlspecialchars($fotoProjeto['thumb'], ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="Foto do projeto <?= $index + 1; ?>"
                                        onerror="this.onerror=null;this.src='<?= htmlspecialchars($fotoProjeto['full'], ENT_QUOTES, 'UTF-8'); ?>';" />
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="project-gallery-empty">Este projeto ainda nÃ£o tem fotos adicionais.</div>
                    <?php endif; ?>
                </div>
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





    <div class="modal fade project-photo-modal" id="projectPhotoModal" tabindex="-1" aria-labelledby="projectPhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectPhotoModalLabel">Foto do projeto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <img id="projectPhotoModalImage" src="" alt="Foto ampliada do projeto">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-success" id="btnMaisFotosModal">Mais fotos</button>
                </div>
            </div>
        </div>
    </div>

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
                    <input type="hidden" id="idReciboCert" name="id_recibo">
                    <input type="hidden" id="docDestino" name="doc_destino" value="certificado.php">
                    <input type="hidden" id="nascimento" name="nascimento" required>
                    <input type="date" id="nascimentoPicker" class="birthdate-picker-native" tabindex="-1" aria-hidden="true">
                    <label for="nascimentoDisplay" class="form-label">Data de nascimento</label>
                    <div class="birthdate-input-group">
                        <input type="text"
                            class="form-control"
                            id="nascimentoDisplay"
                            inputmode="numeric"
                            autocomplete="bday"
                            placeholder="dd/mm/aaaa"
                            maxlength="10"
                            aria-describedby="textoLiberacaoDocumento"
                            required>
                        <button type="button"
                            class="btn btn-outline-secondary birthdate-calendar-btn"
                            id="btnNascimentoCalendar"
                            aria-label="Abrir calendário">📅</button>
                    </div>
                    <small class="text-muted" id="textoLiberacaoDocumento">Informe sua data para liberar o certificado.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Continuar</button>
                </div>
            </form>
        </div>
    </div>



    <!-- (NOVO) Modal INSCRIÇÃO/RENOVAÇÃO – autocomplete + data de nascimento -->
    <div id="downloadLoadingIndicator" class="download-loading-indicator" aria-hidden="true">
        <div class="download-loading-card">
            <div class="download-loading-spinner" aria-hidden="true"></div>
            <div class="download-loading-text">Baixando...</div>
        </div>
    </div>

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
            COALESCE(rc.recibo_qtd, 0) AS recibo_qtd,
            rc.recibo_ids,
            (SELECT COUNT(*) 
               FROM pend_cad pc 
              WHERE pc.id_colaborador = c.id 
                AND pc.pendencia = 1
            ) AS pendencia
        FROM colaborador c
        JOIN ano_projeto a ON c.id = a.id_colaborador
        LEFT JOIN (
            SELECT
                c2.id AS id_colaborador_ref,
                id_projeto,
                COUNT(*) AS recibo_qtd,
                GROUP_CONCAT(cx.id ORDER BY cx.data ASC, cx.id ASC SEPARATOR ',') AS recibo_ids
            FROM caixa cx
            INNER JOIN colaborador c2 ON (
                (cx.id_colaborador IS NOT NULL AND cx.id_colaborador > 0 AND cx.id_colaborador = c2.id)
                OR (
                    REPLACE(REPLACE(REPLACE(COALESCE(cx.favorecido_documento, ''), '.', ''), '-', ''), '/', '') <> ''
                    AND REPLACE(REPLACE(REPLACE(COALESCE(c2.cpf, ''), '.', ''), '-', ''), '/', '') =
                        REPLACE(REPLACE(REPLACE(COALESCE(cx.favorecido_documento, ''), '.', ''), '-', ''), '/', '')
                )
            )
            GROUP BY c2.id, cx.id_projeto
        ) rc ON rc.id_colaborador_ref = c.id AND rc.id_projeto = a.id_projeto
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
            COALESCE(rc.recibo_qtd, 0) AS recibo_qtd,
            rc.recibo_ids,
            (SELECT COUNT(*) 
               FROM pend_cad pc 
              WHERE pc.id_colaborador = c.id 
                AND pc.pendencia = 1
            ) AS pendencia
        FROM colaborador c
        JOIN ano_projeto a ON c.id = a.id_colaborador
        LEFT JOIN (
            SELECT
                c2.id AS id_colaborador_ref,
                cx.id_projeto,
                COUNT(*) AS recibo_qtd,
                GROUP_CONCAT(cx.id ORDER BY cx.data ASC, cx.id ASC SEPARATOR ',') AS recibo_ids
            FROM caixa cx
            INNER JOIN colaborador c2 ON (
                (cx.id_colaborador IS NOT NULL AND cx.id_colaborador > 0 AND cx.id_colaborador = c2.id)
                OR (
                    REPLACE(REPLACE(REPLACE(COALESCE(cx.favorecido_documento, ''), '.', ''), '-', ''), '/', '') <> ''
                    AND REPLACE(REPLACE(REPLACE(COALESCE(c2.cpf, ''), '.', ''), '-', ''), '/', '') =
                        REPLACE(REPLACE(REPLACE(COALESCE(cx.favorecido_documento, ''), '.', ''), '-', ''), '/', '')
                )
            )
            GROUP BY c2.id, cx.id_projeto
        ) rc ON rc.id_colaborador_ref = c.id AND rc.id_projeto = a.id_projeto
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
  data-destino='certificado.php'
  data-titulo='Confirmar data de nascimento'
  data-texto='Informe sua data para liberar o certificado.'
  data-nome=\"" . htmlspecialchars($campo['nome'], ENT_QUOTES) . "\"
  data-bs-toggle='modal' data-bs-target='#modalCertificado'>Certificado</button>";

                        $retorno .= " <button type='button' class='btn btn-outline-info btn-sm btn-certificado'
  data-idcol='" . $campo['id'] . "'
  data-idproj='" . (int)$id_projeto . "'
  data-destino='declaracao.php'
  data-titulo='Confirmar data de nascimento'
  data-texto='Informe sua data para liberar a declaração.'
  data-nome=\"" . htmlspecialchars($campo['nome'], ENT_QUOTES) . "\"
  data-bs-toggle='modal' data-bs-target='#modalCertificado'>Declaração</button>";

                        $reciboIds = array_values(array_filter(array_map('intval', explode(',', (string) ($campo['recibo_ids'] ?? '')))));
                        if (!empty($reciboIds)) {
                            $primeiroReciboId = (int) $reciboIds[0];
                            $primeiroRotuloRecibo = count($reciboIds) > 1 ? 'Recibo 1' : 'Recibo';
                        $retorno .= " <button type='button' class='btn btn-outline-success btn-sm btn-certificado'
  data-idcol='" . $campo['id'] . "'
  data-idproj='" . (int)$id_projeto . "'
  data-idrecibo='" . $primeiroReciboId . "'
  data-destino='recibo_digital.php'
  data-titulo='Confirmar data de nascimento'
  data-texto='Você vai receber o link do " . $primeiroRotuloRecibo . " no email cadastrado'
  data-nome=\"" . htmlspecialchars($campo['nome'], ENT_QUOTES) . "\"
  data-modo='email'
  data-bs-toggle='modal' data-bs-target='#modalCertificado'>" . $primeiroRotuloRecibo . "</button>";
                            if (count($reciboIds) > 1) {
                                foreach (array_slice($reciboIds, 1) as $indiceExtra => $idReciboExtra) {
                                    $rotuloReciboExtra = 'Recibo ' . ($indiceExtra + 2);
                                    $retorno .= " <button type='button' class='btn btn-outline-success btn-sm btn-certificado'
  data-idcol='" . $campo['id'] . "'
  data-idproj='" . (int)$id_projeto . "'
  data-idrecibo='" . (int) $idReciboExtra . "'
  data-destino='recibo_digital.php'
  data-titulo='Confirmar data de nascimento'
  data-texto='Você vai receber o link do " . $rotuloReciboExtra . " no email cadastrado'
  data-nome=\"" . htmlspecialchars($campo['nome'], ENT_QUOTES) . "\"
  data-modo='email'
  data-bs-toggle='modal' data-bs-target='#modalCertificado'>" . $rotuloReciboExtra . "</button>";
                                }
                            }
                        }
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
                document.addEventListener('DOMContentLoaded', function() {
                    const carousel = document.getElementById('carouselProjetoFotos');
                    if (!carousel) {
                        return;
                    }

                    let fotos = [];
                    try {
                        fotos = JSON.parse(carousel.getAttribute('data-fotos') || '[]');
                    } catch (e) {
                        fotos = [];
                    }

                    const slots = Array.from(carousel.querySelectorAll('[data-slot]'));
                    const modalElement = document.getElementById('projectPhotoModal');
                    const modalImage = document.getElementById('projectPhotoModalImage');
                    const btnMaisFotosModal = document.getElementById('btnMaisFotosModal');
                    const track = carousel.querySelector('.project-carousel-track');
                    const btnPrev = carousel.querySelector('[data-action="prev"]');
                    const btnNext = carousel.querySelector('[data-action="next"]');
                    if (!slots.length || !fotos.length || !track) {
                        return;
                    }

                    let startIndex = 0;
                    let timerId = null;
                    let isAnimating = false;

                    function getVisibleCount() {
                        if (window.innerWidth < 768) {
                            return 1;
                        }
                        if (window.innerWidth < 992) {
                            return 2;
                        }
                        return 3;
                    }

                    function getMaxStart() {
                        return Math.max(0, fotos.length - Math.min(getVisibleCount(), fotos.length));
                    }

                    function syncTrackColumns() {
                        track.style.gridTemplateColumns = 'repeat(' + Math.min(getVisibleCount(), fotos.length) + ', minmax(0, 1fr))';
                    }

                    function renderWindow() {
                        const visibleCount = Math.min(getVisibleCount(), fotos.length);
                        syncTrackColumns();

                        slots.forEach(function(slot, slotIndex) {
                            const frame = slot.parentElement;
                            if (slotIndex < visibleCount) {
                                const foto = fotos[startIndex + slotIndex];
                                slot.src = foto.full;
                                slot.alt = 'Foto do projeto ' + (startIndex + slotIndex + 1);
                                slot.dataset.fallback = foto.thumb;
                                slot.style.display = 'block';
                                if (frame) {
                                    frame.style.display = 'block';
                                    frame.dataset.full = foto.full;
                                    frame.dataset.thumb = foto.thumb;
                                    frame.dataset.index = startIndex + slotIndex + 1;
                                }
                            } else {
                                slot.removeAttribute('src');
                                slot.style.display = 'none';
                                if (frame) {
                                    frame.style.display = 'none';
                                }
                            }
                        });
                    }

                    function normalizeIndex(nextIndex) {
                        const maxStart = getMaxStart();
                        if (nextIndex > maxStart) {
                            return 0;
                        }
                        if (nextIndex < 0) {
                            return maxStart;
                        }
                        return nextIndex;
                    }

                    function finishAnimation(nextIndex, className) {
                        startIndex = normalizeIndex(nextIndex);
                        renderWindow();
                        track.classList.remove(className);
                        isAnimating = false;
                    }

                    function advance(step) {
                        if (isAnimating) {
                            return;
                        }

                        const nextIndex = normalizeIndex(startIndex + step);
                        if (nextIndex === startIndex) {
                            return;
                        }

                        const className = step > 0 ? 'is-sliding-next' : 'is-sliding-prev';
                        isAnimating = true;
                        track.classList.remove('is-sliding-next', 'is-sliding-prev');
                        void track.offsetWidth;
                        track.classList.add(className);

                        window.setTimeout(function() {
                            finishAnimation(nextIndex, className);
                        }, 450);
                    }

                    function startAuto() {
                        const visibleCount = Math.min(getVisibleCount(), fotos.length);
                        if (timerId) {
                            window.clearInterval(timerId);
                        }
                        if (fotos.length <= visibleCount) {
                            return;
                        }
                        timerId = window.setInterval(function() {
                            advance(1);
                        }, 2500);
                    }

                    slots.forEach(function(slot) {
                        slot.addEventListener('error', function() {
                            const fallback = this.dataset.fallback || '';
                            if (fallback && this.src !== fallback) {
                                this.src = fallback;
                            }
                        });
                    });

                    const photoModal = modalElement ? new bootstrap.Modal(modalElement) : null;

                    function openPhotoModal(trigger) {
                        if (!photoModal || !modalImage || !trigger) {
                            return;
                        }
                        modalImage.src = trigger.getAttribute('data-full') || trigger.getAttribute('data-thumb') || '';
                        modalImage.onerror = function() {
                            const fallback = trigger.getAttribute('data-thumb') || '';
                            if (fallback && this.src !== fallback) {
                                this.src = fallback;
                            }
                        };
                        photoModal.show();
                    }

                    document.querySelectorAll('.js-open-photo-modal').forEach(function(button) {
                        button.addEventListener('click', function() {
                            openPhotoModal(this);
                        });
                    });

                    if (btnMaisFotosModal) {
                        btnMaisFotosModal.addEventListener('click', function() {
                            if (photoModal) {
                                photoModal.hide();
                            }
                            const tabFotosButton = document.getElementById('tab-fotos');
                            const paneFotos = document.getElementById('pane-fotos');
                            if (tabFotosButton) {
                                bootstrap.Tab.getOrCreateInstance(tabFotosButton).show();
                            }
                            if (paneFotos) {
                                window.setTimeout(function() {
                                    paneFotos.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'start'
                                    });
                                }, 200);
                            }
                        });
                    }

                    if (btnPrev) {
                        btnPrev.addEventListener('click', function() {
                            advance(-1);
                            startAuto();
                        });
                    }

                    if (btnNext) {
                        btnNext.addEventListener('click', function() {
                            advance(1);
                            startAuto();
                        });
                    }

                    window.addEventListener('resize', function() {
                        startIndex = Math.min(startIndex, getMaxStart());
                        renderWindow();
                        startAuto();
                    });

                    renderWindow();
                    startAuto();
                });
            </script>

            <script>
                // Abre o modal e preenche os IDs do colaborador/projeto
                $(document).on('click', '.btn-certificado', function() {
                    $('#idColabCert').val($(this).data('idcol'));
                    $('#idProjCert').val($(this).data('idproj'));
                    $('#idReciboCert').val($(this).data('idrecibo') || '');
                    $('#docDestino').val($(this).data('destino') || 'certificado.php');
                    $('#modalCertificadoLabel').text($(this).data('titulo') || 'Confirmar data de nascimento');
                    $('#textoLiberacaoDocumento').text($(this).data('texto') || 'Informe sua data para liberar o certificado.');
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
                    const destino = $('#docDestino').val() || 'certificado.php';
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
                            const url = destino + '?id_colaborador=' + encodeURIComponent(id_colaborador) +
                                '&id_projeto=' + encodeURIComponent(id_projeto) +
                                '&nascimento=' + encodeURIComponent($('#nascimento').val());
                            window.open(url, '_blank');
                        } else {
                            alert(resp.msg || 'Data de nascimento não confere.');
                        }
                    }).fail(function() {
                        alert('Falha ao validar a data. Tente novamente.');
                    });

                });
            </script>

            
            <!-- Realização -->
            <script>
                function formatBirthDateDigits(value) {
                    const digits = String(value || '').replace(/\D/g, '').slice(0, 8);
                    if (digits.length <= 2) {
                        return digits;
                    }
                    if (digits.length <= 4) {
                        return digits.slice(0, 2) + '/' + digits.slice(2);
                    }
                    return digits.slice(0, 2) + '/' + digits.slice(2, 4) + '/' + digits.slice(4);
                }

                function isoToDisplayDate(isoValue) {
                    const match = String(isoValue || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
                    if (!match) {
                        return '';
                    }
                    return match[3] + '/' + match[2] + '/' + match[1];
                }

                function displayToIsoDate(displayValue) {
                    const match = String(displayValue || '').match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
                    if (!match) {
                        return '';
                    }

                    const day = Number(match[1]);
                    const month = Number(match[2]);
                    const year = Number(match[3]);
                    const date = new Date(year, month - 1, day);

                    if (
                        Number.isNaN(date.getTime()) ||
                        date.getFullYear() !== year ||
                        date.getMonth() !== month - 1 ||
                        date.getDate() !== day
                    ) {
                        return '';
                    }

                    return String(year).padStart(4, '0') + '-' +
                        String(month).padStart(2, '0') + '-' +
                        String(day).padStart(2, '0');
                }

                function downloadDocument(url) {
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = '';
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                }

                let downloadLoadingTimerId = null;

                function showDownloadLoadingIndicator() {
                    const indicator = document.getElementById('downloadLoadingIndicator');
                    if (!indicator) {
                        return;
                    }

                    indicator.classList.add('is-visible');
                    if (downloadLoadingTimerId) {
                        window.clearTimeout(downloadLoadingTimerId);
                    }
                    downloadLoadingTimerId = window.setTimeout(function() {
                        indicator.classList.remove('is-visible');
                        downloadLoadingTimerId = null;
                    }, 5000);
                }

                function hideDownloadLoadingIndicator() {
                    const indicator = document.getElementById('downloadLoadingIndicator');
                    if (!indicator) {
                        return;
                    }
                    if (downloadLoadingTimerId) {
                        window.clearTimeout(downloadLoadingTimerId);
                        downloadLoadingTimerId = null;
                    }
                    indicator.classList.remove('is-visible');
                }

                (function setupBirthDateModal() {
                    const modalElement = document.getElementById('modalCertificado');
                    const displayInput = document.getElementById('nascimentoDisplay');
                    const hiddenInput = document.getElementById('nascimento');
                    const nativePicker = document.getElementById('nascimentoPicker');
                    const calendarButton = document.getElementById('btnNascimentoCalendar');

                    if (!modalElement || !displayInput || !hiddenInput || !nativePicker || !calendarButton) {
                        return;
                    }

                    if (modalElement.parentElement !== document.body) {
                        document.body.appendChild(modalElement);
                    }

                    function syncFromDisplay() {
                        displayInput.value = formatBirthDateDigits(displayInput.value);
                        const isoValue = displayToIsoDate(displayInput.value);
                        hiddenInput.value = isoValue;
                        nativePicker.value = isoValue;
                        if (isoValue) {
                            displayInput.classList.remove('border-danger');
                        }
                    }

                    function resetBirthDateFields() {
                        displayInput.value = '';
                        hiddenInput.value = '';
                        nativePicker.value = '';
                        displayInput.classList.remove('border-danger');
                    }

                    function closeBirthDateModal() {
                        const modalInstance = bootstrap.Modal.getInstance(modalElement) || bootstrap.Modal.getOrCreateInstance(modalElement);
                        if (modalInstance) {
                            modalInstance.hide();
                        }

                        document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
                            backdrop.remove();
                        });

                        document.body.classList.remove('modal-open');
                        document.body.style.removeProperty('padding-right');
                        document.body.style.removeProperty('overflow');
                    }

                    displayInput.addEventListener('input', syncFromDisplay);
                    displayInput.addEventListener('blur', syncFromDisplay);

                    nativePicker.addEventListener('change', function() {
                        hiddenInput.value = this.value || '';
                        displayInput.value = isoToDisplayDate(this.value);
                        if (this.value) {
                            displayInput.classList.remove('border-danger');
                        }
                    });

                    calendarButton.addEventListener('click', function() {
                        if (typeof nativePicker.showPicker === 'function') {
                            nativePicker.showPicker();
                            return;
                        }
                        nativePicker.focus();
                        nativePicker.click();
                    });

                    $(document).on('click', '.btn-certificado', function() {
                        resetBirthDateFields();
                        $('#formCertificado').data('modo', $(this).data('modo') || 'download');
                    });

                    $('#formCertificado').off('submit').on('submit', function(e) {
                        e.preventDefault();

                        const id_colaborador = $('#idColabCert').val();
                        const id_projeto = $('#idProjCert').val();
                        const id_recibo = $('#idReciboCert').val();
                        const destino = $('#docDestino').val() || 'certificado.php';
                        const modo = $(this).data('modo') || 'download';
                        const nascimentoConvertido = displayToIsoDate((displayInput.value || '').trim());

                        hiddenInput.value = nascimentoConvertido;
                        nativePicker.value = nascimentoConvertido;

                        if (!nascimentoConvertido) {
                            alert('Informe a data de nascimento.');
                            displayInput.classList.add('border-danger');
                            displayInput.focus();
                            return;
                        }

                        showDownloadLoadingIndicator();

                        if (modo === 'email' || destino === 'recibo_digital.php') {
                            $.ajax({
                                url: 'recibo_digital.php?action=solicitarAcesso',
                                method: 'POST',
                                dataType: 'json',
                                data: {
                                    id_colaborador: id_colaborador,
                                    id_projeto: id_projeto,
                                    id_recibo: id_recibo,
                                    nascimento: nascimentoConvertido
                                }
                            }).done(function(resp) {
                                hideDownloadLoadingIndicator();
                                if (resp.ok) {
                                    const emailInfo = resp.email ? '\n\nE-mail de destino: ' + resp.email : '';
                                    alert((resp.msg || 'Enviamos o link do recibo digital para o e-mail cadastrado.') + emailInfo);
                                    closeBirthDateModal();
                                    resetBirthDateFields();
                                } else {
                                    displayInput.classList.add('border-danger');
                                    alert(resp.msg || 'Nao foi possivel enviar o recibo digital.');
                                }
                            }).fail(function(xhr) {
                                hideDownloadLoadingIndicator();
                                displayInput.classList.add('border-danger');
                                const fallbackMsg = xhr && xhr.responseJSON && xhr.responseJSON.msg
                                    ? xhr.responseJSON.msg
                                    : 'Falha ao validar e enviar o recibo digital. Tente novamente.';
                                alert(fallbackMsg);
                            });
                            return;
                        }

                        $.getJSON('certificado.php', {
                            action: 'confirmaNascimento',
                            id_colaborador: id_colaborador,
                            id_projeto: id_projeto,
                            nascimento: nascimentoConvertido
                        }).done(function(resp) {
                            if (resp.ok) {
                                const url = destino + '?id_colaborador=' + encodeURIComponent(id_colaborador) +
                                    '&id_projeto=' + encodeURIComponent(id_projeto) +
                                    '&nascimento=' + encodeURIComponent(nascimentoConvertido);
                                downloadDocument(url);
                            } else {
                                hideDownloadLoadingIndicator();
                                displayInput.classList.add('border-danger');
                                alert(resp.msg || 'Data de nascimento nÃ£o confere.');
                            }
                        }).fail(function() {
                            hideDownloadLoadingIndicator();
                            alert('Falha ao validar a data. Tente novamente.');
                        });
                    });
                })();
            </script>

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
