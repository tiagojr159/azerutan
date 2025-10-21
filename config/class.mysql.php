<?php

class Mysql{
 
 /*
   var $servidor = 'localhost'; // servidor
    private $db_user = 'ki6_ki6'; // usuario do banco
    private $db_pass = 'policarpo12!@'; // senha do usuario do banco
    private $db_name = 'ki6_sagradafamiliaigarassu'; // nome do banco
*/
/*
	var $servidor = 'localhost';
	var $banco = 'ki6_sagradafamiliaigarassu';
	var $usuario = 'ki6_ki6';
	var $senha = 'policarpo12!@';
*/

	var $servidor = 'localhost';
	var $banco = 'paixao';
	var $usuario = 'root';
	var $senha = '';

/*
	var $servidor = 'localhost';  // host do mysql
	var $senha = 'recife12';     // senha do usu?rio
	var $usuario = 'a1336446_anon';       // usu?rio
	var $banco = 'a1336446_anon'; // nome da base de dados
	*/	
	
	
	var $conexao;
	var $consulta;
	var $resultado;
	var $total_registros = 0;
	//metodo construtor
	function Mysql(){
    }
	//conectar no banco de dados
	function Conecta(){
		$this->conexao = @mysql_connect($this->servidor,$this->usuario,$this->senha); //varaivel link desntro desta classa recebera a conex�o
		if(!$this->conexao){
			echo 'Falha na conex�o com o banco de dados<br>';
			exit();
		}elseif(!mysql_select_db($this->banco,$this->conexao)){
			echo 'Falha ao selecionar o banco de dados<br>';
			exit();
		}
	}
	//realizar consulta sql
	function Consulta($query){
		$this->Conecta();
		$this->consulta = $query;
		if($this->resultado = @mysql_query($this->consulta)){
			$this->Desconecta();
			return $this->resultado;
		}else{
			$this->Desconecta();
			echo 'Erro ao realizar consulta';
			exit();
		}
	
	}
	//total de registros
	function Totalreg($query){
		$this->total_registros = $this->Consulta($query);
		$this->total_registros = mysqli_fetch_array($this->total_registros);
		return $this->total_registros[0];
	}
	//fechar a conex�o
	function Desconecta(){
		return mysql_close($this->conexao);
	}
}
?>