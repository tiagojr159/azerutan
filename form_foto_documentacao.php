<?php
//form_foto_documentacao.php
/*
 *   Ofereço a Deus todos esses código que escrevi como fruto do
 * meu trabalho e por intercessão de São Isodoro de Servilha e
 * São Jose Maria Escrivá esses sistema nunca seja usado para o mau
 * ou desagrado do nosso senhor Jesus Cristo. Amém.
 *
 * Tiago Junior - 31/08/2014
 * form_foto_documentacao.php
 */

$dateAno = date('Y');
require_once 'config/conexao.class.php';
require_once 'config/crud.class.php';
require_once 'config.php';

date_default_timezone_set('America/Sao_Paulo');
error_reporting(0);

$con = new conexao();
$con->connect();

/**
 * [AJUSTE PEQUENO] normaliza ids (sem mudar regra de negócio)
 */
$id_colaborador = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_projeto     = isset($_GET['id_projeto']) ? (int)$_GET['id_projeto'] : 0;

/**
 * ==========================================================
 *  [REFATORAÇÃO SOMENTE UPLOAD + CHECAGEM DE EXISTÊNCIA]
 *  - mantém as regras e retornos (echo do link)
 *  - melhora validação, nomes e checagem para URL/arquivo local
 * ==========================================================
 */
if (isset($_POST['acao']) && $_POST['acao'] === "seExiste") {

    // pode vir URL (link_imagem_projeto...) ou path local (path_imagem_projeto...)
    $link = isset($_POST['link']) ? trim($_POST['link']) : '';

    function is_url($str)
    {
        return (bool)preg_match('#^https?://#i', $str);
    }

    function urlExiste200($url)
    {
        $headers = @get_headers($url);
        if (!$headers || !isset($headers[0])) return false;
        return (strpos($headers[0], '200') !== false);
    }

    // tenta mapear URL -> caminho local quando possível (para ficar mais confiável)
    // Ex: https://.../upload_pic/2026/resize_...  ->  /var/www/.../upload_pic/2026/resize_...
    // (sem mexer no seu config, usa o que já existe: $link_imagem_projeto e $path_imagem_projeto)
    $ok = false;

    if ($link !== '') {
        if (is_url($link)) {
            // se for URL, primeiro tenta checar por HTTP
            $ok = urlExiste200($link);

            // se falhar e for URL do seu próprio servidor, tenta mapear para path local
            if (!$ok) {
                // tenta substituir base de URL pela base do path (quando o link começa com $link_imagem_projeto)
                if (!empty($GLOBALS['link_imagem_projeto']) && !empty($GLOBALS['path_imagem_projeto']) && strpos($link, $GLOBALS['link_imagem_projeto']) === 0) {
                    $local = str_replace($GLOBALS['link_imagem_projeto'], $GLOBALS['path_imagem_projeto'], $link);
                    $ok = @file_exists($local);
                }
            }
        } else {
            // path local
            $ok = @file_exists($link);
        }
    }

    echo $ok ? "ok" : "nok";
    die();
}

if (isset($_POST['acao']) && $_POST['acao'] === "cadastrar") {

    $id_colaborador = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $ano            = date('Y');
    $tipo           = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
    $time           = time();

    // validações básicas (sem mudar regra de negócio)
    if ($id_colaborador <= 0 || $tipo === '' || !isset($_FILES['foto'])) {
        echo "";
        die();
    }

    $foto = $_FILES['foto'];

    // validação de upload
    if (!isset($foto['tmp_name']) || $foto['tmp_name'] === '' || !is_uploaded_file($foto['tmp_name'])) {
        echo "";
        die();
    }

    // segue mesma regra do front: até 5MB (mantém negócio)
    if (!empty($foto['size']) && (int)$foto['size'] > 5000000) {
        echo "";
        die();
    }

    // extensões aceitas como "documento" (mantém sua lógica)
    $docExts = array('txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'xlms');
    $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));

    // garante pasta do ano
    if (!file_exists($path_imagem_projeto . "$ano/")) {
        @mkdir($path_imagem_projeto . "$ano/", 0775, true);
    }

    // nome mais robusto (pequeno ajuste)
    $seed = $time . '_' . mt_rand(1000, 9999);

    $link_arquivo_retorno = '';
    $photoParaDb = ''; // o que será gravado em foto_colaborador.foto

    if (in_array($ext, $docExts, true)) {

        // documento (PDF/Office)
        $photoParaDb = arquivoPDF($foto, $baseDir, $path_imagem_projeto, $seed);
        // retorna path local (como você já fazia nesta branch)
        $link_arquivo_retorno = $path_imagem_projeto . $photoParaDb;
    } else {

        // imagem: mantém seu padrão (resize + thumbnail) e mantém DB com thumbnail
        // (apenas organizando as variáveis)
        Redimensionar($foto, 800, $path_imagem_projeto . "", "$ano/resize_", $seed);
        $photoThumb = Redimensionar($foto, 150, $path_imagem_projeto . "", "$ano/thumbnail_", $seed);

        // o seu padrão: salva thumbnail no BD e usa resize para link
        $photoParaDb = $photoThumb;
        $fotoResizeRel = str_replace('thumbnail', 'resize', $photoThumb);

        // retorna URL (como você já fazia nesta branch)
        $link_arquivo_retorno = $link_imagem_projeto . "upload_pic/" . $fotoResizeRel;
    }

    // salva foto no BD (mesma tabela e campos)
    $crud = new crud('foto_colaborador');
    $crud->inserir("id_colaborador,foto,tipo,tamanho", "'$id_colaborador','$photoParaDb','$tipo',350");

    // mapeamento de tipo -> id_campo (mesma regra, só mais simples)
    $mapCampo = array(
        'RG'         => 7,
        'P'          => 5,
        'RESIDENCIA' => 6,
        'HABILITACAO' => 7,
        'CPF'        => 8,
    );

    if (isset($mapCampo[$tipo])) {
        $id_campo = (int)$mapCampo[$tipo];
        $date = date("Y-m-d H:i:s");
        $crudPend = new crud('pend_cad');
        $crudPend->atualizar("pendencia=0,data='$date'", "id_colaborador=$id_colaborador and id_campo = $id_campo");
    }

    // mantém retorno: o JS espera isso
    echo $link_arquivo_retorno;
    die();
}

if (isset($_POST['acao']) && $_POST['acao'] == "atualizar") {
    $id_colaborador = $_POST['id'];
    $consulta_cad_colab = mysqli_query($con->connect(), "select * from colaborador  where id = '" . $id_colaborador . "' ");
    while ($consulta_cad = mysqli_fetch_assoc($consulta_cad_colab)) {
        $telefone = $consulta_cad['telefone'];
        $raca = $consulta_cad['raca'];
        $celular = $consulta_cad['celular'];
        $sexo = $consulta_cad['sexo'];
        $comentario = $consulta_cad['comentario'];
    }
    if (!empty($_POST['raca'])) {
        $raca = $_POST['raca'];
    }
    if (!empty($_POST['celular'])) {
        $celular = $_POST['celular'];
        $comentario .= "    " . $celular;
    }
    if (!empty($_POST['sexo'])) {
        $sexo = $_POST['sexo'];
    }
    if (!empty($_POST['telefone'])) {
        $telefone = $_POST['telefone'];
        $comentario .= "    " . $telefone;
    }
    $date = date("Y-m-d H:i:s");
    $crud = new crud('pend_cad');
    $crud->atualizar("pendencia='0',data='$date'", "id_colaborador='$id_colaborador' and id_campo not in(5,6,7,8)");
    $crud = new crud('colaborador');
    $crud->atualizar("celular='$celular', telefone='$telefone', raca='$raca', sexo='$sexo', comentario='$comentario'", "id='$id_colaborador'");
    die();
}

/**
 * [AJUSTE PEQUENO] arquivoPDF recebe um nome mais robusto quando passado
 * (sem alterar regra de negócio: continua salvando na pasta do ano e retornando ano/arquivo.ext)
 */
function arquivoPDF($foto, $baseDir, $path_imagem_projeto, $seed = null)
{
    $ano = date('Y');
    $extensao = pathinfo($foto['name'], PATHINFO_EXTENSION);
    $name = $seed ? $seed : strtotime(date('Y-m-d H:i:s'));
    $baseDir = dirname(__DIR__);
    $upload_dir = $path_imagem_projeto . "$ano/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0775, true);
    }
    $uploadfile = $upload_dir . $name . "." . $extensao;

    @move_uploaded_file($foto['tmp_name'], $uploadfile);

    return $ano . "/" . $name . "." . $extensao;
}

function Redimensionar($imagem, $largura, $pasta, $nomeArquivo, $time)
{
    $extensao = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));
    switch ($extensao) {
        case "jpeg":
        case "jpg":
            $img = imagecreatefromjpeg($imagem['tmp_name']);
            break;
        case "gif":
            $img = imagecreatefromgif($imagem['tmp_name']);
            break;
        case "png":
            $img = imagecreatefrompng($imagem['tmp_name']);
            imagealphablending($img, false);
            imagesavealpha($img, true);
            break;
        default:
            return false;
    }
    $x = imagesx($img);
    $y = imagesy($img);
    $altura = ($largura * $y) / $x;
    $nova = imagecreatetruecolor($largura, $altura);
    imagealphablending($nova, false);
    imagesavealpha($nova, true);
    imagecopyresampled($nova, $img, 0, 0, 0, 0, $largura, $altura, $x, $y);
    $local = "$pasta/$nomeArquivo$time.$extensao";
    switch ($extensao) {
        case "jpeg":
        case "jpg":
            imagejpeg($nova, $local, 90);
            break;
        case "gif":
            imagegif($nova, $local);
            break;
        case "png":
            imagepng($nova, $local, 9);
            break;
    }
    imagedestroy($nova);
    imagedestroy($img);
    return str_replace("$pasta/", "", $local);
}

$consultaColaborador = mysqli_query($con->connect(), "SELECT * FROM colaborador where id = '" . $id_colaborador . "' order by id desc limit 1");
$Colaborador2 = mysqli_fetch_assoc($consultaColaborador);

require_once 'header.php';
?>

<main class="container mt-5 pt-5">
    <div class="row g-4">

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Novo Cadastro</div>
                <div class="card-body">
                    <p>Se você nunca fez a pasta de oração pelo grupo de teatro Azerutan, clique abaixo para criar nova matrícula.</p>
                    <a href="inscricao.php?novo=1" class="btn btn-success">Nova Matrícula</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Lista de projetos</div>
                <div class="card-body">
                    <p>Clique no botão a seguir para exibir a lista de todas as pessoas matriculadas para A Paixão de Cristo deste ano.</p>
                    <a href="projeto.php?id=<?php echo $_GET['projeto']; ?>" class="btn btn-success">Lista de Projetos</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Secretaria Azerutan</div>
                <div class="card-body">
                    <p>Em caso de problema na inscrição, acesse o grupo do WhatsApp da secretaria Azerutan.</p>
                    <a href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC">
                        <img src="https://paixaodecristodeigarassu.ki6.com.br/projeto/images/icone/whatsapp-icon.png" width="50" alt="WhatsApp">
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3><?php echo strtoupper($Colaborador2['nome']); ?></h3>
        </div>
        <div class="card-body">
            <form id="atualizarCad" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="atualizar" />
                <input type="hidden" name="id" value="<?php echo $id_colaborador; ?>" />
                <?php
                $botao = 0;
                $arquivo = 0;
                $pendenciaAzul = 0;
                $consulta_atua_cad = mysqli_query($con->connect(), "select * from pend_cad where id_colaborador = '" . $id_colaborador . "'");
                while ($atua_cad = mysqli_fetch_assoc($consulta_atua_cad)) {
                    $id_campo = $atua_cad['id_campo'];
                    $pendencia = $atua_cad['pendencia'];
                    if ($id_campo == 1 && $pendencia == 1) {
                        $botao = 1;
                        $pendenciaAzul = 1;
                ?>
                        <div class="form-group">
                            <span class="status-pendente"><b>FALTA ATUALIZAR O ETNIA</b>*</span>
                            <select name="raca" class="form-control">
                                <option value=""></option>
                                <option value="BRANCO(A)">BRANCO(A)</option>
                                <option value="NEGRO(A)">NEGRO(A)</option>
                                <option value="PARDO(A)">PARDO(A)</option>
                                <option value="INDÍGINO(A)">INDÍGINO(A)</option>
                                <option value="OUTRO(A)">OUTRO(A)</option>
                                <option value="NÃO INFORMARDO">NÃO INFORMAR</option>
                            </select>
                        </div>
                    <?php
                    }
                    if ($id_campo == 2 && $pendencia == 1) {
                        $botao = 1;
                        $pendenciaAzul = 1;
                    ?>
                        <div class="form-group">
                            <span class="status-pendente"><b>FALTA ATUALIZAR O GÊNERO</b>*</span>
                            <select name="sexo" class="form-control">
                                <option value="<?php echo @$campo['sexo']; ?>" selected><?php echo @$campo['sexo']; ?></option>
                                <option value="Masculino">Masculino</option>
                                <option value="Feminino">Feminino</option>
                                <option value="LGBTQIAPN+">LGBTQIAPN+</option>
                                <option value="Não Informado">Não Informar</option>
                            </select>
                        </div>
                    <?php
                    }
                    if ($id_campo == 3 && $pendencia == 1) {
                        $botao = 1;
                        $pendenciaAzul = 1;
                    ?>
                        <div class="form-group">
                            <span class="status-pendente"><b>FALTA ATUALIZAR O WHATSAPP</b>*</span>
                            <input type="text" name="telefone" maxlength="15" onkeyup="handlePhone(event)" class="form-control" />
                        </div>
                    <?php
                    }
                    if ($id_campo == 4 && $pendencia == 1) {
                        $botao = 1;
                        $pendenciaAzul = 1;
                    ?>
                        <div class="form-group">
                            <span class="status-pendente"><b>FALTA ATUALIZAR O CELULAR</b>*</span>
                            <input type="text" name="celular" maxlength="15" onkeyup="handlePhone(event)" class="form-control" />
                        </div>
                    <?php
                    }
                    if ($id_campo == 5 && $pendencia == 1) {
                        $arquivo = 1;
                        $pendenciaAzul = 1;
                    ?>
                        <div class="form-group">
                            <span class="status-pendente">Falta Foto do Perfil</span>
                        </div>
                    <?php
                    }
                    if ($id_campo == 6 && $pendencia == 1) {
                        $arquivo = 1;
                        $pendenciaAzul = 1;
                    ?>
                        <div class="form-group">
                            <span class="status-pendente">Falta Foto do Comprovante de Residência até 3 meses</span>
                        </div>
                    <?php
                    }
                    if ($id_campo == 7 && $pendencia == 1) {
                        $arquivo = 1;
                        $pendenciaAzul = 1;
                    ?>
                        <div class="form-group">
                            <span class="status-pendente">Falta Foto da Identidade/CNH</span>
                        </div>
                    <?php
                    }
                    if ($id_campo == 8 && $pendencia == 1) {
                        $arquivo = 1;
                        $pendenciaAzul = 1;
                    ?>
                        <div class="form-group">
                            <span class="status-pendente">Falta Foto do CPF</span>
                        </div>
                    <?php
                    }
                }
                if ($botao == 1) {
                    $arquivo = 0;
                    ?>
                    <button type="button" class="btn btn-primary" id="btnAtualizarDados">Atualizar Dados</button>
                <?php
                }
                ?>
            </form>

            <?php if ($pendenciaAzul == 0) { ?>
                <div class="form-group">
                    <span class="status-ok" style="display: block; text-align: center;">Prezado Colaborador(a): <b><?php echo $Colaborador2['nome']; ?></b>, Você não tem Pendência de Envio de Documentação.</span>
                    <script type="text/javascript">
                        falar('Você não tem Pendência de Envio de Documentação.');
                    </script>
                </div>
            <?php } ?>

            <?php if ($arquivo == 1) { ?>
                <form id="formImagem" enctype="multipart/form-data">
                    <div class="form-group">
                        <span><b>Selecione a foto do Documento ou Currículo Artístico PDF:</b></span>
                        <label class="label-input">
                            <span>Escolher o arquivo</span>
                            <input type="file" name="foto" required />
                        </label>
                    </div>
                    <input type="hidden" name="acao" value="cadastrar" />
                    <input type="hidden" name="id" value="<?php echo $id_colaborador; ?>" />
                    <div class="form-group">
                        <span><b>Tipo do Documento:</b></span>
                        <select name="tipo" id="tipo" class="form-control" required>
                            <option value=""></option>
                            <option value="RG">RG</option>
                            <option value="P">Foto do Perfil</option>
                            <option value="CPF">CPF</option>
                            <option value="RECIBO">RECIBO</option>
                            <option value="RESIDENCIA">COMPROVANTE RESIDÊNCIA</option>
                            <option value="HABILITACAO">CARTEIRA DE HABILITAÇÃO</option>
                            <option value="CURRICULO">CURRÍCULO ARTÍSTICO</option>
                            <option value="DOC/PDF">OUTRO TIPO DE DOCUMENTO</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" id="btnEnviar">Enviar</button>
                </form>
            <?php } ?>

            <div class="image-gallery">
                <?php
                // use a conexão já aberta
                $conn = $con->connect();
                if (!$conn) {
                    die('Erro ao conectar ao banco de dados: ' . $con->getError());
                }

                // segurança básica no id
                $id_colaborador = (int) $id_colaborador;

                function urlExiste200Local(string $url): bool
                {
                    $headers = @get_headers($url, 1);
                    if (!$headers) return false;

                    if (is_array($headers)) {
                        foreach ($headers as $k => $v) {
                            if (is_string($k) && stripos($k, 'HTTP/') === 0) {
                                if (is_string($v) && strpos($v, '200') !== false) return true;
                            }
                            if (is_int($k) && is_string($v) && strpos($v, '200') !== false) return true;
                        }
                    } else {
                        return strpos($headers[0] ?? '', '200') !== false;
                    }
                    return false;
                }

                $sqlFotos = "SELECT * FROM foto_colaborador WHERE id_colaborador = {$id_colaborador} ORDER BY id DESC LIMIT 50";
                $consulta2 = mysqli_query($conn, $sqlFotos);

                while ($campoColaborador = mysqli_fetch_assoc($consulta2)) {
                    $dataDateTime  = new DateTime($campoColaborador['data']);
                    $agoraDateTime = new DateTime();

                    // diferença total em minutos (evita usar ->i que é só o minuto do relógio)
                    $diffSegundos = $agoraDateTime->getTimestamp() - $dataDateTime->getTimestamp();
                    $diffMinutos  = (int) floor($diffSegundos / 60);

                    if ($campoColaborador['tipo'] === 'P') {
                        // verifica existência do arquivo antes de exibir
                        $fotoOrig = $campoColaborador['foto'] ?? '';
                        if ($fotoOrig === '') {
                            echo "<img src='{$baseIcones}512652.png' alt='Erro'>";
                            continue;
                        }

                        $fotoResize = str_replace('thumbnail', 'resize', $fotoOrig);
                        $urlResize  = $link_imagem_projeto . ltrim($fotoResize, '/');
                        $urlOrig    = $path_imagem_projeto . ltrim($fotoOrig, '/');
                        $urlValida = file_exists($urlResize) ? $urlResize : (file_exists($urlOrig) ? $urlOrig : '');

                        if ($urlValida) {
                            echo "<img src='" . htmlspecialchars($urlResize, ENT_QUOTES) . "' alt='Foto Perfil'>";
                        } else {
                            echo "<img src='{$baseIcones}512652.png' alt='Erro'>";
                        }
                        continue;
                    }

                    // outras fotos: usar a versão "resize" quando existir
                    $fotoOrig   = $campoColaborador['foto'] ?? '';
                    $fotoResize = str_replace('thumbnail', 'resize', $fotoOrig);

                    $urlResize  = $path_imagem_projeto . ltrim($fotoResize, '/');
                    $urlOrig    = $path_imagem_projeto . ltrim($fotoOrig,   '/');

                    $urlValida = file_exists($urlResize) ? $urlResize : (file_exists($urlOrig) ? $urlOrig : '');

                    if ($urlValida) {
                        $ext = strtolower(pathinfo($fotoOrig, PATHINFO_EXTENSION));

                        if (in_array($ext, ['txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'xlms'], true)) {
                            echo "<img src='{$baseIcones}painelestudante_z.png' alt='Documento'>";
                        } else {
                            if ($diffMinutos < 10) {
                                $restantes = 10 - $diffMinutos;
                                echo "<img src='" . $link_imagem_projeto . "" . $fotoResize . "' data-toggle='tooltip' data-placement='top' title='A foto ficarÃ¡ disponÃ­vel por {$restantes} minuto(s).' alt='Foto TemporÃ¡ria'>";
                            } else {
                                echo "<img src='{$baseIcones}51265.png' alt='Foto Expirada'>";
                            }
                        }
                    } else {
                        echo "<img src='{$baseIcones}512652.png' alt='Erro'>";
                    }
                }
                ?>
            </div>

            <h1 class="display-3 text-center mt-4">Realização</h1>
            <p class="text-center">
                <img src="<?php echo $link_imagem_projeto ?>../images/azerutan2023.jpg" style="max-width: 40%;" alt="Azerutan 2023">
            </p>
        </div>
    </div>
</main>

<footer class="container">
    <p>© Companhia 2017-<?php echo date('Y'); ?></p>
</footer>

<div class="modal" id="meuModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Carregando</h4>
                <button type="button" class="close" data-bs-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <center>
                    <img src="https://www.superiorlawncareusa.com/wp-content/uploads/2020/05/loading-gif-png-5.gif" width="120" alt="Carregando">
                    <p>Aguarde enquanto a foto está sendo carregada.</p>
                    <div id="lbmeuarquivo"></div>
                    <p>
                    <div id="temporizador"></div>
                    </p>
                </center>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/jquery.maskedinput-1.1.4.pack.js"></script>
<script src="tts.js"></script>

<script type="text/javascript">
    const handlePhone = (event) => {
        let input = event.target;
        input.value = phoneMask(input.value);
    }

    const phoneMask = (value) => {
        if (!value) return "";
        value = value.replace(/\D/g, '');
        value = value.replace(/(\d{2})(\d)/, "($1) $2");
        value = value.replace(/(\d)(\d{4})$/, "$1-$2");
        return value;
    }

    const tempoInicial = 15;

    function falar(qrCodeMessage) {
        const msg = new SpeechSynthesisUtterance();
        msg.volume = 1;
        msg.rate = 1;
        msg.pitch = 1;
        msg.text = qrCodeMessage;
        const voice = speaks[0];
        voice.voiceURI = voice.name;
        msg.lang = voice.lang;
        speechSynthesis.speak(msg);
    }

    /**
     * [AJUSTE PEQUENO] startTimer mais estável:
     * - troca setInterval de 15s por setTimeout (evita múltiplos reloads)
     * - mantém áudio, modal, regra de 5MB e checagem seExiste
     */
    async function startTimer() {
        var tipo = document.getElementById("tipo").value;
        if (tipo == '') {
            falar('Selecione o tipo do documento.');
            alert('Selecione o tipo do documento.');
            return false;
        }

        var formData = new FormData($('#formImagem')[0]);
        var file = formData.get('foto');

        if (!file) {
            falar('Selecione um arquivo.');
            alert('Selecione um arquivo.');
            return false;
        }

        console.log('Tamanho do arquivo:', file.size);

        var meuarquivo = file.size / 1000000;
        if (file.size > 5000000) {
            falar('Selecione um arquivo de imagem até 5MB. Seu arquivo tem ' + meuarquivo + 'MB');
            alert('Selecione um arquivo de imagem até 5MB. Seu arquivo tem ' + meuarquivo + 'MB');
            return false;
        }

        abrirModal();
        falar('Aguarde enquanto a foto está sendo carregada');
        document.getElementById('lbmeuarquivo').textContent = 'Meu arquivo tem ' + meuarquivo.toString().substring(0, 4) + 'MB';

        let tempoRestante = tempoInicial;

        // contador visível
        const intervaloContador = setInterval(() => {
            tempoRestante--;
            const segundos = tempoRestante % 60;
            document.getElementById('temporizador').textContent = segundos + ' segundos...';
            if (tempoRestante <= 0) {
                clearInterval(intervaloContador);
            }
        }, 1000);

        // fallback de recarregar em 15s (mesma intenção original, sem ficar criando múltiplos intervals)
        const reloadTimeout = setTimeout(() => {
            fecharModal();
            location.reload();
        }, 15000);

        // 1) envia upload
        var link = await salvar5sec(formData);

        // se não retornou link, deixa o fallback recarregar e avisa
        if (!link || (typeof link === 'string' && link.trim() === '')) {
            falar('Houve um problema ao enviar o arquivo. Se continuar, tente outro telefone.');
            return false;
        }

        // 2) checa se existe
        var existe = await existe5sec(link);

        if (existe == 'ok') {
            clearTimeout(reloadTimeout);
            clearInterval(intervaloContador);
            fecharModal();
            location.reload();
        }

        if (existe == 'nok') {
            // mantém sua regra de voz
            falar('A imagem não carregou por que a internet está ruim, se o problema continuar, envie o documento em outro telefone.');
        }
    }

    $(document).ready(function() {
        $('#btnEnviar').on('click', async function() {
            startTimer();
        });

        $('#btnAtualizarDados').on('click', async function() {
            let formData = new FormData($('#atualizarCad')[0]);
            const response = await $.ajax({
                type: 'POST',
                url: 'form_foto_documentacao.php',
                data: formData,
                processData: false,
                contentType: false,
            }).done(function(data) {
                alert('Foto enviada com sucesso!');
                location.reload();
            });
        });
    });

    async function salvar5sec(formData) {
        const response = await $.ajax({
            type: 'POST',
            url: 'form_foto_documentacao.php',
            data: formData,
            processData: false,
            contentType: false,
        });
        return response;
    }

    async function existe5sec(link) {
        var param = {};
        param['acao'] = 'seExiste';
        param['link'] = link;
        const response = await $.ajax({
            type: 'POST',
            url: 'form_foto_documentacao.php',
            data: param,
        });
        return response;
    }

    function abrirModal() {
        $('#meuModal').modal('show');
    }

    function fecharModal() {
        $('#meuModal').modal('hide');
    }
</script>
</body>

</html>