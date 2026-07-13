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

function recibo_origem_base_url()
{
    global $azerutan_recibo_origem_base_url;

    $configured = trim((string) ($azerutan_recibo_origem_base_url ?? ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    return 'http://localhost/paixaodecristo/projeto';
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

function recibo_buscar_dados(mysqli $link, $idRecibo, $idColaborador, $idProjeto)
{
    $sql = "
        SELECT
            cx.id AS recibo_id,
            cx.valor AS recibo_valor,
            cx.data AS recibo_data,
            cx.descricao AS recibo_descricao,
            cx.detalhe_pagamento,
            cx.recibo_numero,
            c.id,
            c.nome,
            c.email,
            c.nascimento,
            p.nome AS projeto_nome
        FROM caixa cx
        INNER JOIN colaborador c ON c.id = cx.id_colaborador
        INNER JOIN projetos p ON p.id = cx.id_projeto
        WHERE cx.id = ? AND cx.id_colaborador = ? AND cx.id_projeto = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'iii', $idRecibo, $idColaborador, $idProjeto);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $dados = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    return $dados ?: null;
}

function recibo_link_origem($idRecibo)
{
    return recibo_origem_base_url() . '/print_caixa_reciboPDF.php?id=' . (int) $idRecibo . '&modelo=digital';
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

    $ok = @mail($destinatario, '=?UTF-8?B?' . base64_encode($assunto) . '?=', $html, implode("\r\n", $headers));
    if (!$ok) {
        error_log('Falha ao enviar e-mail de recibo para ' . $destinatario . '. Provider esperado: HostGator.');
    }

    return $ok;
}

if (isset($_GET['action']) && $_GET['action'] === 'solicitarAcesso') {
    $idRecibo = filter_input(INPUT_POST, 'id_recibo', FILTER_VALIDATE_INT);
    $idColaborador = filter_input(INPUT_POST, 'id_colaborador', FILTER_VALIDATE_INT);
    $idProjeto = filter_input(INPUT_POST, 'id_projeto', FILTER_VALIDATE_INT);
    $nascimento = trim((string) ($_POST['nascimento'] ?? ''));

    if (!$idRecibo || !$idColaborador || !$idProjeto || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $nascimento)) {
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

    $dados = recibo_buscar_dados($link, $idRecibo, $idColaborador, $idProjeto);
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
        'r' => (int) $idRecibo,
        'c' => (int) $idColaborador,
        'p' => (int) $idProjeto,
        'exp' => time() + recibo_ttl(),
    ];
    $token = recibo_payload_sign($payload);
    $linkRecibo = recibo_base_url() . '/recibo_digital.php?t=' . urlencode($token);

    $nome = trim((string) ($dados['nome'] ?? 'colaborador(a)'));
    $projetoNome = trim((string) ($dados['projeto_nome'] ?? 'Projeto Azerutan'));
    $reciboNumero = trim((string) ($dados['recibo_numero'] ?? ''));
    $rotuloRecibo = $reciboNumero !== '' ? $reciboNumero : ('Recibo #' . (int) $idRecibo);
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
                <p><strong>Recibo:</strong> ' . recibo_h($rotuloRecibo) . '</p>
                <p style="margin:24px 0;">
                    <a href="' . recibo_h($linkRecibo) . '" style="display:inline-block;background:#198754;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:bold;">Abrir recibo digital</a>
                </p>
                <p>Se o botao nao funcionar, use este link:<br><a href="' . recibo_h($linkRecibo) . '">' . recibo_h($linkRecibo) . '</a></p>
                <p style="color:#5c6678;">Por seguranca, este link expira automaticamente.</p>
            </div>
        </div>
        </body></html>';
    $texto = 'Seu recibo digital esta disponivel: ' . $linkRecibo;

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

$idRecibo = (int) ($payload['r'] ?? 0);
$idColaborador = (int) ($payload['c'] ?? 0);
$idProjeto = (int) ($payload['p'] ?? 0);
if ($idRecibo <= 0 || $idColaborador <= 0 || $idProjeto <= 0) {
    recibo_abort(400, 'Parametros do recibo invalidos.');
}

$con = new conexao();
$link = $con->connect();
if (!$link) {
    recibo_abort(500, 'Nao foi possivel abrir o recibo agora.');
}

$dados = recibo_buscar_dados($link, $idRecibo, $idColaborador, $idProjeto);
if (!$dados) {
    recibo_abort(404, 'Recibo nao encontrado para este colaborador.');
}

header('Location: ' . recibo_link_origem($idRecibo));
exit;
