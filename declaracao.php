<?php
/*
 * declaracao.php
 * Gera uma declaração em PDF para um colaborador de um projeto.
 * Requer: id_colaborador e id_projeto (GET).
 */

ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config/conexao.class.php';
require_once __DIR__ . '/config/crud.class.php';

$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

if (!class_exists('\Dompdf\Dompdf')) {
    $legacyAutoloads = [
        __DIR__ . '/vendor/dompdf/dompdf/autoload.inc.php',
        __DIR__ . '/dompdf/autoload.inc.php',
    ];

    foreach ($legacyAutoloads as $legacyAutoload) {
        if (file_exists($legacyAutoload)) {
            require_once $legacyAutoload;
            break;
        }
    }
}

use Dompdf\Dompdf;
use Dompdf\Options;

function http_abort($code, $msg)
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $msg;
    exit;
}

function h($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function data_extenso_pt_br($dateStr = 'now')
{
    $timestamp = is_numeric($dateStr) ? (int) $dateStr : strtotime((string) $dateStr);
    if ($timestamp === false) {
        $timestamp = time();
    }

    static $meses = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'marco',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro',
    ];

    return date('d', $timestamp) . ' de ' . $meses[(int) date('n', $timestamp)] . ' de ' . date('Y', $timestamp);
}

function gerarQRCode($texto)
{
    try {
        if (class_exists('\Endroid\QrCode\QrCode')) {
            $qrCode = new \Endroid\QrCode\QrCode($texto);
            $qrCode->setSize(250);
            $qrCode->setMargin(10);
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);
            return 'data:image/png;base64,' . base64_encode($result->getString());
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'user_agent' => 'Mozilla/5.0',
                'follow_location' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $qrURL = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($texto);
        $qrImage = @file_get_contents($qrURL, false, $context);
        if ($qrImage !== false && strlen($qrImage) > 100) {
            return 'data:image/png;base64,' . base64_encode($qrImage);
        }

        return null;
    } catch (Exception $e) {
        error_log('Erro ao gerar QR Code da declaracao: ' . $e->getMessage());
        return null;
    }
}

function load_binary_from_candidates(array $candidates)
{
    foreach ($candidates as $candidate) {
        if (!$candidate) {
            continue;
        }

        if (preg_match('#^https?://#i', $candidate)) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 8,
                    'user_agent' => 'Mozilla/5.0',
                    'follow_location' => true,
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
            $data = @file_get_contents($candidate, false, $context);
            if ($data !== false && strlen($data) > 0) {
                return $data;
            }
            continue;
        }

        if (file_exists($candidate) && is_file($candidate)) {
            $data = @file_get_contents($candidate);
            if ($data !== false && strlen($data) > 0) {
                return $data;
            }
        }
    }

    return null;
}

function image_data_uri_from_project_asset($relativeAssetPath)
{
    global $baseDir, $link_imagem_projeto, $path_imagem_projeto;

    $normalized = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativeAssetPath), DIRECTORY_SEPARATOR);
    $relativeUrl = str_replace(DIRECTORY_SEPARATOR, '/', $normalized);

    $candidates = [];

    if (!empty($baseDir)) {
        $candidates[] = rtrim($baseDir, '\\/') . DIRECTORY_SEPARATOR . 'paixaodecristo' . DIRECTORY_SEPARATOR . 'projeto' . DIRECTORY_SEPARATOR . $normalized;
    }

    if (!empty($path_imagem_projeto)) {
        $uploadBase = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path_imagem_projeto);
        $projectRoot = dirname(rtrim($uploadBase, '\\/'));
        $candidates[] = $projectRoot . DIRECTORY_SEPARATOR . $normalized;
    }

    if (!empty($link_imagem_projeto)) {
        $imageBaseUrl = rtrim(str_replace('/upload_pic/', '/images/', $link_imagem_projeto), '/');
        $candidates[] = $imageBaseUrl . '/' . $relativeUrl;
    }

    $binary = load_binary_from_candidates(array_unique($candidates));
    if ($binary === null) {
        return null;
    }

    $ext = strtolower(pathinfo($relativeUrl, PATHINFO_EXTENSION));
    $mimeMap = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
    ];
    $mime = $mimeMap[$ext] ?? 'application/octet-stream';

    return 'data:' . $mime . ';base64,' . base64_encode($binary);
}

function image_data_uri_from_local_asset($relativeAssetPath)
{
    $fullPath = __DIR__ . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativeAssetPath), DIRECTORY_SEPARATOR);
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        return null;
    }

    $binary = @file_get_contents($fullPath);
    if ($binary === false || strlen($binary) === 0) {
        return null;
    }

    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $mimeMap = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
    ];
    $mime = $mimeMap[$ext] ?? 'application/octet-stream';

    return 'data:' . $mime . ';base64,' . base64_encode($binary);
}

function normalize_text_for_match($text)
{
    $text = mb_strtolower((string) $text, 'UTF-8');
    $map = [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
        'é' => 'e', 'ê' => 'e',
        'í' => 'i',
        'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ú' => 'u',
        'ç' => 'c',
    ];

    return strtr($text, $map);
}

function obter_duracao_contrato($papel)
{
    $papelNormalizado = normalize_text_for_match($papel);

    if (in_array($papelNormalizado, ['direcao', 'assistente em direcao', 'direcao secundaria'], true)) {
        return 60;
    }

    if ($papelNormalizado === 'producao') {
        return 30;
    }

    return 20;
}

$id_colaborador = filter_input(INPUT_GET, 'id_colaborador', FILTER_VALIDATE_INT);
$id_projeto = filter_input(INPUT_GET, 'id_projeto', FILTER_VALIDATE_INT);
$nascimentoGET = isset($_GET['nascimento']) ? trim($_GET['nascimento']) : null;

if (!$id_colaborador || !$id_projeto) {
    http_abort(400, 'Parametros obrigatorios ausentes. Use: declaracao.php?id_colaborador={id}&id_projeto={id}');
}

$con = new conexao();
$link = $con->connect();
if (!$link) {
    http_abort(500, 'Erro ao conectar ao banco de dados.');
}

$sql = "
    SELECT
        c.id AS id_colab,
        c.nome AS nome_colab,
        c.nascimento AS nascimento_colab,
        p.id AS id_proj,
        p.nome AS nome_proj,
        p.categoria AS categoria_proj,
        p.anoprojeto AS ano_proj,
        ap.papel1 AS papel,
        ap.papel_detalhe AS papel_detalhe,
        ap.situacao AS situacao
    FROM colaborador c
    INNER JOIN ano_projeto ap ON ap.id_colaborador = c.id
    INNER JOIN projetos p ON p.id = ap.id_projeto
    WHERE c.id = ? AND p.id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($link, $sql);
if (!$stmt) {
    http_abort(500, 'Falha ao preparar consulta.');
}

mysqli_stmt_bind_param($stmt, 'ii', $id_colaborador, $id_projeto);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    http_abort(404, 'Vinculo nao encontrado: colaborador nao associado a este projeto.');
}

$dados = mysqli_fetch_assoc($result);

if (!empty($dados['situacao']) && strtoupper($dados['situacao']) === 'PENDENTE') {
    http_abort(403, 'Declaracao indisponivel: vinculo ainda pendente.');
}

if ($nascimentoGET) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $nascimentoGET)) {
        http_abort(400, 'Parametro "nascimento" invalido. Formato: YYYY-MM-DD.');
    }
    $nascDB = substr((string) $dados['nascimento_colab'], 0, 10);
    if ($nascDB !== $nascimentoGET) {
        http_abort(403, 'Data de nascimento nao confere.');
    }
}

$nomeColab = mb_strtoupper($dados['nome_colab'] ?? '', 'UTF-8');
$nomeProj = $dados['nome_proj'] ?? 'Paixão de Cristo de Igarassu';
$categoria = $dados['categoria_proj'] ?? 'Teatro';
$anoProj = $dados['ano_proj'] ?? date('Y');
$papelBase = trim((string) ($dados['papel_detalhe'] ?? '')) !== '' ? (string) $dados['papel_detalhe'] : (string) ($dados['papel'] ?? '');
$papel = $papelBase !== '' ? mb_strtoupper($papelBase, 'UTF-8') : 'COLABORADOR(A)';
$duracaoContratoDias = obter_duracao_contrato($papelBase);
$dataHojeBR = data_extenso_pt_br();
$presidente = 'TIAGO SEVERINO ANTONIO JUNIOR';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'azerutan.ki6.com.br';
$linkDeclaracao = $scheme . '://' . $host . '/declaracao.php?id_colaborador=' . $id_colaborador . '&id_projeto=' . $id_projeto;
$qrSrc = gerarQRCode($linkDeclaracao);
$logoSrc = image_data_uri_from_project_asset('images/AssocAzerutan.png');
$bgSrc = image_data_uri_from_project_asset('images/azerutanFundo.jpg');
$assinaturaSrc = image_data_uri_from_local_asset('images/assinatura_tiago_estilizada.png');

$qrcodeHtml = '';
if (!empty($qrSrc)) {
    $qrcodeHtml = '<div class="qr-wrap"><img class="qrcode" src="' . $qrSrc . '" alt="QR Code"><div class="qr-text">Leia o QR Code para validar este documento.</div></div>';
}

$logoHtml = '';
if (!empty($logoSrc)) {
    $logoHtml = '<img class="logo" src="' . $logoSrc . '" alt="Associacao Cultural Azerutan">';
}

$bgHtml = '';
if (!empty($bgSrc)) {
    $bgHtml = '<img class="bg-art" src="' . $bgSrc . '" alt="">';
}

$assinaturaHtml = '';
if (!empty($assinaturaSrc)) {
    $assinaturaHtml = '<img class="assinatura-img" src="' . $assinaturaSrc . '" alt="Assinatura Tiago">';
}

$htmlNomeColab = h($nomeColab);
$htmlNomeProj = h($nomeProj);
$htmlCategoria = h($categoria);
$htmlAnoProj = h($anoProj);
$htmlPapel = h($papel);
$htmlDuracaoContratoDias = h((string) $duracaoContratoDias);
$htmlDataHoje = h($dataHojeBR);
$htmlPresidente = h($presidente);

$css = <<<CSS
@page {
    margin: 12mm 14mm 12mm 14mm;
    size: A4 portrait;
}
body {
    font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
    color: #000;
    font-size: 13px;
    line-height: 1.45;
    margin: 0;
    padding: 0;
}
.doc {
    position: relative;
    min-height: 100%;
}
.bg-art {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.18;
    z-index: 1;
}
.content {
    position: relative;
    z-index: 2;
    width: 550px;
    margin: 0 auto;
}
.topo {
    text-align: center;
    margin-bottom: 8mm;
}
.logo {
    width: 270px;
    height: auto;
    margin-bottom: 2mm;
}
.titulo {
    font-size: 17px;
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 8mm;
}
.texto {
    text-align: justify;
    margin-bottom: 10mm;
}
.texto p {
    margin: 0 0 5mm 0;
}
.assinatura {
    margin-top: 14mm;
    text-align: center;
}
.assinatura-img {
    width: 140px;
    height: auto;
    display: block;
    margin: 0 auto 2mm auto;
}
.cargo {
    font-size: 12px;
}
.rodape {
    margin-top: 10mm;
    font-size: 11px;
    text-align: center;
}
.qr-wrap {
    position: absolute;
    right: 10mm;
    bottom: 8mm;
    width: 90px;
    text-align: center;
    z-index: 3;
}
.qrcode {
    width: 80px;
    height: 80px;
}
.qr-text {
    margin-top: 2mm;
    font-size: 8px;
    line-height: 1.3;
}
CSS;

$html = <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<style>{$css}</style>
</head>
<body>
    <div class="doc">
        {$bgHtml}
        <div class="content">
            <div class="topo">
                {$logoHtml}
                <div class="titulo">DECLARAÇÃO</div>
            </div>

            <div class="texto">
                <p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;A ASSOCIAÇÃO CULTURAL AZERUTAN, inscrita no CNPJ 53.849.215/0001-48, com sede em Igarassu - PE, por meio de seu presidente <strong>{$htmlPresidente}</strong>, declara, para os devidos fins, que <strong>{$htmlNomeColab}</strong> participou do projeto <strong>{$htmlNomeProj}</strong>, exercendo a função de <strong>{$htmlPapel}</strong>, vinculado a <strong>{$htmlCategoria}</strong>, no ano de <strong>{$htmlAnoProj}</strong>, com contrato de <strong>{$htmlDuracaoContratoDias} dias</strong>.</p>

                <p>A presente declaração é emitida a pedido do(a) interessado(a), para comprovar sua participação nas atividades artísticas, culturais e organizacionais da <strong>Paixão de Cristo de Igarassu</strong>, realizada pela Associação Cultural Azerutan.</p>

                <p>Por ser verdade, firmamos o presente documento para que produza os efeitos cabíveis.</p>
            </div>

            <div class="assinatura">
                <div>Igarassu - PE, {$htmlDataHoje}</div>
                <div style="height:14mm"></div>
                {$assinaturaHtml}
                <div><strong>{$htmlPresidente}</strong></div>
                <div class="cargo">Presidente da Associação Cultural Azerutan</div>
            </div>

            <div class="rodape">
                <strong>Rua Guiana Francesa, Agamenon Magalhães - Igarassu - PE.</strong><br>
                <strong>Fone (81) 99194-2138 - CNPJ: 53.849.215/0001-48</strong>
            </div>
        </div>
        {$qrcodeHtml}
    </div>
</body>
</html>
HTML;

if (!class_exists('\Dompdf\Dompdf')) {
    http_abort(500, 'Biblioteca Dompdf nao encontrada no servidor. Publique a pasta vendor ou rode o Composer em producao.');
}

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$nomeArquivo = 'DECLARAÇÃO_' . preg_replace('/\s+/', '_', $nomeColab) . '.pdf';

while (ob_get_level()) {
    ob_end_clean();
}
header('Content-Type: application/pdf; charset=utf-8');
header('Content-Disposition: inline; filename="' . $nomeArquivo . '"');
echo $dompdf->output();
exit;
