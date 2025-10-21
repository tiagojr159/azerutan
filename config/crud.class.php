<?php
//error_reporting(0);
/** Classe CRUD - Create, Recovery, Update and Delete
 * @author - Tiago Juniot
 * @date - 25/08/2014
 * Arquivo - codigo.class.php
 * @package crud
 * Dedico este trabalho ao nosso senhor Jesus Crusto, que eu amo, adoro e entrego minha vida
 */
require_once 'config/conexao.class.php';

class crud
{

    //$const = New Constats();
    private $sql_ins = "";
    private $tabela = "";
    private $sql_sel = "";
    private $con = "";




    // Caso pretendamos que esta classe seja herdada por outras, então alguns atrubutos podem ser protected

    /** Método construtor
     * @method __construct
     * @param string $tabela
     * @return $this->tabela
     */
    public function __construct($tabela)
    { // construtor, nome da tabela como parametro
        $this->tabela = $tabela;
        $con = new conexao();
        $this->con = $con->connect();
        //var_dump($this->con);
        //return $this->tabela;
    }


    public function log($param)
    {
        $date = date("Y-m-d H:i:s");
        $f = fopen("config/log.txt", "a");
        fwrite($f, "
            

        " . $date . "	
        " . $param);
        fclose($f);
    }

    /** Método inserir
     * @method inserir
     * @param string $campos
     * @param string $valores
     * @example: $campos = "codigo, nome, email" e $valores = "1, 'João Brito', 'joao@joao.net'"
     * @return void
     */
    public function inserir($campos, $valores)
    { // funçao de inserçao, campos e seus respectivos valores como parametros
        //include'restrict.php';    
        $this->sql_ins = "INSERT INTO " . $this->tabela . " ($campos) VALUES ($valores)";
        $crud = new crud('');
        $crud->log($this->sql_ins);
        if (!$this->ins = mysqli_query($this->con, $this->sql_ins)) {
            die("<center>Erro na inclusão " . '<br>Linha: ' . __LINE__ . "<br>" . mysqli_error($this->con) . "<br>
			" . $this->sql_ins . "
				<a href='index.php'>Voltar ao Menu</a></center>");
        } else {
            //    $pieces = explode("_", $this->tabela);
            //print "<script>location='index.php?tela=" . $pieces[1] . "';</script>";
        }


    }

    public function inserir_void($campos, $valores)
    { // funçao de inserçao, campos e seus respectivos valores como parametros
        //include'restrict.php';    
        $this->sql_ins = "INSERT INTO " . $this->tabela . " ($campos) VALUES ($valores)";
        $crud = new crud();
        $crud->log($this->sql_ins);
        if (!$this->ins = mysqli_query($this->con, $this->sql_ins)) {
            die("<center>Erro na inclusão " . '<br>Linha: ' . __LINE__ . "<br>" . mysqli_error() . "<br>
				<a href='index.php'>Voltar ao Menu</a></center>");
        }

    }

    public function atualizar($camposvalores, $where = NULL)
    { // funçao de ediçao, campos com seus respectivos valores e o campo id que define a linha a ser editada como parametros
        //include'restrict.php';  

        if ($where) {
            $this->sql_upd = "UPDATE  " . $this->tabela . " SET $camposvalores WHERE $where";
        } else {
            $this->sql_upd = "UPDATE  " . $this->tabela . " SET $camposvalores";
        }

        if (!$this->upd = mysqli_query($this->con, $this->sql_upd)) {
            die("<center>Erro na atualização " . "<br>Linha: " . __LINE__ . "<br>" . mysqli_error() . "<br>
				<a href='index.php'>Voltar ao Menu</a></center>");
        } else {
            $pieces = explode("_", $this->tabela);
            //  print "<script>location='index.php?tela=" . $pieces[1] . "';</script>";
        }
    }

    /** Método excluir
     * @method excluir
     * @param string $where
     * @example: $where = " codigo=2 AND nome='João' "
     * @return void
     */
    public function excluir($where = NULL)
    { // funçao de exclusao, campo que define a linha a ser editada como parametro
        include 'restrict.php';
        if ($where) {
            $this->sql_sel = "SELECT * FROM " . $this->tabela . " WHERE $where";
            $this->sql_del = "DELETE FROM " . $this->tabela . " WHERE $where";
        } else {
            $this->sql_sel = "SELECT * FROM " . $this->tabela;
            $this->sql_del = "DELETE FROM " . $this->tabela;
        }
        $crud = new crud('');
        $crud->log($this->sql_del);

        $sel = mysqli_query($this->con, $this->sql_sel);
        $regs = mysqli_num_rows($sel);

        if ($regs > 0) {
            if (!$this->del = mysqli_query($this->con, $this->sql_del)) {
                die("<center>Erro na exclusão " . '<br>Linha: ' . __LINE__ . "<br>" . mysqli_error() . "<br>
				<a href='index.php'>Voltar ao Menu</a></center>");
            } else {
                $pieces = explode("_", $this->tabela);
                print "<script>location='index.php?tela=" . $pieces[1] . "';</script>";
            }
        } else {
            print "<center>Registro Não encontrado!<br><a href='index.php'>Voltar ao Menu</a></center>";
        }
    }

    public function login($where, $con)
    { // funçao de inserçao, campos e seus respectivos valores como parametros
        @session_start();
        $this->sql_sel = "select * from " . $this->tabela . " where $where ";

        $regs = mysqli_num_rows(mysqli_query($con, $this->sql_sel));

        if (!$regs > 0) {
            die("<center>Você Digitou a senha errada<br>
				<a href='index.php?tela=login'>Voltar ao Menu</a></center>");
        } else {
            $campo = mysqli_fetch_assoc(mysqli_query($this->con, $this->sql_sel));
            $_SESSION['user'] = $campo['user'];
            print "<script>location='index.php?tela=home';</script>";
        }
    }

}

?>