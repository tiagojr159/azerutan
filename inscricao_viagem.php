
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

   <body background="aaa.jpg">

	<center>
     
		<font face=arial size=2>
	<table border='0' bordercolor='#000' bgcolor="white" cellspacing='10' cellpadding='0' width=600>
	<tr>
        <td COLSPAN=5>
		<img src="topo_paixao.jpg" width="700">



<script	type="text/javascript" src="jquery.js" charset="UTF-8"></script>
<script	type="text/javascript" src="js/jquery.autocomplete.js" charset="UTF-8"></script>
<script	type="text/javascript" src="js/autocompletar.js" charset="UTF-8"></script>
<link href="styles-autocomplete.css" rel="stylesheet" />
	
       

<?php
/*
 *   Ofereço a Deus todos esses código que escrevi como fruto do 
 * meu trabalho e por intercessão de São Isodoro de Servilha e 
 * São Jose  Maria Escrivá esses sistema nunca seja usado para o mau 
 * ou desagrado do nosso senhor Jesus Cristo. Amém.  
 * 
 * Tiago Junior - 31/08/2014
 */
//include 'restrict.php';
require_once 'config/conexao.class.php';
require_once 'config/crud.class.php';
//error_reporting(0);
$con = new conexao(); // instancia classe de conxao
$con->connect(); // abre conexao com o banco



function calc_idade($data_nasc) {
		if(!empty($data_nasc)){
			$data_nasc=explode('/',$data_nasc);
			$data=date('d/m/Y');
			$data=explode('/',$data);
			$anos=$data[2]-$data_nasc[2];
			if($data_nasc[1] > $data[1]){
			return $anos-1;
			}

			if($data_nasc[1] == $data[1]){
				if($data_nasc[0] <= $data[0]) {
				return $anos;
				//break;
				}
			}else{
				return $anos-1;
				//break;
			}

			if ($data_nasc[1] < $data[1]){
			return $anos;
			}
		}
	}











$anodata = date('Y');
$consulta_vagas = mysqli_query($con->connect(), "SELECT * FROM colaborador c, ano_projeto a where c.id = a.id_colaborador
and a.situacao = 'SIM' and a.ano = ".$anodata."  ORDER BY  nome asc limit 220 "); // query que busca todos os dados da tabela PRODUTO
$num_rows_consulta_vagas = mysqli_num_rows($consulta_vagas);


$consulta_vagas_remanejamento = mysqli_query($con->connect(), "ELECT * FROM colaborador c, ano_projeto a where c.id = a.id_colaborador
and a.situacao = 'SIM' and a.ano = ".$anodata."  ORDER BY  nome asc limit 220"); // query que busca todos os dados da tabela PRODUTO
$consulta_vagas_remanejamento = mysqli_num_rows($consulta_vagas);


?>

<br><br>


		
<br>
<br><br>



				
			<?php
$anodata = date('Y');
$texto ="<center>
		<br><br><br><b><font FACE=''  color='#ff0000'  size=4>Viagem para Nova Jelusalem<font></b>
		<br>
		<br>
		<table border='1' bordercolor='blue'  width='680' cellspacing='0' cellpadding='0'>	";
$consulta = mysqli_query($con->connect(),"SELECT * FROM colaborador c, evento e where c.id = e.id_colaborador and e.ano = ".$anodata."  group by c.id ORDER BY  nome asc limit 220 "); // query que busca todos os dados da tabela PRODUTO
$total = 0;
while ($campo = mysqli_fetch_array($consulta)) { // laço de repetiçao que vai trazer todos os resultados da consulta
	$idade = $campo['idade'];   
	$texto .= "<tr><td><font color='blue'>".$campo['id']." - ".strtoupper($campo['nome'])."</font>";
	
	if(calc_idade($campo['nascimento']) >= 18){
		$texto .= "";
	}else{
		$texto .= " - ". calc_idade($campo['nascimento']) . " anos";
	}
	

	$texto .= "</td></tr>";
	$total++;
}
$texto .= "</table></CENTER>";
$texto .= "<font color='#ff0000'>Total: ".$total."<font>";
echo $texto;
		?>			
						
				
<br><br>
<br><br>
	
						
						
						
						
						
						
						
						
						
				
						
						
						
						
						
						
						
						
						<tr>
        <td COLSPAN=5>
		<img src="azerutan_rodape2.jpg" width="700">
</tr>		
	
	
</table>
		


		
		
		



              