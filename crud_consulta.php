<?php
//crud_consulta.php

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

if ($_GET['action'] === 'valida_renovacao') {

    header('Content-Type: application/json; charset=utf-8');

    function resp($ok, $msg, $redirect = '') {
        echo json_encode([
            'ok' => $ok,
            'msg' => $msg,
            'redirect' => $redirect
        ]);
        exit();
    }

    $id_projeto     = (int)($_GET['projeto'] ?? 0);
    $id_colaborador = (int)($_GET['id_colaborador'] ?? 0);
    $nascimento_in  = trim($_GET['data_nascimento'] ?? '');

    if ($id_projeto <= 0) {
        resp(false, 'Projeto inválido.');
    }

    if ($id_colaborador <= 0) {
        resp(false, 'Cadastro não encontrado.', "inscricao.php?novo=1&id_projeto=$id_projeto");
    }

    // busca colaborador
    $q = mysqli_query(
        $con->connect(),
        "SELECT nascimento FROM colaborador WHERE id = $id_colaborador LIMIT 1"
    );

    if (!$q || mysqli_num_rows($q) === 0) {
        resp(false, 'Colaborador não encontrado.', "inscricao.php?novo=1&id_projeto=$id_projeto");
    }

    $c = mysqli_fetch_assoc($q);
    $nascimento_bd = date('Y-m-d', strtotime($c['nascimento']));

    if ($nascimento_bd !== $nascimento_in) {
        resp(false, 'Data de nascimento não confere.');
    }

    $ano = date('Y');

    // evita duplicidade
    $existe = mysqli_query(
        $con->connect(),
        "SELECT 1 FROM ano_projeto
         WHERE id_colaborador = $id_colaborador
           AND id_projeto = $id_projeto
           AND ano = '$ano'
         LIMIT 1"
    );

    if (mysqli_num_rows($existe) > 0) {
        resp(
            false,
            'Sua matrícula já foi renovada.',
            "form_foto_documentacao.php?id=$id_colaborador&projeto=$id_projeto"
        );
    }

    // insere renovação
    $crud = new crud('ano_projeto');
    $crud->inserir(
        "ano, situacao, id_colaborador, tipo, id_projeto",
        "'$ano','PENDENTE',$id_colaborador,'C',$id_projeto"
    );

    // ativa pendências iniciais
    $data = date('Y-m-d H:i:s');
    $crud = new crud('pend_cad');
    $crud->atualizar(
        "pendencia=1, data='$data'",
        "id_colaborador=$id_colaborador AND id_campo IN (3,6)"
    );

    resp(
        true,
        'Matrícula renovada com sucesso!',
        "form_foto_documentacao.php?id=$id_colaborador&projeto=$id_projeto"
    );
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
