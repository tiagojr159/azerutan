<?php
/*
 * certificado.php
 * Gera o certificado em PDF (paisagem) para um colaborador de um projeto.
 * Requer: id_colaborador e id_projeto (GET).
 * AJAX: action=confirmaNascimento (id_colaborador, id_projeto, nascimento)
 */

ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// REMOVIDO: ini_set('mbstring.internal_encoding', 'UTF-8');  // (deprecated em PHP 7+)
setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR', 'pt_BR.utf8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/config/conexao.class.php';
require_once __DIR__ . '/config/crud.class.php';

// Dompdf (ajuste o caminho do autoload conforme seu projeto)
require_once __DIR__ . '/vendor/autoload.php';


/* ---------- Gera QR Code em Base64 ---------- */
/* ---------- Gera QR Code embutido ---------- */
$linkCertificado = "http://azerutan.ki6.com.br/certificado.php?id_colaborador={$id_colaborador}&id_projeto={$id_projeto}";
$qrURL = "https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=" . urlencode($linkCertificado) . "&choe=UTF-8";

// Baixa a imagem do QR Code e converte para Base64
$qrImage = @file_get_contents($qrURL);
if ($qrImage !== false) {
    $qrBase64 = base64_encode($qrImage);
    $qrSrc = "data:image/png;base64,{$qrBase64}";
} else {
    $qrSrc = null;
}


use Dompdf\Dompdf;
use Dompdf\Options;

/* ---------- Funções utilitárias ---------- */

function http_abort($code, $msg)
{
    // Garante que nada ficou no buffer
    while (ob_get_level()) ob_end_clean();
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $msg;
    exit;
}

function h($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function data_br($dateStr)
{
    if (!$dateStr) return '';
    $ts = strtotime($dateStr);
    if ($ts === false) return '';
    return date('d/m/Y', $ts);
}

/* ============================================================
   BLOCO AJAX: validação de nascimento (JSON limpo)
   ============================================================ */
if (isset($_GET['action']) && $_GET['action'] === 'confirmaNascimento') {
    // Não permita nada no output antes do JSON
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');

    $id_colaborador = filter_input(INPUT_GET, 'id_colaborador', FILTER_VALIDATE_INT);
    $id_projeto     = filter_input(INPUT_GET, 'id_projeto', FILTER_VALIDATE_INT);
    $nascimento     = $_GET['nascimento'] ?? '';

    $out = ['ok' => false, 'msg' => 'Parâmetros inválidos.'];
    if ($id_colaborador && $id_projeto && preg_match('/^\d{4}-\d{2}-\d{2}$/', $nascimento)) {
        $con  = new conexao();
        $link = $con->connect();

        if ($link) {
            // Confere se o colaborador pertence ao projeto E se a data bate
            $sql = "
                SELECT 1
                FROM colaborador c
                JOIN ano_projeto ap ON ap.id_colaborador = c.id
                WHERE c.id = ? AND ap.id_projeto = ? AND DATE(c.nascimento) = ?
                LIMIT 1
            ";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, 'iis', $id_colaborador, $id_projeto, $nascimento);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);

                if ($res && mysqli_num_rows($res) > 0) {
                    $out = ['ok' => true];
                } else {
                    $out = ['ok' => false, 'msg' => 'Data de nascimento não confere para este projeto.'];
                }
            } else {
                $out = ['ok' => false, 'msg' => 'Falha ao preparar consulta.'];
            }
        } else {
            $out = ['ok' => false, 'msg' => 'Erro de conexão ao banco.'];
        }
    }

    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit; // IMPORTANTÍSSIMO: não renderiza o PDF nesse fluxo
}

/* ============================================================
   FLUXO PDF (sem action=confirmaNascimento)
   ============================================================ */

/* ---------- Entrada (GET) ---------- */
$id_colaborador = filter_input(INPUT_GET, 'id_colaborador', FILTER_VALIDATE_INT);
$id_projeto     = filter_input(INPUT_GET, 'id_projeto', FILTER_VALIDATE_INT);
// nascimento é opcional no fluxo do PDF; se vier e não bater, bloqueia
$nascimentoGET  = isset($_GET['nascimento']) ? trim($_GET['nascimento']) : null;

if (!$id_colaborador || !$id_projeto) {
    http_abort(400, "Parâmetros obrigatórios ausentes. Use: certificado.php?id_colaborador={id}&id_projeto={id}");
}

/* ---------- Conexão ---------- */
$con = new conexao();
$link = $con->connect();
if (!$link) {
    http_abort(500, "Erro ao conectar ao banco de dados.");
}

/* ---------- Consulta: valida vínculo colaborador/projeto ---------- */
$sql = "
    SELECT 
        c.id          AS id_colab,
        c.nome        AS nome_colab,
        c.nascimento  AS nascimento_colab,
        p.id          AS id_proj,
        p.nome        AS nome_proj,
        p.categoria   AS categoria_proj,
        p.anoprojeto  AS ano_proj,
        ap.papel1     AS papel,
        ap.situacao   AS situacao
    FROM colaborador c
    INNER JOIN ano_projeto ap ON ap.id_colaborador = c.id
    INNER JOIN projetos p     ON p.id = ap.id_projeto
    WHERE c.id = ? AND p.id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($link, $sql);
if (!$stmt) {
    http_abort(500, "Falha ao preparar consulta.");
}
mysqli_stmt_bind_param($stmt, 'ii', $id_colaborador, $id_projeto);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    http_abort(404, "Vínculo não encontrado: colaborador não associado a este projeto.");
}

$dados = mysqli_fetch_assoc($result);

/* ---------- Regra opcional: bloquear pendente ---------- */
if (!empty($dados['situacao']) && strtoupper($dados['situacao']) === 'PENDENTE') {
    http_abort(403, "Certificado indisponível: vínculo ainda pendente.");
}

/* ---------- Se veio nascimento no GET, confira ---------- */
if ($nascimentoGET) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $nascimentoGET)) {
        http_abort(400, 'Parâmetro "nascimento" inválido. Formato: YYYY-MM-DD.');
    }
    $nascDB = substr((string)$dados['nascimento_colab'], 0, 10);
    if ($nascDB !== $nascimentoGET) {
        http_abort(403, 'Data de nascimento não confere.');
    }
}

/* ---------- Monta HTML do certificado ---------- */

$nomeColab   = mb_strtoupper($dados['nome_colab'] ?? '', 'UTF-8');
$nomeProj    = $dados['nome_proj'] ?? '';
$categoria   = $dados['categoria_proj'] ?? '';
$anoProj     = $dados['ano_proj'] ?? date('Y');
$papel       = !empty($dados['papel']) ? mb_strtoupper($dados['papel'], 'UTF-8') : '';
$nascBR      = data_br($dados['nascimento_colab'] ?? null);
$dataHojeBR  = strftime('%d de %B de %Y');

// Caminho do plano de fundo
$bgPath = __DIR__ . '/backup/certificado.png';
if (!file_exists($bgPath)) {
    http_abort(500, "Imagem de fundo não encontrada: backup/certificado.png");
}
$bgBase64 = base64_encode(file_get_contents($bgPath));
$bgMime = mime_content_type($bgPath);

$css = <<<CSS
    @page { margin: 0; }
   body {
    margin: 0;
    padding: 0;
    background-image: url('data:$bgMime;base64,$bgBase64');
    background-size: cover;
    background-position: center;
    font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
    color:#000;
}

.wrap {
    position: relative;
    width: 105%;
    height: 100%;
    text-align: center;
}

/* Nome do participante */
.projeto {
    position: absolute;
    top: 38%;        /* alinhado exatamente entre as linhas douradas */
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 34px;
    font-weight: 800;
    letter-spacing: 1px;
}

/* Texto de participação e detalhes */
.detalhes {
    position: absolute;
    top: 56%;        /* linha dos detalhes sobre as faixas douradas inferiores */
    left: 40%;
    transform: translate(-40%, -50%);
    font-size: 15px;
    line-height: 1.3;
}

/* Campo da assinatura e informações institucionais */
.assinatura {
    position: absolute;
    bottom: 20%;     /* fica encaixado logo acima da linha da máscara direita */
    left: 38%;
    text-align: center;
    font-size: 11.5px;
    line-height: 1.1;
}

.qrcode {
    position: absolute;
    bottom: 20%;
    right: 65%;
    width: 120px;
    height: 120px;
}
CSS;

$html = <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<meta charset="utf-8">
<style>{$css}</style>
<body>
<div class="wrap">
    <div class="projeto">{$nomeProj}</div>
    <div class="detalhes">

A Associação Cultural AZERUTAN declara, para os devidos fins, que <b>{$nomeColab}</B> participou do Projeto <b>{$nomeProj}</b>, desenvolvido por esta instituição, exercendo a função de <b>{$papel}</b>, contribuindo com empenho e dedicação nas ações de formação e difusão cultural promovidas pela associação.
<br>Este certificado é concedido em reconhecimento à sua participação e colaboração nas atividades artísticas e educativas do referido projeto.


    </div>
    <div class="assinatura">
<b>{$categoria} - {$papel} - {$anoProj}</b><br>

        Emitido em {$dataHojeBR}<br>
        Associação Cultural Azerutan<br>
        CNPJ: 53.849.215/0001-48<br>
        Igarassu - PE

    </div>
    
    <?php if (!empty($qrSrc)): ?>
    <img class="qrcode" src="<?= $qrSrc ?>" alt="QR Code de validação">
    <small style="position:absolute; bottom:9%; right:7%; font-size:10px;">Escaneie o QR Code para abrir o certificado</small>
<?php else: ?>
    <small style="position:absolute; bottom:9%; right:7%; font-size:10px; color:red;">QR Code indisponível</small>
<?php endif; ?>
    <img class="qrcode" src="data:image/png;base64,{$qrBase64}" alt="QR Code de validação">
    
</div>
</body>
</html>
HTML;

/* ---------- Renderiza PDF (paisagem) ---------- */

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');     // paisagem
$dompdf->render();

$nomeArquivo = 'CERTIFICADO_' . preg_replace('/\s+/', '_', $nomeColab) . '.pdf';

// Limpa qualquer buffer e envia o PDF inline
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/pdf; charset=utf-8');
header('Content-Disposition: inline; filename="' . $nomeArquivo . '"');
echo $dompdf->output();
exit;
