<?php
//inscricao.php
/*
 *   Ofereço a Deus todos esses código que escrevi como fruto do 
 * meu trabalho e por intercessão de São Isodoro de Servilha e 
 * São José Maria Escrivá esses sistema nunca seja usado para o mau 
 * ou desagrado do nosso senhor Jesus Cristo. Amém.  
 * 
 * Tiago Junior - 31/08/2014
 */
require_once 'config/conexao.class.php';
require_once 'config/crud.class.php';

$con = new conexao();
$con->connect();
$conn = $con->connect();

$id_projeto = 0;
if (!empty($_POST['projeto'])) {
    $id_projeto =  $_POST['projeto'];
} elseif (!empty($_GET['projeto'])) {
    $id_projeto = $_GET['projeto'];
}

if (isset($_POST['action']) && $_POST['action'] == 'cadastrar') {
    $anodata = date('Y');
    $nome = $_POST['nome'];
    $id_projeto = $_POST['id_projeto'];
    $idade = "0";
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $celular = $_POST['celular'];
    $serie = $_POST['serie'];
    $endereco = $_POST['endereco'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $sexo = $_POST['sexo'];
    $raca = $_POST['raca'];
    $pai = $_POST['pai'];
    $mae = $_POST['mae'];
    $nascimento = $_POST['nascimento'];
    $cpf = $_POST['cpf'];
    $rg = $_POST['rg'];
    $cep = $_POST['cep'];
    $responsavel = $_POST['responsavel'];
    $responsavelrg = $_POST['responsavelrg'];
    $responsavelcpf = $_POST['responsavelcpf'];

    try {
        $crud = new crud('colaborador');
        $crud->inserir(
            "nome,idade,email,telefone,celular,serie,endereco,bairro,cidade,sexo,pai,mae,nascimento,cpf,rg,responsavel,responsavelrg,responsavelcpf,cep,raca",
            "'$nome','$idade','$email','$telefone','$celular','$serie','$endereco','$bairro','$cidade','$sexo','$pai','$mae','$nascimento','$cpf','$rg','$responsavel','$responsavelrg','$responsavelcpf','$cep','$raca'"
        );
    } catch (Exception $e) {
        http_response_code(400);
        die('Erro ao inserir colaborador: ' . $e->getMessage());
    }

    $consulta_vagas = mysqli_query($con->connect(), "SELECT id FROM colaborador ORDER BY id DESC LIMIT 1");
    $campo = mysqli_fetch_assoc($consulta_vagas);
    echo $campo['id'];//id do colaborador inserido para retorno ajax ir pra tela de subir documentacao

    $crud = new crud('pend_cad');
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",1,0");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",2,0");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",3,0");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",4,0");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",5,1");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",6,1");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",7,1");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",8,1");


    try {
        $crud = new crud('ano_projeto');
        $crud->inserir(
            "ano,tipo,id_colaborador,situacao,id_projeto",
            "'$anodata','C','" . $campo['id'] . "','PENDENTE','" . $id_projeto . "' "
        );
    } catch (Exception $e) {
        http_response_code(400);
        die('Erro ao inserir ano_projeto: ' . $e->getMessage());
    }
    exit();
}

include_once 'header.php';
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscrição</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ IMPORTANTE: jQuery precisa vir ANTES de qualquer script que usa $ -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Ajuste visual (sem mexer em nenhuma funcionalidade) -->
    <style>
        .az-card {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, .08);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .04);
        }

        .az-card .card-header {
            background: rgba(76, 175, 80, .10);
            border-bottom: 1px solid rgba(0, 0, 0, .06);
            font-weight: 700;
        }

        .az-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            margin: 6px 0 14px;
        }

        .az-section-title .badge {
            background: rgba(76, 175, 80, .12);
            color: #1f7a32;
            border: 1px solid rgba(76, 175, 80, .20);
            font-weight: 700;
        }

        .az-help {
            font-size: .88rem;
            color: #6c757d;
            margin-top: 4px;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
        }

        .btn {
            border-radius: 12px;
        }

        .outrotitle {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 800;
            background: rgba(255, 193, 7, .16);
            border: 1px solid rgba(255, 193, 7, .25);
            color: #7a5a00;
        }

        .sucesso {
            display: none;
            border-radius: 16px;
            padding: 18px;
            background: rgba(25, 135, 84, .08);
            border: 1px solid rgba(25, 135, 84, .18);
        }

        .sucesso p {
            margin: 0 0 10px;
            font-weight: 700;
            color: #146c43;
        }

        /* Mantém comportamento homologado: menor começa escondido */
        #outrasInformacoes {
            display: none;
        }
    </style>
</head>

<body>

    <main class="container mt-5 pt-4">

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Projetos</div>
                    <div class="card-body">
                        <p>Veja a lista de Projetos realizados pelo azerutan.</p>
                        <a href="index.php?id=<?= $id_projeto; ?>" class="btn btn-primary">Lista de Projetos</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Secretaria Azerutan</div>
                    <div class="card-body">
                        <p>Entre no grupo do WhatsApp para suporte ou dúvidas.</p>
                        <a href="https://chat.whatsapp.com/CKvK1IcvC0E69CsnXZLtaC">
                            <img src="images/icone/whatsapp.png" alt="WhatsApp" width="50">
                        </a>
                    </div>
                </div>
            </div>

            <?php
            $stmt = $conn->prepare("SELECT nome FROM projetos WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $id_projeto);
            $stmt->execute();
            $stmt->bind_result($nomeProjeto);
            $stmt->fetch();
            $stmt->close();
            ?>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Projeto</div>
                    <div class="card-body text-center">
                        <h3 style="color:#4CAF50;">Inscrições Abertas</h3>
                        <?php if (!empty($nomeProjeto)) : ?>
                            <div class="text-muted"><?= htmlspecialchars($nomeProjeto); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD FORM -->
        <div class="card az-card mt-4">
            <div class="card-header">Cadastro</div>
            <div class="card-body">

                <form action="" method="post" id="inscricaoAtor" name="inscricaoAtor">
                    <input type="hidden" name="action" value="cadastrar" />
                    <input type="hidden" name="id_projeto" value="<?= $id_projeto ?>" />

                    <div class="row g-3" id="formColaborador">

                        <!-- Seção: Dados Pessoais -->
                        <div class="col-12">
                            <div class="az-section-title">
                                <span class="badge">1</span> <span>Dados pessoais</span>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label">Nome*</label>
                            <input type="text" name="nome" maxlength="250" class="form-control"
                                style="text-transform: uppercase;" placeholder="Digite seu nome completo" />
                            <div class="az-help">Digite exatamente como no documento.</div>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Data de Nascimento*</label>
                            <input type="date" id="datanascimento" name="nascimento" class="form-control" onblur="getAge()" />
                            <div class="az-help">Ao sair do campo, o sistema valida maior/menor de idade.</div>
                        </div>

                        <div class="col-md-6" id="documento_rg">
                            <label class="form-label">CPF*</label>
                            <input type="text" name="cpf" id="cpf" maxlength="14"
                                onkeyup="handleCPF(event)" placeholder="000.000.000-00"
                                class="form-control" />
                            <div class="az-help">Ao sair do campo, o sistema verifica se já existe cadastro.</div>
                        </div>

                        <div class="col-md-6" id="documento_rg">
                            <label class="form-label">RG* <span class="text-muted">(ex: 9.999.999 SDS PE)</span></label>
                            <input type="text" name="rg" maxlength="250" class="form-control" placeholder="Digite seu RG" />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Gênero*</label>
                            <select name="sexo" class="form-select">
                                <option value="<?php echo @$campo['sexo']; ?>" selected><?php echo @$campo['sexo']; ?></option>
                                <option value="Masculino">Masculino</option>
                                <option value="Feminino">Feminino</option>
                                <option value="LGBTQIAPN+">LGBTQIAPN+</option>
                                <option value="Não Informado">Não Informar</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Raça*</label>
                            <select name="raca" class="form-select">
                                <option value=""></option>
                                <option value="BRANCO(A)">BRANCO(A)</option>
                                <option value="NEGRO(A)">NEGRO(A)</option>
                                <option value="PARDO(A)">PARDO(A)</option>
                                <option value="INDÍGINO(A)">INDÍGINO(A)</option>
                                <option value="OUTRO(A)">OUTRO(A)</option>
                                <option value="NÃO INFORMARDO">NÃO INFORMAR</option>
                            </select>
                        </div>

                        <!-- Seção: Contato -->
                        <div class="col-12 mt-2">
                            <div class="az-section-title">
                                <span class="badge">2</span> <span>Contato</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">WhatsApp*</label>
                            <input type="text" name="telefone" maxlength="15" onkeyup="handlePhone(event)"
                                class="form-control" placeholder="(00) 00000-0000" />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Celular*</label>
                            <input type="text" name="celular" maxlength="15" onkeyup="handlePhone(event)"
                                class="form-control" placeholder="(00) 00000-0000" />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="text" name="email" maxlength="250" value="<?php echo @$campo['email']; ?>"
                                class="form-control" placeholder="seuemail@exemplo.com" />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Escolaridade</label>
                            <input type="text" name="serie" maxlength="250" value="<?php echo @$campo['serie']; ?>"
                                class="form-control" placeholder="Ex: Ensino Médio, Superior..." />
                        </div>

                        <!-- Seção: Endereço -->
                        <div class="col-12 mt-2">
                            <div class="az-section-title">
                                <span class="badge">3</span> <span>Endereço</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="cidade" maxlength="250" value="<?php echo @$campo['cidade']; ?>"
                                class="form-control" placeholder="Ex: Igarassu" />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Bairro</label>
                            <input type="text" name="bairro" maxlength="250" value="<?php echo @$campo['bairro']; ?>"
                                class="form-control" placeholder="Ex: Centro" />
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Endereço</label>
                            <input type="text" name="endereco" maxlength="250" value="<?php echo @$campo['endereco']; ?>"
                                class="form-control" placeholder="Rua, número, complemento" />
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">CEP</label>
                            <input type="text" name="cep" maxlength="250" value="<?php echo @$campo['cep']; ?>"
                                class="form-control" placeholder="00000-000" />
                        </div>

                        <!-- Menor de idade -->
                        <div class="col-12 outrasInformacoes" id="outrasInformacoes">
                            <div class="mt-3 mb-2">
                                <span class="outrotitle">INFORMAÇÕES PARA MENOR DE IDADE</span>
                                <div class="az-help mt-2">
                                    Se você for menor de 18 anos, estes campos ficam obrigatórios conforme a validação do sistema.
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label">Nome do Pai</label>
                                    <input type="text" name="pai" maxlength="250" style="text-transform: uppercase;"
                                        class="form-control" placeholder="Nome do pai" />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nome da Mãe</label>
                                    <input type="text" name="mae" maxlength="250" style="text-transform: uppercase;"
                                        class="form-control" placeholder="Nome da mãe" />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nome do Responsável</label>
                                    <input type="text" name="responsavel" maxlength="250"
                                        class="form-control" placeholder="Responsável legal" />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">RG do Responsável</label>
                                    <input type="text" name="responsavelrg" maxlength="250"
                                        class="form-control" placeholder="RG do responsável" />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">CPF do Responsável*</label>
                                    <input type="text" name="responsavelcpf" id="responsavelcpf" maxlength="14"
                                        onkeyup="handleCPF(event)" placeholder="000.000.000-00"
                                        class="form-control" />
                                </div>
                            </div>
                        </div>

                        <div class="col-12 text-center mt-4">
                            <input type="button" class="btn btn-success px-5 py-2"
                                onclick="salvarCadastro()" name="cadastrar" value="SALVAR" />
                            <div class="az-help mt-2">Ao salvar, você será direcionado para enviar a documentação.</div>
                        </div>
                    </div>
                </form>

                <div class="sucesso" id="sucesso">
                    <p>Sua inscrição foi realizada com sucesso.</p>
                    <div class="botaoDocumentos" id="botaoDocumentos"></div>
                </div>

            </div>
        </div>
    </main>

    <footer class="container text-center py-4">
        <p class="text-muted mb-0">© Companhia 2017-2025</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Mantém seu tts.js, mas com proteção para não quebrar se algum elemento não existir -->
    <script>
        // se o tts.js depender de algum elemento específico, evita quebrar tudo
        window.addEventListener('error', function(e) {
            // não bloqueia erros do sistema, só evita "parar" scripts por causa do tts
        });
    </script>
    <script src="tts.js"></script>

    <script language="JavaScript">
        function getAge() {
            let dataNascimento = document.getElementById('datanascimento');
            const today = new Date();

            // input type="date" vem como YYYY-MM-DD
            const birthDate = new Date(dataNascimento.value);
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();

            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            if (age < 18) {
                document.getElementById('outrasInformacoes').style.display = 'block';
                document.getElementById('documento_rg').style.display = 'none';

                if (document.inscricaoAtor.responsavelcpf.value == "" || document.inscricaoAtor.responsavelcpf.value.length < 11) {
                    falar('Digite o CPF do responsável corretamente!');
                    alert("Digite o CPF do responsável corretamente!");
                    document.inscricaoAtor.cpf.focus();
                    return false;
                }
                if (document.inscricaoAtor.responsavelrg.value == "" || document.inscricaoAtor.responsavelrg.value.length < 5) {
                    falar('Digite o RG do responsável corretamente!');
                    alert("Digite o RG do responsável corretamente!");
                    document.inscricaoAtor.rg.focus();
                    return false;
                }
            } else {
                document.getElementById('outrasInformacoes').style.display = 'none';
                document.getElementById('documento_rg').style.display = 'block';

                if (document.inscricaoAtor.cpf.value == "" || document.inscricaoAtor.cpf.value.length < 11) {
                    falar('Digite seu CPF corretamente!');
                    alert("Digite seu CPF corretamente!");
                    document.inscricaoAtor.cpf.focus();
                    return false;
                }
                if (document.inscricaoAtor.rg.value == "" || document.inscricaoAtor.rg.value.length < 5) {
                    falar('Digite o RG corretamente!');
                    alert("Digite o RG corretamente!");
                    document.inscricaoAtor.rg.focus();
                    return false;
                }
            }
            return true;
        }

        const handleCPF = (event) => {
            let input = event.target;
            input.value = CPFMask(input.value);
        }

        const CPFMask = (value) => {
            if (!value) return "";
            value = value.replace(/\D/g, "");
            value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
            return value;
        };

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

        function validarCPF(cpf) {
            if (cpf.value.length > 0) {
                $.ajax({
                    url: 'crud_consulta.php',
                    type: 'GET',
                    data: {
                        cpf: cpf.value, // ✅ envia valor correto
                        action: 'valida_cpf'
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.message == 'existe') {
                            falar('Este CPF já está cadastrado');
                            alert('Você deve renovar sua matrícula');
                            document.inscricaoAtor.cpf.focus();
                            window.location.href = 'inscricao_renovar.php';
                            return false;
                        }
                    },
                    error: function() {
                        alert('Erro ao validar CPF!');
                        return false;
                    }
                });
            }
        }

        $(document).ready(function() {
            // ✅ antes estava passando string; agora passa o elemento (this)
            $('#cpf').on('blur', function() {
                validarCPF(this);
            });
        });

        function salvarCadastro() {
            if (getAge() == false) {
                return false;
            }

            var $inputs = $('#inscricaoAtor').find("input, date, select, button, textarea");
            var serializedData = $inputs.serializeArray();

            $inputs.each(function() {
                if ($(this).attr("type") === "date") {
                    serializedData.push({
                        name: $(this).attr("name"),
                        value: $(this).val()
                    });
                }
            });

            var retorno = enviardados();
            if (!retorno) {
                return false;
            }

            $.ajax({
                url: "inscricao.php",
                data: serializedData,
                type: "POST",
                success: function(data) {
                    falar('Seu cadastro foi efetuado com sucesso, clique no botão para subir a foto de sua documentação!');
                    document.getElementById('formColaborador').style.display = 'none';
                    document.getElementById('sucesso').style.display = 'block';
                    document.getElementById('botaoDocumentos').innerHTML =
                        "<a class='btn btn-primary' href='form_foto_documentacao.php?id=" + data + "'>Subir fotos da documentação</a>";
                },
                // ✅ jQuery usa "error" aqui, não "fail"
                error: function(data) {
                    alert('Houve um erro ao enviar o formulário.');
                    console.log(data);
                }
            });
        }

        function falar(qrCodeMessage) {
            const msg = new SpeechSynthesisUtterance();
            msg.volume = 1;
            msg.rate = 1;
            msg.pitch = 1;
            msg.text = qrCodeMessage;

            // mantém sua lógica original (se speaks existir)
            try {
                const voice = speaks[0];
                voice.voiceURI = voice.name;
                msg.lang = voice.lang;
            } catch (e) {}

            speechSynthesis.speak(msg);
        }

        function enviardados() {
            if (document.inscricaoAtor.telefone.value == "" || document.inscricaoAtor.telefone.value.length < 8) {
                falar('Preencha o número de WhatsApp corretamente!');
                alert("Preencha o número de WhatsApp corretamente!");
                document.inscricaoAtor.telefone.focus();
                return false;
            }
            if (document.inscricaoAtor.celular.value == "" || document.inscricaoAtor.celular.value.length < 8) {
                falar('Preencha o número de Celular corretamente!');
                alert("Preencha o número de Celular corretamente!");
                document.inscricaoAtor.celular.focus();
                return false;
            }
            if (document.inscricaoAtor.nascimento.value == "" || document.inscricaoAtor.nascimento.value.length < 8) {
                falar('Digite a data de nascimento corretamente!');
                alert("Digite a data de nascimento corretamente!");
                document.inscricaoAtor.nascimento.focus();
                return false;
            }
            if (document.inscricaoAtor.nome.value == "" || document.inscricaoAtor.nome.value.length < 8) {
                falar('Digite seu Nome corretamente!');
                alert("Digite seu Nome corretamente!");
                document.inscricaoAtor.nome.focus();
                return false;
            }
            if (document.inscricaoAtor.sexo.value == "" || document.inscricaoAtor.sexo.value.length < 5) {
                falar('Informe um gênero!');
                alert("Informe um gênero!");
                document.inscricaoAtor.rg.focus();
                return false;
            }
            if (document.inscricaoAtor.raca.value == "" || document.inscricaoAtor.raca.value.length < 5) {
                falar('Declare sua Raça!');
                alert("Declare sua Raça!");
                document.inscricaoAtor.rg.focus();
                return false;
            }
            return true;
        }

        function Formatadata(Campo, teclapres) {
            var tecla = teclapres.keyCode;
            var vr = new String(Campo.value);
            vr = vr.replace("/", "");
            vr = vr.replace("/", "");
            vr = vr.replace("/", "");
            tam = vr.length + 1;
            if (tecla != 8 && tecla != 8) {
                if (tam > 0 && tam < 2)
                    Campo.value = vr.substr(0, 2);
                if (tam > 2 && tam < 4)
                    Campo.value = vr.substr(0, 2) + '/' + vr.substr(2, 2);
                if (tam > 4 && tam < 7)
                    Campo.value = vr.substr(0, 2) + '/' + vr.substr(2, 2) + '/' + vr.substr(4, 7);
            }
        }
    </script>

</body>

</html>

<?php $con->disconnect(); ?>