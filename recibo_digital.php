<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/config/conexao.class.php';
require_once __DIR__ . '/config.php';

function recibo_h($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function recibo_json($payload, $code = 200)
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function recibo_abort($code, $message)
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

function recibo_base_url()
{
    global $azerutan_base_url;

    $configured = trim((string) ($azerutan_base_url ?? ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    return $scheme . '://' . $host . rtrim($dir, '/');
}

function recibo_secret()
{
    global $azerutan_recibo_secret;
    return (string) ($azerutan_recibo_secret ?? 'azerutan_recibo_v1');
}

function recibo_ttl()
{
    global $azerutan_recibo_link_ttl;
    $ttl = (int) ($azerutan_recibo_link_ttl ?? 86400);
    return $ttl > 0 ? $ttl : 86400;
}

function recibo_payload_sign(array $data)
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $signature = hash_hmac('sha256', $json, recibo_secret());
    return rtrim(strtr(base64_encode($json . '|' . $signature), '+/', '-_'), '=');
}

function recibo_payload_verify($token)
{
    $token = trim((string) $token);
    if ($token === '') {
        return null;
    }

    $base64 = strtr($token, '-_', '+/');
    $padding = strlen($base64) % 4;
    if ($padding > 0) {
        $base64 .= str_repeat('=', 4 - $padding);
    }

    $decoded = base64_decode($base64, true);
    if ($decoded === false || strpos($decoded, '|') === false) {
        return null;
    }

    [$json, $signature] = explode('|', $decoded, 2);
    $expected = hash_hmac('sha256', $json, recibo_secret());
    if (!hash_equals($expected, (string) $signature)) {
        return null;
    }

    $payload = json_decode($json, true);
    if (!is_array($payload)) {
        return null;
    }

    $expiresAt = isset($payload['exp']) ? (int) $payload['exp'] : 0;
    if ($expiresAt <= time()) {
        return null;
    }

    return $payload;
}

function recibo_data_br($dateStr)
{
    if (!$dateStr) {
        return '';
    }
    $ts = strtotime((string) $dateStr);
    if ($ts === false) {
        return '';
    }
    return date('d/m/Y', $ts);
}

function recibo_valor_extenso($valor = 0.0)
{
    $valor = (float) str_replace(',', '.', (string) $valor);
    $singular = ["centavo", "real", "mil", "milhao", "bilhao", "trilhao", "quatrilhao"];
    $plural = ["centavos", "reais", "mil", "milhoes", "bilhoes", "trilhoes", "quatrilhoes"];
    $c = ["", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos"];
    $d = ["", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa"];
    $d10 = ["dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove"];
    $u = ["", "um", "dois", "tres", "quatro", "cinco", "seis", "sete", "oito", "nove"];

    $z = 0;
    $valor = number_format($valor, 2, ".", ".");
    $inteiro = explode(".", $valor);
    for ($i = 0; $i < count($inteiro); $i++) {
        while (strlen($inteiro[$i]) < 3) {
            $inteiro[$i] = "0" . $inteiro[$i];
        }
    }

    $rt = '';
    $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
    for ($i = 0; $i < count($inteiro); $i++) {
        $valorAtual = $inteiro[$i];
        $rc = (($valorAtual > 100) && ($valorAtual < 200)) ? "cento" : $c[$valorAtual[0]];
        $rd = ($valorAtual[1] < 2) ? "" : $d[$valorAtual[1]];
        $ru = ($valorAtual > 0) ? (($valorAtual[1] == 1) ? $d10[$valorAtual[2]] : $u[$valorAtual[2]]) : "";
        $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
        $t = count($inteiro) - 1 - $i;
        $r .= $r ? " " . ($valorAtual > 1 ? $plural[$t] : $singular[$t]) : "";
        if ($valorAtual == "000") {
            $z++;
        } elseif ($z > 0) {
            $z--;
        }
        if (($t == 1) && ($z > 0) && ($inteiro[0] > 0)) {
            $r .= (($z > 1) ? " de " : "") . $plural[$t];
        }
        if ($r) {
            $rt .= ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? (($i < $fim) ? ", " : " e ") : " ") . $r;
        }
    }

    return trim($rt ?: 'zero');
}

function recibo_buscar_dados(mysqli $link, $idColaborador, $idProjeto)
{
    $sql = "
        SELECT
            c.id,
            c.nome,
            c.email,
            c.nascimento,
            c.cpf,
            c.rg,
            c.celular,
            c.telefone,
            c.endereco,
            c.bairro,
            c.cidade,
            c.sexo,
            c.pai,
            c.mae,
            c.responsavel,
            c.responsavelrg,
            c.responsavelcpf,
            p.nome AS projeto_nome,
            p.categoria AS projeto_categoria,
            p.anoprojeto AS projeto_ano,
            a.papel1,
            a.cache AS cacheano,
            a.situacao
        FROM colaborador c
        INNER JOIN ano_projeto a ON a.id_colaborador = c.id
        INNER JOIN projetos p ON p.id = a.id_projeto
        WHERE c.id = ? AND p.id = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'ii', $idColaborador, $idProjeto);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $dados = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    return $dados ?: null;
}

function recibo_enviar_email($destinatario, $nomeDestinatario, $assunto, $html, $texto)
{
    global $azerutan_email_from, $azerutan_email_from_name, $azerutan_email_notificacao;

    $from = trim((string) ($azerutan_email_from ?? 'azerutan@ki6.com.br'));
    $fromName = trim((string) ($azerutan_email_from_name ?? 'Associacao Cultural Azerutan'));
    $bcc = trim((string) ($azerutan_email_notificacao ?? ''));

    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . sprintf('"%s" <%s>', addslashes($fromName), $from);
    $headers[] = 'Reply-To: ' . $from;
    if ($bcc !== '') {
        $headers[] = 'Bcc: ' . $bcc;
    }

    $body = $html;
    $ok = @mail($destinatario, '=?UTF-8?B?' . base64_encode($assunto) . '?=', $body, implode("\r\n", $headers));
    if (!$ok) {
        error_log('Falha ao enviar e-mail de recibo para ' . $destinatario . '. Provider esperado: HostGator.');
    }

    return $ok;
}

if (isset($_GET['action']) && $_GET['action'] === 'solicitarAcesso') {
    $idColaborador = filter_input(INPUT_POST, 'id_colaborador', FILTER_VALIDATE_INT);
    $idProjeto = filter_input(INPUT_POST, 'id_projeto', FILTER_VALIDATE_INT);
    $nascimento = trim((string) ($_POST['nascimento'] ?? ''));

    if (!$idColaborador || !$idProjeto || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $nascimento)) {
        recibo_json([
            'ok' => false,
            'msg' => 'Informe uma data de nascimento valida para continuar.'
        ], 400);
    }

    $con = new conexao();
    $link = $con->connect();
    if (!$link) {
        recibo_json([
            'ok' => false,
            'msg' => 'Nao foi possivel validar o recibo agora. Tente novamente em instantes.'
        ], 500);
    }

    $dados = recibo_buscar_dados($link, $idColaborador, $idProjeto);
    if (!$dados) {
        recibo_json([
            'ok' => false,
            'msg' => 'Recibo nao encontrado para este colaborador no projeto selecionado.'
        ], 404);
    }

    $nascDb = substr((string) ($dados['nascimento'] ?? ''), 0, 10);
    if ($nascDb !== $nascimento) {
        recibo_json([
            'ok' => false,
            'msg' => 'A data de nascimento nao confere. Confira os dados e tente novamente.'
        ], 403);
    }

    $email = trim((string) ($dados['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        recibo_json([
            'ok' => false,
            'msg' => 'Nao encontramos um e-mail valido no cadastro deste colaborador para enviar o recibo digital.'
        ], 422);
    }

    $payload = [
        'doc' => 'recibo_digital',
        'c' => (int) $idColaborador,
        'p' => (int) $idProjeto,
        'exp' => time() + recibo_ttl(),
    ];
    $token = recibo_payload_sign($payload);
    $linkRecibo = recibo_base_url() . '/recibo_digital.php?t=' . urlencode($token);

    $nome = trim((string) ($dados['nome'] ?? 'colaborador(a)'));
    $projetoNome = trim((string) ($dados['projeto_nome'] ?? 'Projeto Azerutan'));
    $assunto = 'Seu recibo digital - ' . $projetoNome;
    $html = '
        <html><body style="font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;color:#172033;padding:24px;">
        <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #d9e0ea;border-radius:14px;overflow:hidden;">
            <div style="padding:20px 24px;background:#0f766e;color:#ffffff;">
                <h2 style="margin:0;font-size:22px;">Recibo digital liberado</h2>
            </div>
            <div style="padding:24px;">
                <p>Ola, <strong>' . recibo_h($nome) . '</strong>.</p>
                <p>Seu recibo digital do projeto <strong>' . recibo_h($projetoNome) . '</strong> ja esta disponivel.</p>
                <p style="margin:24px 0;">
                    <a href="' . recibo_h($linkRecibo) . '" style="display:inline-block;background:#198754;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:bold;">Abrir recibo digital</a>
                </p>
                <p>Se o botao nao funcionar, use este link:<br><a href="' . recibo_h($linkRecibo) . '">' . recibo_h($linkRecibo) . '</a></p>
                <p style="color:#5c6678;">Por seguranca, este link expira automaticamente.</p>
            </div>
        </div>
        </body></html>';
    $texto = "Seu recibo digital esta disponivel: " . $linkRecibo;

    if (!recibo_enviar_email($email, $nome, $assunto, $html, $texto)) {
        recibo_json([
            'ok' => false,
            'msg' => 'A validacao foi concluida, mas nao conseguimos enviar o e-mail agora. Tente novamente em instantes.'
        ], 500);
    }

    recibo_json([
        'ok' => true,
        'msg' => 'Validacao concluida. Enviamos o link do recibo digital para o e-mail cadastrado.',
        'email' => $email,
        'destino_configuracao' => (string) ($azerutan_email_notificacao ?? 'azerutan@ki6.com.br'),
        'provider' => (string) ($azerutan_email_provider ?? 'HostGator')
    ]);
}

$token = (string) ($_GET['t'] ?? '');
$payload = recibo_payload_verify($token);
if (!$payload || ($payload['doc'] ?? '') !== 'recibo_digital') {
    recibo_abort(403, 'Link do recibo invalido ou expirado.');
}

$idColaborador = (int) ($payload['c'] ?? 0);
$idProjeto = (int) ($payload['p'] ?? 0);
if ($idColaborador <= 0 || $idProjeto <= 0) {
    recibo_abort(400, 'Parametros do recibo invalidos.');
}

$con = new conexao();
$link = $con->connect();
if (!$link) {
    recibo_abort(500, 'Nao foi possivel abrir o recibo agora.');
}

$dados = recibo_buscar_dados($link, $idColaborador, $idProjeto);
if (!$dados) {
    recibo_abort(404, 'Recibo nao encontrado para este colaborador.');
}

$nomeColab = strtoupper((string) ($dados['nome'] ?? ''));
$projetoNome = (string) ($dados['projeto_nome'] ?? 'Projeto Azerutan');
$papel = (string) ($dados['papel1'] ?? 'Colaborador(a)');
$valor = (float) ($dados['cacheano'] ?? 0);
$valorFormatado = 'R$ ' . number_format($valor, 2, ',', '.');
$valorExtenso = recibo_valor_extenso($valor);
$nascimentoBr = recibo_data_br($dados['nascimento'] ?? '');
$dataEmissao = date('d/m/Y H:i');
$anoProjeto = (string) ($dados['projeto_ano'] ?? date('Y'));
$responsavel = trim((string) ($dados['responsavel'] ?? ''));
$maiorDeIdade = false;
if (!empty($dados['nascimento'])) {
    try {
        $maiorDeIdade = (new DateTime($dados['nascimento']))->diff(new DateTime('now'))->y >= 18;
    } catch (Exception $e) {
        $maiorDeIdade = false;
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo Digital</title>
    <style>
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; background: #eef2f7; color: #172033; }
        .wrap { max-width: 980px; margin: 26px auto; padding: 0 14px; }
        .card { background: #fff; border-radius: 18px; border: 1px solid #d9e0ea; box-shadow: 0 14px 30px rgba(15, 23, 42, .10); overflow: hidden; }
        .head { padding: 22px 24px; background: linear-gradient(135deg, #0f766e, #198754); color: #fff; }
        .head h1 { margin: 0 0 6px; font-size: 28px; }
        .head p { margin: 0; opacity: .9; }
        .body { padding: 24px; }
        .badge { display: inline-block; padding: 7px 12px; border-radius: 999px; background: #dcfce7; color: #166534; font-weight: 700; margin-bottom: 14px; }
        .meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 22px; }
        .meta div { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; }
        .meta b { display: block; font-size: 12px; color: #475569; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
        .texto { border: 1px solid #e5e7eb; background: #fffdf7; border-radius: 12px; padding: 18px; line-height: 1.7; }
        .assinatura { margin-top: 24px; text-align: center; color: #334155; }
        .assinatura .linha { margin: 22px auto 8px; width: min(360px, 100%); border-top: 1px solid #334155; }
        .rodape { margin-top: 18px; color: #64748b; font-size: 14px; }
        @media (max-width: 700px) { .meta { grid-template-columns: 1fr; } .head h1 { font-size: 24px; } }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="head">
                <h1>Recibo Digital</h1>
                <p>Associacao Cultural Azerutan</p>
            </div>
            <div class="body">
                <span class="badge">Acesso validado por link seguro</span>

                <div class="meta">
                    <div><b>Colaborador(a)</b><?php echo recibo_h($nomeColab); ?></div>
                    <div><b>Projeto</b><?php echo recibo_h($projetoNome); ?></div>
                    <div><b>Funcao</b><?php echo recibo_h($papel); ?></div>
                    <div><b>Ano</b><?php echo recibo_h($anoProjeto); ?></div>
                    <div><b>Valor</b><?php echo recibo_h($valorFormatado); ?></div>
                    <div><b>Emitido em</b><?php echo recibo_h($dataEmissao); ?></div>
                </div>

                <div class="texto">
                    Recebi da <strong>Associacao Cultural Azerutan</strong> a importancia de
                    <strong><?php echo recibo_h($valorFormatado); ?> (<?php echo recibo_h($valorExtenso); ?>)</strong>,
                    referente ao recibo digital de participacao no projeto
                    <strong><?php echo recibo_h($projetoNome); ?></strong>,
                    exercendo a funcao de <strong><?php echo recibo_h($papel); ?></strong>,
                    no ano de <strong><?php echo recibo_h($anoProjeto); ?></strong>,
                    sem exposicao publica de dados sensiveis no link de acesso.
                    <?php if (!$maiorDeIdade && $responsavel !== '') { ?>
                        <br><br>
                        Responsavel legal informado no cadastro: <strong><?php echo recibo_h(strtoupper($responsavel)); ?></strong>.
                    <?php } ?>
                </div>

                <div class="assinatura">
                    <div class="linha"></div>
                    <strong><?php echo recibo_h($nomeColab); ?></strong><br>
                    Nascimento: <?php echo recibo_h($nascimentoBr); ?><br>
                    CPF: <?php echo recibo_h((string) ($dados['cpf'] ?? 'Nao informado')); ?>
                </div>

                <div class="rodape">
                    Este recibo foi liberado por validacao de data de nascimento e enviado para o e-mail cadastrado do colaborador.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
