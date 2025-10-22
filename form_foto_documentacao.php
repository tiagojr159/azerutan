<?php
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
date_default_timezone_set('America/Sao_Paulo');
error_reporting(0);
$con = new conexao();
$con->connect();
@$id_colaborador = $_GET['id'];
@$id_projeto = $_GET['id_projeto'];

if (isset($_POST['acao']) && $_POST['acao'] == "seExiste") {
    if (!file_exists($_POST['link'])) {
        echo "nok";
    } else {
        echo "ok";
    }
    die();
}
if (isset($_POST['acao']) && $_POST['acao'] == "cadastrar") {
    $id_colaborador = $_POST['id'];
    $ano = date('Y');
    $foto = $_FILES['foto'];
    $tipo = $_POST['tipo'];
    $time = time();
    if (in_array(strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION)), array('txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'xlms'))) {
        $photo = arquivoPDF($foto);
        $link_arquivo = "https://paixaodecristodeigarassu.ki6.com.br/projeto/upload_pic/" . $photo;
    } else {
        redimensionar($foto, 800, "https://paixaodecristodeigarassu.ki6.com.br/projeto/upload_pic", "$ano/resize_", $time);
        $photo = redimensionar($foto, 150, "https://paixaodecristodeigarassu.ki6.com.br/projeto/upload_pic", "$ano/thumbnail_", $time);
        $foto = str_replace('thumbnail', 'resize', $photo);
        $link_arquivo = "https://paixaodecristodeigarassu.ki6.com.br/projeto/upload_pic/" . $foto;
    }
    $crud = new crud('foto_colaborador');
    $crud->inserir("id_colaborador,foto,tipo,tamanho", "'$id_colaborador','$photo','$tipo',350");
    if ($tipo == 'RG') {
        $id_campo = 7;
    }
    if ($tipo == 'P') {
        $id_campo = 5;
    }
    if ($tipo == 'RESIDENCIA') {
        $id_campo = 6;
    }
    if ($tipo == 'HABILITACAO') {
        $id_campo = 7;
    }
    if ($tipo == 'CPF') {
        $id_campo = 8;
    }
    $date = date("Y-m-d H:i:s");
    $crud = new crud('pend_cad');
    $crud->atualizar("pendencia=0,data='$date'", "id_colaborador=$id_colaborador and id_campo = $id_campo");
    echo $link_arquivo;
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

function arquivoPDF($foto) {
    $ano = date('Y');
    $extensao = pathinfo($foto['name'], PATHINFO_EXTENSION);
    $name = strtotime(date('Y-m-d H:i:s'));
    $upload_dir = "/home3/ki6com20/paixaodecristodeigarassu.ki6.com.br/projeto/upload_pic/$ano/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0775, true);
}
$uploadfile = $upload_dir . $name . "." . $extensao;
move_uploaded_file($foto['tmp_name'], $uploadfile);

    return $ano . "/" . $name . "." . $extensao;
}

function Redimensionar($imagem, $largura, $pasta, $nomeArquivo, $time) {
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
        :root {
            --light-gray: #ECEFF1;
            --forest-green: #2E7D32;
            --white: #FFFFFF;
            --light-green: #A5D6A7;
            --blue: #1976D2;
            --darker-blue: #1565C0;
            --green: #4CAF50;
            --darker-green: #43A047;
            --red: #D32F2F;
            --dark-gray: #333;
            --grayish-blue: #B0BEC5;
            --very-light-green: #E8F5E9;
            --pale-green: #DCEDC8;
            --almost-white-green: #F1F8E9;
            --soft-green: #C8E6C9;
            --medium-green: #81C784;
            --bright-green: #66BB6A;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }

        .navbar {
            background-color: var(--forest-green);
        }

        .navbar-brand, .nav-link {
            color: var(--white) !important;
        }

        .nav-link:hover {
            color: var(--light-green) !important;
        }

        .btn-primary {
            background-color: var(--blue);
            border-color: var(--blue);
        }

        .btn-primary:hover {
            background-color: var(--darker-blue);
            border-color: var(--darker-blue);
        }

        .btn-success {
            background-color: var(--green);
            border-color: var(--green);
        }

        .btn-success:hover {
            background-color: var(--darker-green);
            border-color: var(--darker-green);
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
            background-color: var(--light-green);
            border-bottom: none;
            font-weight: bold;
            text-align: center;
        }

        .card-body {
            background-color: var(--very-light-green);
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .status-pendente {
            color: var(--red);
            font-weight: bold;
        }

        .status-ok {
            color: var(--green);
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group span {
            display: block;
            margin-bottom: 5px;
        }

        .form-group .status-pendente {
            padding: 5px 10px;
            border-radius: 5px;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--grayish-blue);
            border-radius: 4px;
        }

        .label-input {
            border: 2px solid var(--grayish-blue);
            border-radius: 4px;
            padding: 8px 12px;
            background: var(--light-green);
            display: inline-block;
            cursor: pointer;
        }

        .label-input:hover {
            background: var(--pale-green);
        }

        .label-input:active {
            background: var(--soft-green);
        }

        .label-input input[type="file"] {
            display: none;
        }

        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .image-gallery img {
            max-width: 180px;
            border-radius: 4px;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: var(--dark-gray);
            background-color: var(--light-gray);
            margin-top: 20px;
        }

        @media (max-width: 576px) {
            .card-grid {
                grid-template-columns: 1fr;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
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
                        <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-bs-toggle="dropdown">Opções</a>
                        <ul class="dropdown-menu" aria-labelledby="dropdown01">
                            <li><a class="dropdown-item" href="form_colaborador.php?novo=1">Nova Matrícula</a></li>
                            <li><a class="dropdown-item" href="inscricao_renovar.php">Renovar Matrícula</a></li>
                            <li><a class="dropdown-item" href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC">Secretaria Azerutan</a></li>
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
        <div class="card-grid">
            <div class="card">
                <div class="card-header">Nova Matrícula</div>
                <div class="card-body">
                    <p>Se você nunca fez a pasta de oração pelo grupo de teatro Azerutan, clique abaixo para criar nova matrícula.</p>
                    <a href="form_colaborador.php?novo=1" class="btn btn-success">Nova Matrícula</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Lista de Candidatos</div>
                <div class="card-body">
                    <p>Clique no botão a seguir para exibir a lista de todas as pessoas matriculadas para A Paixão de Cristo deste ano.</p>
                    <a href="lista.php" class="btn btn-success">Lista de Candidatos</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Secretaria Azerutan</div>
                <div class="card-body">
                    <p>Em caso de problema na inscrição, acesse o grupo do WhatsApp da secretaria Azerutan.</p>
                    <a href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC">
                        <img src="https://paixaodecristodeigarassu.ki6.com.br/projeto/images/icone/whatsapp-icon-logo-BDC0A8063B-seeklogo.com.png" width="50" alt="WhatsApp">
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header"><?php echo $Colaborador2['nome']; ?></div>
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
                        <script type="text/javascript">falar('Você não tem Pendência de Envio de Documentação.');</script>
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

function urlExiste200(string $url): bool {
    // verifica headers; aceita qualquer linha com 200 OK (mesmo após redirecionamentos)
    $headers = @get_headers($url, 1);
    if (!$headers) return false;

    // pode vir uma string única ou array em redirecionamentos
    if (is_array($headers)) {
        foreach ($headers as $k => $v) {
            if (is_string($k) && stripos($k, 'HTTP/') === 0) { // segurança
                if (is_string($v) && strpos($v, '200') !== false) return true;
            }
            if (is_int($k) && is_string($v) && strpos($v, '200') !== false) return true;
        }
    } else {
        // fallback
        return strpos($headers[0] ?? '', '200') !== false;
    }
    return false;
}

$baseUpload = 'https://paixaodecristodeigarassu.ki6.com.br/projeto/upload_pic/';
$baseIcones = 'https://paixaodecristodeigarassu.ki6.com.br/projeto/images/icone/';

$sqlFotos = "SELECT * FROM foto_colaborador WHERE id_colaborador = {$id_colaborador} ORDER BY id DESC LIMIT 50";
$consulta2 = mysqli_query($conn, $sqlFotos);

while ($campoColaborador = mysqli_fetch_assoc($consulta2)) {
    $dataDateTime  = new DateTime($campoColaborador['data']);
    $agoraDateTime = new DateTime();

    // diferença total em minutos (evita usar ->i que é só o minuto do relógio)
    $diffSegundos = $agoraDateTime->getTimestamp() - $dataDateTime->getTimestamp();
    $diffMinutos  = (int) floor($diffSegundos / 60);

    if ($campoColaborador['tipo'] === 'P') {
        // foto de perfil direta
        $urlFotoPerfil = $baseUpload . ltrim($campoColaborador['foto'], '/');
        if (urlExiste200($urlFotoPerfil)) {
            echo "<img src='" . htmlspecialchars($urlFotoPerfil, ENT_QUOTES) . "' alt='Foto Perfil'>";
        } else {
            echo "<img src='{$baseIcones}512652.png' alt='Erro'>";
        }
        continue;
    }

    // outras fotos: usar a versão "resize" quando existir
    $fotoOrig   = $campoColaborador['foto'] ?? '';
    $fotoResize = str_replace('thumbnail', 'resize', $fotoOrig);

    $urlResize  = $baseUpload . ltrim($fotoResize, '/');
    $urlOrig    = $baseUpload . ltrim($fotoOrig,   '/');

    // checa existência online
    $urlValida = urlExiste200($urlResize) ? $urlResize : (urlExiste200($urlOrig) ? $urlOrig : '');

    if ($urlValida) {
        // extensão segura
        $ext = strtolower(pathinfo($fotoOrig, PATHINFO_EXTENSION));

        // se for documento, mostra ícone de documento
        if (in_array($ext, ['txt','pdf','doc','docx','xls','xlsx','ppt','pptx','xlms'], true)) {
            echo "<img src='{$baseIcones}painelestudante_z.png' alt='Documento'>";
        } else {
            // válido por 10 minutos
            if ($diffMinutos < 10) {
                $restantes = 10 - $diffMinutos;
                echo "<img src='" . htmlspecialchars($urlValida, ENT_QUOTES) . "' data-toggle='tooltip' data-placement='top' title='A foto ficará disponível por {$restantes} minuto(s).' alt='Foto Temporária'>";
            } else {
                echo "<img src='{$baseIcones}51265.png' alt='Foto Expirada'>";
            }
        }
    } else {
        // não existe (404 ou outro erro)
        echo "<img src='{$baseIcones}512652.png' alt='Erro'>";
    }
}
?>

                </div>

                <h1 class="display-3 text-center mt-4">Realização</h1>
                <p class="text-center">
                    <img src="https://paixaodecristodeigarassu.ki6.com.br/projeto/images/azerutan2023.jpg" style="max-width: 40%;" alt="Azerutan 2023">
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
                        <p><div id="temporizador"></div></p>
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

        async function startTimer() {
            var tipo = document.getElementById("tipo").value;
            if (tipo == '') {
                falar('Selecione o tipo do documento.');
                alert('Selecione o tipo do documento.');
                return false;
            }
            var formData = new FormData($('#formImagem')[0]);
            var file = formData.get('foto');
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
            setInterval(async () => {
                tempoRestante--;
                const minutos = Math.floor(tempoRestante / 60);
                const segundos = tempoRestante % 60;
                document.getElementById('temporizador').textContent = segundos + ' segundos...';
            }, 1000);

            setInterval(async () => {
                fecharModal();
                location.reload();
            }, 15000);

            var link = await salvar5sec(formData);
            console.log('a imagem existe?' + link);
            var existe = await existe5sec(link);
            if (existe == 'ok') {
                fecharModal();
                location.reload();
            }
            if (existe == 'nok') {
                falar('A imagem não carregou por que a internet está ruim, se o problema continuar, envie o documento em outro telefone.');
            }
        }

        $(document).ready(function () {
            $('#btnEnviar').on('click', async function () {
                startTimer();
            });

            $('#btnAtualizarDados').on('click', async function () {
                let formData = new FormData($('#atualizarCad')[0]);
                const response = await $.ajax({
                    type: 'POST',
                    url: 'form_foto_documentacao.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                }).done(function (data) {
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