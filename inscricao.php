<?php
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
$conn = $con->connect(); // <-- ADICIONE ESTA LINHA
try {
    $id_projeto = (int)($_GET['id_projeto'] ?? $_POST['id_projeto'] ?? 0);
} catch (\Throwable $th) {
    $id_projeto = 0;
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

    $crud = new crud('colaborador');
    $crud->inserir(
        "nome,idade,email,telefone,celular,serie,endereco,bairro,cidade,sexo,pai,mae,nascimento,cpf,rg,responsavel,responsavelrg,responsavelcpf,cep,raca",
        "'$nome','$idade','$email','$telefone','$celular','$serie','$endereco','$bairro','$cidade','$sexo','$pai','$mae','$nascimento','$cpf','$rg','$responsavel','$responsavelrg','$responsavelcpf','$cep','$raca'"
    );

    $consulta_vagas = mysqli_query($con->connect(), "SELECT id FROM colaborador ORDER BY id DESC LIMIT 1");
    $campo = mysqli_fetch_assoc($consulta_vagas);
    echo $campo['id'];

    $crud = new crud('pend_cad');
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",1,0");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",2,0");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",3,0");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",4,0");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",5,1");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",6,1");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",7,1");
    $crud->inserir("id_colaborador,id_campo,pendencia", $campo['id'] . ",8,1");
    $crud = new crud('ano_projeto');
    $crud->inserir(
        "ano,tipo,id_colaborador,situacao,id_projeto",
        "'$anodata','C','" . $campo['id'] . "','PENDENTE','" . $id_projeto . "' "
    );
    exit();
}

include_once 'header.php';
?>


<main class="container mt-5 pt-5">
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
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">Cadastro</div>
        <div class="card-body form-section">
            <form action="" method="post" id="inscricaoAtor" name="inscricaoAtor">
                <input type="hidden" name="action" value="cadastrar" />
                <input type="hidden" name="id_projeto" value="<?= $id_projeto ?>" />
                <div class="row g-3" id="formColaborador">
                    <div class="col-md-6">
                        <label class="form-label">Nome*</label>
                        <input type="text" name="nome" maxlength="250" class="form-control" style="text-transform: uppercase;" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Data de Nascimento*</label>
                        <input type="date" id="datanascimento" name="nascimento" class="form-control" onblur="getAge()" />
                    </div>
                    <div class="col-md-6" id="documento_rg">
                        <label class="form-label">CPF*</label>
                        <input type="text" name="cpf" id="cpf" maxlength="14" onkeyup="handleCPF(event)" placeholder="000.000.000-00" class="form-control" />
                    </div>
                    <div class="col-md-6" id="documento_rg">
                        <label class="form-label">RG* (ex: 9.999.999 SDS PE)</label>
                        <input type="text" name="rg" maxlength="250" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp*</label>
                        <input type="text" name="telefone" maxlength="15" onkeyup="handlePhone(event)" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Celular*</label>
                        <input type="text" name="celular" maxlength="15" onkeyup="handlePhone(event)" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gênero*</label>
                        <select name="sexo" class="form-control">
                            <option value="<?php echo @$campo['sexo']; ?>" selected><?php echo @$campo['sexo']; ?></option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                            <option value="LGBTQIAPN+">LGBTQIAPN+</option>
                            <option value="Não Informado">Não Informar</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Raça*</label>
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
                    <div class="col-md-6">
                        <label class="form-label">Cidade</label>
                        <input type="text" name="cidade" maxlength="250" value="<?php echo @$campo['cidade']; ?>" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="text" name="email" maxlength="250" value="<?php echo @$campo['email']; ?>" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="endereco" maxlength="250" value="<?php echo @$campo['endereco']; ?>" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Escolaridade</label>
                        <input type="text" name="serie" maxlength="250" value="<?php echo @$campo['serie']; ?>" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bairro</label>
                        <input type="text" name="bairro" maxlength="250" value="<?php echo @$campo['bairro']; ?>" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CEP</label>
                        <input type="text" name="cep" maxlength="250" value="<?php echo @$campo['cep']; ?>" class="form-control" />
                    </div>

                    <div class="col-12 outrasInformacoes" id="outrasInformacoes">
                        <span class="outrotitle">INFORMAÇÕES PARA MENOR DE IDADE</span>
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome do Pai</label>
                                <input type="text" name="pai" maxlength="250" style="text-transform: uppercase;" class="form-control" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nome da Mãe</label>
                                <input type="text" name="mae" maxlength="250" style="text-transform: uppercase;" class="form-control" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nome do Responsável</label>
                                <input type="text" name="responsavel" maxlength="250" class="form-control" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">RG do Responsável</label>
                                <input type="text" name="responsavelrg" maxlength="250" class="form-control" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CPF do Responsável*</label>
                                <input type="text" name="responsavelcpf" id="responsavelcpf" maxlength="14" onkeyup="handleCPF(event)" placeholder="000.000.000-00" class="form-control" />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-center mt-4">
                        <input type="button" class="btn btn-success" onclick="salvarCadastro()" name="cadastrar" value="SALVAR" />
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

<footer class="container text-center py-3">
    <p class="text-muted">© Companhia 2017-2025</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="tts.js"></script>
<script language="JavaScript">
    function getAge() {
        let dataNascimento = document.getElementById('datanascimento');
        const today = new Date();
        const birthDate = new Date(dataNascimento.value.split('/').reverse().join('/'));
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
                    cpf: cpf,
                    action: 'valida_cpf'
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    console.log(response);
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
        $('#cpf').on('blur', function() {
            var cpf = $(this).val();
            validarCPF(cpf);
        });
    });

    function salvarCadastro() {
        if (getAge() == false) {
            return false;
        }
        var $form = $(this);
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
        console.log(retorno);

        $.ajax({
            url: "inscricao.php",
            data: serializedData,
            type: "POST",
            success: function(data) {
                falar('Seu cadastro foi efetuado com sucesso, clique no botão para subir a foto de sua documentação!');
                document.getElementById('formColaborador').style.display = 'none';
                document.getElementById('sucesso').style.display = 'block';
                document.getElementById('botaoDocumentos').innerHTML = "<a class='btn btn-primary' href='form_foto_documentacao.php?id=" + data + "'>Subir fotos da documentação</a>";
                console.log(data);
            },
            fail: function(data) {
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
        const voice = speaks[0];
        voice.voiceURI = voice.name;
        msg.lang = voice.lang;
        speechSynthesis.speak(msg);
    }

    function enviardados() {
        if (document.inscricaoAtor.telefone.value == "" || document.inscricaoAtor.telefone.value.length < 8) {
            falar('Preencha o número de WhatsApp corretamente!');
            alert("Preencha o número de WhatsApp corretamente!");
            document.dados.telefone.focus();
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