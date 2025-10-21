<?php


require_once 'config/conexao.class.php';
require_once 'config/crud.class.php';
$con = new conexao(); // instancia classe de conxao
$con->connect(); // abre conexao com o banco

$anodata = date('Y');
$data = date('Y-m-d h:i:s');
if ($_GET['action'] == "consultaNome") {
	$consulta_vagas = mysqli_query($con->connect(), "SELECT c.id as id,
	 UPPER(c.nome) as nome 
        FROM colaborador c
        JOIN ano_projeto a ON c.id = a.id_colaborador
        GROUP BY a.id_colaborador
        ORDER BY nome ASC");

	while ($campo = mysqli_fetch_array($consulta_vagas)) {
		echo $campo['id'] . "|" . $campo['nome'] . ";";
	}
}


if ($_GET['action'] == "valida_renovacao") {


	$id_projeto = $_GET['projeto'];
	if (empty($_GET['id_colaborador'])) {
		$partes = explode("|", $_GET['nome']);
		$id_colaborador = $partes[0];
	} else {
		$id_colaborador = $_GET['id_colaborador'];
	}


	$consulta_colaborador = mysqli_query($con->connect(), "SELECT * FROM colaborador where id = '" . $id_colaborador . "' || nome = '" . $id_colaborador . "'"); // query que busca todos os dados da tabela PRODUTO
	$campo_consulta_colab = mysqli_num_rows($consulta_colaborador);
	$id_colaborador = 0;
	while ($campo_usuario = mysqli_fetch_array($consulta_colaborador)) {
		$id_colaborador = $campo_usuario['id'];
	}
	if ($campo_consulta_colab == 0) {
		echo "<script>
		alert('O Nome que você digitou não está cadastrado, por favor faça seu cadastro.');
		 window.history.back();
		</script>";
		exit();
	}

	$consulta_matricula = mysqli_query($con->connect(), "SELECT * FROM ano_projeto 
	where id_colaborador = '" . $id_colaborador . "' and id_projeto='$id_projeto' "); // query que busca todos os dados da tabela PRODUTO
	$campo_matricula = mysqli_num_rows($consulta_matricula);


	if ($campo_matricula > 0) {
		echo "<script>
		alert('Sua matrícula já tinha sido renovada anteriomente, verifique se há pendência de documentação.');
		location='form_foto_documentacao.php?id=" . $id_colaborador . "';
		</script>";
		exit();
	}

	$consulta_vagas = mysqli_query($con->connect(), "SELECT * FROM colaborador where id = upper('" . $id_colaborador . "') order by id limit 1"); // query que busca todos os dados da tabela PRODUTO
	$campo = mysqli_fetch_array($consulta_vagas);
	$id_colaborador = $campo['id'];

	$crud = new crud('ano_projeto');
	$crud->inserir("ano, situacao, id_colaborador, tipo, id_projeto", "'" . $anodata . "', 'PENDENTE', '" . $campo['id'] . "', 'C','" . $id_projeto . "' ");

	$crud = new crud('pend_cad');
	$crud->atualizar("pendencia='1',data='$data'", "id_colaborador='$id_colaborador' and id_campo in(3,6)");


	echo "<script>
				  alert('Matriculado com sucesso!');
				  location='form_foto_documentacao.php?id=" . $id_colaborador . "&projeto=" . $id_projeto . "';
				  </script>";
}


if ($_GET['action'] == "valida_cpf") {
	$cpf = $_GET['cpf'];
	$consulta_cpf = mysqli_query($con->connect(), "SELECT * FROM colaborador WHERE cpf = '$cpf' LIMIT 1");

	if (mysqli_num_rows($consulta_cpf) > 0) {
		echo json_encode(['message' => 'existe']);
	} else {
		echo json_encode(['message' => 'noexiste']);
	}
}
