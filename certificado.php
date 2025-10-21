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

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function data_br($dateStr) {
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

$css = <<<CSS
    @page { margin: 30px; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#1b1f23; }
    .wrap { width: 100%; padding: 24px 28px; box-sizing: border-box; }
    .box {
        border: 4px solid #20b2aa;
        border-radius: 16px;
        padding: 28px 32px;
    }
    .titulo {
        text-align: center;
        font-size: 28px;
        margin: 0 0 6px 0;
        letter-spacing: .4px;
        color:#17928b;
        font-weight: 700;
    }
    .sub { text-align: center; font-size: 14px; color:#6c757d; margin-bottom: 24px; }
    .nome {
        text-align: center;
        font-size: 36px;
        margin: 10px 0 4px 0;
        font-weight: 800;
    }
    .linha { text-align: center; font-size: 16px; margin: 10px 0; }
    .destaque { font-weight: 700; }
    .rodape {
        margin-top: 36px;
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color:#6c757d;
    }
    .assinaturas {
        margin-top: 46px;
        display: flex;
        justify-content: space-around;
        text-align: center;
        font-size: 12px;
    }
    .ass {
        width: 40%;
        border-top: 1px solid #aaa;
        padding-top: 6px;
    }
    .branding {
        position: absolute;
        right: 28px;
        top: 22px;
        font-size: 11px;
        color:#6c757d;
    }
CSS;

$html = <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<meta charset="utf-8">
<style>{$css}</style>
<body>
<div class="wrap">
    <div class="branding">Emitido em {$dataHojeBR}</div>
    <div class="box">
        <h1 class="titulo">CERTIFICADO DE PARTICIPAÇÃO</h1>
        <div class="sub">Formação/Projeto Cultural — {$anoProj}</div>

        <p class="linha">Certificamos que</p>
        <div class="nome">{$nomeColab}</div>
        <p class="linha">nascido(a) em <span class="destaque">{$nascBR}</span>, participou do projeto</p>
        <p class="linha"><span class="destaque">{$nomeProj}</span> <small>({$categoria})</small></p>
HTML;

if (!empty($papel)) {
    $html .= "<p class=\"linha\">atuação/ função: <span class=\"destaque\">{$papel}</span></p>";
}

$html .= <<<HTML
        <div class="assinaturas">
            <div class="ass">
                Associação Cultural Azerutan<br>
                Presidência
            </div>
            <div class="ass">
                Coordenação do Projeto<br>
                {$nomeProj}
            </div>
        </div>

        <div class="rodape">
            <div>Associação Cultural Azerutan</div>
            <div>CNPJ: 53.849.215/0001-48</div>
            <div>Igarassu - PE</div>
        </div>
    </div>
</div>
</body>
</html>
HTML;

/* ---------- Renderiza PDF (paisagem) ---------- */

$options = new Options();
$options->set('isRemoteEnabled', true);   // se for usar imagens externas
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');     // paisagem
$dompdf->render();

$nomeArquivo = 'CERTIFICADO_' . preg_replace('/\s+/', '_', $nomeColab) . '.pdf';

// Limpa qualquer buffer e envia o PDF inline
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/pdf; charset=utf-8');
header('Content-Disposition: inline; filename="'.$nomeArquivo.'"');
echo $dompdf->output();
exit;
