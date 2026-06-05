<?php
//session_start(); //Do not remove this
error_reporting(E_ALL);

?>

 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />






	<center>
     
		<font face=arial size=2>
	<table border='0' bordercolor='#000' bgcolor="white" cellspacing='10' cellpadding='0' width=600>
	<tr>
        <td COLSPAN=5>
		<img src="topo_paixao.jpg" width="700">








<?php
//error_reporting(E_ALL ^ E_NOTICE);


//echo $_SESSION['id_colaborador']."ppppppppppppppppp";
    require_once 'config/conexao.class.php';
    require_once 'config/crud.class.php';


$con = new conexao();
$con->connect();
@$getId = $_GET['id'];
if (@$getId) {
    $consulta =mysqli_query($con->connect(),"SELECT * FROM foto_colaborador WHERE id = + $getId");
    $campo = mysqli_fetch_array($consulta);
}

if (isset($_POST['cadastrar'])) {
    $id_colaborador = $_SESSION['id_colaborador'];
    $photo = $_POST['photo'];
    $crud = new crud('foto_colaborador');
    $crud->inserir("id_colaborador,foto", "'$id_colaborador','$photo'");
    $_SESSION['id_colaborador'] = 0 ;
    $_SESSION['tela_post'] = "";
   echo "<script>location='index.php?tela=colaborador';</script>";
}




if(isset($_GET['id_colaborador'])){
    $_SESSION['id_colaborador'] = $_GET['id_colaborador'] ;
    $_SESSION['tela_post'] = $_GET['tela_post'];
}

//echo $_SESSION['id_colaborador'].$_SESSION['tela_post'];


if (!isset($_SESSION['random_key']) || strlen($_SESSION['random_key']) == 0) {
    $_SESSION['random_key'] = strtotime(date('Y-m-d H:i:s')); //assign the timestamp to the session variable
}


$upload_dir = "../projeto/upload_pic";     // The directory for the images to be saved in
$upload_path = $upload_dir . "/";    // The path to where the image will be saved
$large_image_prefix = "resize_";    // The prefix name to large image
$thumb_image_prefix = "thumbnail_";   // The prefix name to the thumb image
$large_image_name = $large_image_prefix . $_SESSION['random_key'] . ".jpg";     // New name of the large image (append the timestamp to the filename)
$thumb_image_name = $thumb_image_prefix . $_SESSION['random_key'] . ".jpg";     // New name of the thumbnail image (append the timestamp to the filename)
$max_file = "2148576";       // Approx 1MB
$max_width = "500";       // Max width allowed for the large image
$thumb_width = "100";      // Width of thumbnail image
$thumb_height = "100";      // Height of thumbnail image
//Image functions
//You do not need to alter these functions

function resizeImage($image, $width, $height, $scale) {
    $newImageWidth = ceil($width * $scale);
    $newImageHeight = ceil($height * $scale);
    $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
    $source = imagecreatefromjpeg($image);
    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newImageWidth, $newImageHeight, $width, $height);
    imagejpeg($newImage, $image, 90);
    chmod($image, 0777);
    return $image;
}

//You do not need to alter these functions
function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale) {
    $newImageWidth = ceil($width * $scale);
    $newImageHeight = ceil($height * $scale);
    $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
    $source = imagecreatefromjpeg($image);
    imagecopyresampled($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);
    imagejpeg($newImage, $thumb_image_name, 90);
    chmod($thumb_image_name, 0777);
    return $thumb_image_name;
}

//You do not need to alter these functions
function getHeight($image) {
    $sizes = getimagesize($image);
    $height = $sizes[1];
    return $height;
}

//You do not need to alter these functions
function getWidth($image) {
    $sizes = getimagesize($image);
    $width = $sizes[0];
    return $width;
}

//Image Locations
$large_image_location = $upload_path . $large_image_name;
$thumb_image_location = $upload_path . $thumb_image_name;

//Create the upload directory with the right permissions if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777);
    chmod($upload_dir, 0777);
}

//Check to see if any images with the same names already exist
if (file_exists($large_image_location)) {
    if (file_exists($thumb_image_location)) {
        $thumb_photo_exists = "<img src=\"" . $upload_path . $thumb_image_name . "\" alt=\"Thumbnail Image\" width=\"300\"    />";
    } else {
        $thumb_photo_exists = "";
    }
    $large_photo_exists = "<img src=\"" . $upload_path . $large_image_name . "\" alt=\"Large Image\"/>";
} else {
    $large_photo_exists = "";
    $thumb_photo_exists = "";
}

if (isset($_POST["upload"])) {
    //Get the file information
    $userfile_name = $_FILES['image']['name'];
    $userfile_tmp = $_FILES['image']['tmp_name'];
    $userfile_size = $_FILES['image']['size'];
    $filename = basename($_FILES['image']['name']);
    $file_ext = substr($filename, strrpos($filename, '.') + 1);

    //Only process if the file is a JPG and below the allowed limit
    if ((!empty($_FILES["image"])) && ($_FILES['image']['error'] == 0)) {
        if (($file_ext != "jpg") || ($userfile_size > $max_file)) { // UPDATED ERROR CHECK
            $error = "ONLY jpeg images under 1MB are accepted for upload";
        }
    } else {
        $error = "Select a jpeg image for upload";
    }
    //Everything is ok, so we can upload the image.
    if (strlen($error) == 0) {

        if (isset($_FILES['image']['name'])) {

            move_uploaded_file($userfile_tmp, $large_image_location);
            chmod($large_image_location, 0777);

            $width = getWidth($large_image_location);
            $height = getHeight($large_image_location);
            //Scale the image if it is greater than the width set above
            if ($width > $max_width) {
                $scale = $max_width / $width;
                $uploaded = resizeImage($large_image_location, $width, $height, $scale);
            } else {
                $scale = 1;
                $uploaded = resizeImage($large_image_location, $width, $height, $scale);
            }
            //Delete the thumbnail file so the user can create a new one
            if (file_exists($thumb_image_location)) {
                unlink($thumb_image_location);
            }
        }
        //Refresh the page to show the new uploaded image
        // header("location:" . $_SERVER["PHP_SELF"]);
        echo"<script>window.location='form_foto_colaborador.php';</script>";
        exit();
    }
}






	//Get the new coordinates to crop the image.

	if($_POST["x1"]=="" || $_POST["y1"] =="" || $_POST["x2"]=="" || $_POST["y2"]=="" || $_POST["w"] =="" || $_POST["h"]==""){
		$x1 = 0;
		$y1 = 0;
		$x2 = 400;
		$y2 = 400;
		$w = 400;
		$h = 400;
	}else{
		$x1 = $_POST["x1"];
		$y1 = $_POST["y1"];
		$x2 = $_POST["x2"];
		$y2 = $_POST["y2"];
		$w = $_POST["w"];
		$h = $_POST["h"];
	}


	
	
	$desc = $_POST["att"];
	$scale = $thumb_width / $w;
	$cropped = resizeThumbnailImage($thumb_image_location, $large_image_location, $w, $h, $x1, $y1, $scale);

	echo"<script>window.location='form_foto_colaborador.php';</script>";
	exit();


if ($_GET['a'] == "delete" && strlen($_GET['t']) > 0) {
//get the file locations 
    $large_image_location = $upload_path . $large_image_prefix . $_GET['t'] . ".jpg";
    $thumb_image_location = $upload_path . $thumb_image_prefix . $_GET['t'] . ".jpg";
    if (file_exists($large_image_location)) {
        unlink($large_image_location);
    }
    if (file_exists($thumb_image_location)) {
        unlink($thumb_image_location);
    }
    //  header("location:" . $_SERVER["PHP_SELF"]);
    echo"<script>window.location='form_foto_colaborador.php';</script>";
    exit();
}
?>






<script language="JavaScript" src="js/jquery00.js" type="text/javascript"></script>
<script language="JavaScript" src="js/jquery01.js" type="text/javascript"></script>

<script type="text/javascript" src="js/jquery-pack.js"></script>
<script type="text/javascript" src="js/jquery.imgareaselect.min.js"></script>












<?php
//Only display the javacript if an image has been uploaded
if (strlen($large_photo_exists) > 0) {
    $current_large_image_width = getWidth($large_image_location);
    $current_large_image_height = getHeight($large_image_location);
    ?>
    <script type="text/javascript">
        function preview(img, selection) { 
            var scaleX = <?php echo $thumb_width; ?> / selection.width; 
            var scaleY = <?php echo $thumb_height; ?> / selection.height; 
                	
            $('#thumbnail + div > img').css({ 
                width: Math.round(scaleX * <?php echo $current_large_image_width; ?>) + 'px', 
                height: Math.round(scaleY * <?php echo $current_large_image_height; ?>) + 'px',
                marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px', 
                marginTop: '-' + Math.round(scaleY * selection.y1) + 'px' 
            });
            $('#x1').val(selection.x1);
            $('#y1').val(selection.y1);
            $('#x2').val(selection.x2);
            $('#y2').val(selection.y2);
            $('#w').val(selection.width);
            $('#h').val(selection.height);
        } 

        $(document).ready(function () { 
            $('#save_thumb').click(function() {
                var x1 = $('#x1').val();
                var y1 = $('#y1').val();
                var x2 = $('#x2').val();
                var y2 = $('#y2').val();
                var w = $('#w').val();
                var h = $('#h').val();
                if(x1=="" || y1=="" || x2=="" || y2=="" || w=="" || h==""){
                    alert("You must make a selection first");
                    return false;
                }else{
                    return true;
                }
            });
        }); 

        $(window).load(function () { 
            $('#thumbnail').imgAreaSelect({ aspectRatio: '1:<?php echo $thumb_height / $thumb_width; ?>', onSelectChange: preview }); 
        });

    </script>
<?php } ?>
<?php
//Display error message if there are any 
$id = $_GET['id'];

if (empty($_GET['id'])) {
    
} else {
    $_SESSION['id_cadastrado'] = $_GET['id'];
}
$id3 = $_SESSION['id_cadastrado'];


if (strlen($error) > 0) {
    echo "<ul><li><strong>Error!</strong></li><li>" . $error . "</li></ul>";
}
if (strlen($large_photo_exists) > 0 && strlen($thumb_photo_exists) > 0) {


    echo "<center>Sua Foto Foi Salva com sucesso.<center>" . $thumb_photo_exists . "</center>";
    echo" <br/>
                                            <form action='form_foto_colaborador.php' method='post'>
                                            <input type='hidden' value='$thumb_image_name' name='photo'>
                                            
                                        <table border=0>
                                       
                                        <tr>
                                          <td></td>
                                          <td><input type='submit' name='cadastrar' value='Salvar'/></td>
                                        </tr>
                                        </table>
                                            </form> ";

   // echo "<p><a href=\"" . $_SERVER["PHP_SELF"] . "?a=delete&t=" . $_SESSION['random_key'] . "\">Delete images</a></p>";

    //Clear the time stamp session
    $_SESSION['random_key'] = "";
} else {
    if (strlen($large_photo_exists) > 0) {
        ?>

        <center>
            <h2>Recorte a Imagem</h2>
            <img src="<?php echo $upload_path . $large_image_name; ?>" style="margin-right: 10px;" id="thumbnail" alt="Create Thumbnail" />


            <form name="thumbnail" action="<?php echo $_SERVER["PHP_SELF"]; ?>?tela=files" method="post">
                <input type="hidden" name="x1" value="" id="x1" />
                <input type="hidden" name="y1" value="" id="y1" />
                <input type="hidden" name="x2" value="" id="x2" />
                <input type="hidden" name="y2" value="" id="y2" />
                <input type="hidden" name="w" value="" id="w" />
                <input type="hidden" name="h" value="" id="h" />
                <input type="hidden" name="att" value="att"  />
                <br style="clear:both;"/>


                <input type="submit" name="upload_thumbnail" value="Recortar e Salvar" id="save_thumb" /><br/><br/><br/>
            </form>
        </center>
        <br style="clear:both;"/>
    <?php
    } else if (isset($id)) {

        $id = $_GET['id_colaborador'];
        $consulta =mysqli_query($con->connect(),"SELECT * FROM foto_colaborador where id = '$id' ORDER BY `date` DESC LIMIT  100"); // query que busca todos os dados da tabela PRODUTO
        $campo = mysql_fetch_assoc($consulta);
        $thumb_image_name = $campo['file'];
        $title = $campo['title'];
        $text = $campo['text'];

        echo" 
                                             <form action='form_foto_colaborador.php' method='post'>    
                                             <input name='id' value='$id' type='hidden'/>
                                             <input name='photo' value='$thumb_image_name' type='hidden'/>
                                             <center><img src='../projeto/upload_pic/$thumb_image_name'></center>
                                        <table border=0>
                                       
                                        <tr>
                                          <td></td>
                                          <td><input type='submit' name='editar' value='Editar'/></td>
                                        </tr>
                                        </table>


                                             </form> ";
    }
    ?>

    <br/><br/><br/><br/>
    <center>
        <h2>É obrigatório cadastrar uma foto</h2>
        <form name="photo" enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
            <input type="file" name="image" size="30" /> 
            <br/><input type="submit" name="upload" value="Enviar o Arquivo para recorte" />
        </form>
<?php } ?>                                   </center>                                             

<br/><br/><br/>











<tr>
        <td COLSPAN=5>
		<img src="azerutan_rodape2.jpg" width="700">
</tr>		
	
	
</table>
		


		