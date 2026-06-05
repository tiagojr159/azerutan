<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <title>Crop de imagem com PHP e Jquery</title>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/jquery.Jcrop.js" type="text/javascript"></script>
    <script src="js/jquery.color.js" type="text/javascript"></script>
    <link rel="stylesheet" href="css/jquery.Jcrop.css" type="text/css" />
    <link rel="stylesheet" href="css/jquery.Jcrop.extras.css" type="text/css" />
    <link rel="stylesheet" href="img/demos.css" type="text/css" />
    

    <style>
        .imagem_corte {
            width: 100%;
            height: auto;
        }
    </style>

</head>

<body>

    <body background="aaa.jpg">
        <table border='1' align='center' bgcolor="#fff">
            <tr>
                <td>
                    <?php
                    require_once 'config/crud.class.php';
                    require_once 'config/conexao.class.php';

                    $con = new conexao();
                    $con->connect();

                    $date = date('YmdHis');
                    $ano = date('Y');
                    if (!empty($_POST['subir'])) {



                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            $nome = $_POST['arquivo'];
                            $file = explode('.', $nome);
                            $nomeArquivo = $date . "." . $file[1];
                            $ext = $_POST['tipo_arquivo'];

                            $targ_w = $_POST['w'] / 5;
                            $targ_h = $_POST['h'] / 5;
                            $jpeg_quality = 100;
                            $src = $_POST['arquivo'];
                            if ($ext == 'jpg') {
                                $img_r = imagecreatefromjpeg("../projeto/upload_pic/recorte.jpg");
                            } else {
                                $img_r = imagecreatefrompng("../projeto/upload_pic/recorte.jpg");
                            }
                            $dst_r = ImageCreateTrueColor($targ_w, $targ_h);

                            imagecopyresampled($dst_r, $img_r, 0, 0, $_POST['x'], $_POST['y'], $targ_w, $targ_h, $_POST['w'], $_POST['h']);
                            if ($ext == 'jpg') {
                                $imagem = imagejpeg($dst_r, "../projeto/upload_pic/" . $ano . "/thumbnail_" . $nomeArquivo, $jpeg_quality);
                            } else {
                                $imagem = imagepng($dst_r, "../projeto/upload_pic/" . $ano . "/thumbnail_" . $nomeArquivo);
                            }
                            $tamanhoImagem = getimagesize("../projeto/upload_pic/recorte.jpg");

                            $dst_r2 = ImageCreateTrueColor(800, 750);

                            imagecopyresampled($dst_r2, $img_r, 0, 0, 0, 0, 800, 750, $tamanhoImagem[0], $tamanhoImagem[1]);

                            if ($ext == 'jpg') {
                                $imagem = imagejpeg($dst_r2, "../projeto/upload_pic/" . $ano . "/resize_" . $nomeArquivo, $jpeg_quality);
                            } else {
                                $imagem = imagepng($dst_r2, "../projeto/upload_pic/" . $ano . "/resize_" . $nomeArquivo);
                            }
                            $nome = $_POST['nome'];
                            $idade = $_POST['idade'];
                            $email = $_POST['email'];
                            $telefone = $_POST['telefone'];
                            $celular = $_POST['celular'];
                            $serie = $_POST['serie'];
                            $endereco = $_POST['endereco'];
                            $bairro = $_POST['bairro'];
                            $cidade = $_POST['cidade'];
                            $sexo = $_POST['sexo'];
                            $cpf = $_POST['cpf'];
                            $rg = $_POST['rg'];
                            $pai = $_POST['pai'];
                            $mae = $_POST['mae'];
                            $tp_sangue = $_POST['tp_sangue'];
                            $nascimento = $_POST['nascimento'];
                            $funcao = $_POST['funcao'];

                            $crud = new crud('colaborador');
                            $crud->inserir(
                                "nome,idade,email,telefone,celular,serie,endereco,bairro,cidade,sexo,pai,mae,tp_sangue,nascimento,funcao, rg, cpf, cache,chamada,comentario,experiencia,grupo,responsavel,responsavelcpf,responsavelrg,cep",
                                "'$nome','$idade','$email','$telefone','$celular','$serie','$endereco','$bairro','$cidade','$sexo','$pai','$mae','$tp_sangue','$nascimento','$funcao','$rg','$cpf','','2015-03-25','','','','','','',''"
                            );

                            $ultimo_id = mysqli_query($con->connect(), "SELECT * FROM colaborador  ORDER BY  id desc LIMIT  1"); // query que busca todos os dados da tabela PRODUTO
                            $ultimo_id = mysqli_fetch_assoc($ultimo_id);
                            $ultimo_id = $ultimo_id['id'];

                            $situacao = 'PENDENTE';

                            $crud = new crud('ano_projeto');
                            $crud->inserir("ano,situacao, id_colaborador, tipo", "'$ano','$situacao', '$ultimo_id', 'C'");

                            $crud = new crud('foto_colaborador');
                            $crud->inserir("id_colaborador,foto,tipo,descricao", "'$ultimo_id','" . $ano . "/thumbnail_$nomeArquivo','P',''");

                            echo "<script>alert('Cadastro realizado com sucesso!');
                            window.location.href = 'https://paixaodecristodeigarassu.ki6.com.br/inscricao/index.php';
                            </script>";
                            header("Location: lista.php?tela=colaborador");

                        }
                    }
                    if ($_FILES) {
                        $nome = $_FILES['arquivo']['name'];
                        $file = explode('.', $nome);
                        if ($file[1] == 'jpg') {
                            $image = imagecreatefromjpeg($_FILES['arquivo']['tmp_name']);
                        } else {
                            $image = imagecreatefrompng($_FILES['arquivo']['tmp_name']);
                        }
                        $nomeArquivo = "recorte.jpg";
                        $ext = $file[1];
                        $tmpArquivo = $_FILES['arquivo']['tmp_name'];
                        move_uploaded_file($tmpArquivo, '../projeto/../projeto/upload_pic/recorte.jpg');
                        $nome = "recorte.jpg";
                        ?>

                        <form class="" method="post" enctype="multipart/form-data" action="">
                            <input type="hidden" id='subir' name="subir" value="subir" />
                            <center><img src="../projeto/upload_pic/<?php echo $nome; ?>" width="300" class="image-m_corte"
                                    id="target" alt="<?php echo $nome; ?>" style="aligen:center" /></center>
                            <input type="hidden" id="arquivo" class="imagem_corte" name="arquivo"
                                value="img/<?php echo $nome; ?>" />
                            <input type="hidden" id="tipo_arquivo" name="tipo_arquivo" value="<?php echo $file[1]; ?>" />
                            <input type="hidden" id="x" name="x" />
                            <input type="hidden" id="y" name="y" />
                            <input type="hidden" id="w" name="w" />
                            <input type="hidden" id="h" name="h" />
                            <input type="hidden" id="tamanhoW" name="tamanhoW" />
                            <input type="hidden" id="tamanhoH" name="tamanhoH" />



                            <input type='hidden' value='$thumb_image_name' name='photo'>
                            <font face=arial size=2>
                                <table border='0' bordercolor='#000' bgcolor='white' cellspacing='10' cellpadding='0'
                                    width='100%'>
                                    <tr>
                                        <td COLSPAN=5>
                                            <!-- <img src='topo_paixao.jpg' width='100%'> -->
                                            <br>
                                            <br>
                                            <center>Inscrição da Paixão de Cristo</center>
                                            <br>
                                            <font face=arial size=2>
                                                <b>Termo de participação:</b>
                                                <br>Aos realizar sua inscrição neste formulário, você declara ter ciencia e
                                                concorda com os termos abaixo:
                                                <li>1 - Estou ciente de que minha participação no espetáculo é de
                                                    voluntário;
                                                <li>2 - Estou ciente serei voluntario em todo processo e que não existe
                                                    vínculo empregatício;
                                                <li>3 - Estou ciente de que a produção do espetáculo e os patrocinadores
                                                    poderão utilizar minhas imagens captadas durante a minha participação no
                                                    espetáculo para fins de divulgação do espetáculo;
                                                <li>4 - Minhas informações são verdadeiras
                                            </font>
                                        <td>
                                    </tr>
                                    <tr>
                                        <td>Nome</td>
                                        <td><input type='text' name='nome' size='35' max='250' value='' /></td>
                                        <td>Idade</td>
                                        <td><input type='text' name='idade' size='15' max='250' value='' /></td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td><input type='text' name='email' size='35' max='250' value='' /></td>
                                        <td>Telefone</td>
                                        <td><input type='text' name='telefone' size='20' max='250' value='' /></td>
                                    </tr>
                                    <tr>
                                        <td>CPF</td>
                                        <td><input type='text' name='cpf' id='cpf' size='35' max='250' /></td>
                                        <td>RG</td>
                                        <td><input type='text' name='rg' size='15' max='250' /></td>
                                    </tr>


                                    <tr>
                                        <td>Celular</td>
                                        <td><input type='text' name='celular' size='20' max='250' value='' /></td>
                                        <td>Escolaridade</td>
                                        <td><input type='text' name='serie' size='10' max='250' value='' /></td>
                                    </tr>
                                    <tr>
                                        <td>Endereco</td>
                                        <td><input type='text' name='endereco' size='40' max='250' value='' /></td>
                                        <td>Bairro</td>
                                        <td><input type='text' name='bairro' size='20' max='250' value='' /></td>
                                    </tr>
                                    <tr>
                                        <td>Nome do Pai</td>
                                        <td><input type='text' name='pai' size='20' max='250' value='' /></td>
                                        <td>Nome da Mãe</td>
                                        <td><input type='text' name='mae' size='20' max='250' value='' /></td>
                                    </tr>
                                    <tr>
                                        <td>Data de Nascimento</td>
                                        <td><input type='text' name='nascimento' size='12' max='250' value='' /></td>
                                        <td>Tipo Sanguineo</td>
                                        <td>

                                            <select name='tp_sangue'>
                                                <option value='' selected> </option>
                                                <option value='A+'>A+</option>
                                                <option value='A-'>A-</option>
                                                <option value='B+'>B+</option>
                                                <option value='B-'>B-</option>
                                                <option value='AB+'>AB+</option>
                                                <option value='AB-'>AB-</option>
                                                <option value='O+'>O+</option>
                                                <option value='O-'>O-</option>
                                            </select>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Cidade</td>
                                        <td><input type='text' name='cidade' size='20' max='250' value='' /></td>
                                        <td>Sexo</td>
                                        <td>
                                            <select name='sexo'>
                                                <option value='' selected> </option>
                                                <option value='Masculino'>Masculino</option>
                                                <option value='Feminino'>Feminino</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td COLSPAN=5>
                                            <center>
                                                <input type='submit' name='cadastrar' value='Salvar Inscrição' />

                                            </center>
                                        <td>
                                    </tr>
                                    <tr>
                                        <td COLSPAN=5>
                                    </tr>
                                </table>
                        </form>

                    <?php } else { ?>
                        <div style="padding:20px">
                            <h2>Selecione uma foto.</h2>
                            <form class="" method="post" enctype="multipart/form-data" action="">
                                <input type="file" class="input" name="arquivo">
                                <br />
                                <br />
                                <button class="large-12" type="submit">Enviar</button>
                            </form>
                        </div>
                    <?php } ?>


                    <script type="text/javascript">
                        jQuery(function ($) {
                            var jcrop_api, boundx, boundy;
                            var wi = $("#target").width();
                            var hei = $("#target").height();
                            var tamanhoW = (<?php echo imagesx($image); ?>* 0.6);
                            var tamanhoH = (<?php echo imagesx($image); ?>* 0.7);

                            $('#target').Jcrop({
                                onChange: updatePreview,
                                onSelect: updatePreview,
                                setSelect: [0, 0, tamanhoW, tamanhoH],
                                trueSize: [<?php echo imagesx($image); ?>, <?php echo imagesy($image); ?>],
                                allowMove: true,
                                allowResize: false,
                                allowSelect: false
                            }, function () {
                                var bounds = this.getBounds();
                                boundx = bounds[0];
                                boundy = bounds[0.75];

                            });

                            function updatePreview(c) {
                                if (parseInt(c.w) > 0) {
                                    var rx = <?php echo imagesx($image); ?> / c.w;
                                    var ry = <?php echo imagesy($image); ?> / c.h;

                                    $('#preview').css({
                                        width: Math.round(rx * boundx) + 'px',
                                        height: Math.round(ry * boundy) + 'px',
                                        marginLeft: '-' + Math.round(rx * c.x) + 'px',
                                        marginTop: '-' + Math.round(ry * c.y) + 'px'
                                    });
                                }

                                $('#x').val(c.x);
                                $('#y').val(c.y);
                                $('#w').val(c.w);
                                $('#h').val(c.h);
                                $("#tamanhoW").val(<?php echo imagesx($image); ?>); //$("#target").width();
                                $("#tamanhoH").val(<?php echo imagesy($image); ?>);

                            };

                        });

                    </script>
                    <br />
                    <br />

                </td>
            </tr>
        </table>
    </body>

</html>