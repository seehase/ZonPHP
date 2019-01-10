<?php
include_once "../Parameters.php";
include_once "../inc/sessionstart.php";
include_once "../inc/connect.php";

// check if tables exists
$sqlpar = "SELECT *	FROM " . $table_prefix . "_parameters limit 1";
$result = mysqli_query($con, $sqlpar) or die(header('location:opstart_installatie.php?fout=table'));


$statoe = 1;

if (!isset($_SESSION['passok']))
   header('location:par_welcome.php');


include_once "par_header.php";

?>
<div id="menus">
	<?php include "par_menu.php";?>
</div> 

<div id="container">
    <div id="bodytextparm">
        <div class="inside">
            <h2 class="notopgap" align="center"><u><?php echo $txt["parupdate"];?></u></h2>

<?php 
$stringdelete="";
$zahler="";

#$datum= $_GET['datum'];
$tag= $_GET['dag'];
$mon= $_GET['maand'];
$jahr= $_GET['jaar'];
$zeit= $_GET['zeit'];
$wert= $_GET['wert'];
$TagesErtrag = $_GET['ertrag'];
#$tag= $_GET['tag'];
$wr= $_GET['wr'];

$datum= $jahr."-".$mon."-".$tag;

		#echo $wert."<br>";
#### Ändern des Deziamlkommas auf Dezimalpunkt ####
	$wert = str_replace(',', '.', $wert);
		#echo $wert."<br>";


if ($tag == "" or $mon == "" or $jahr == "" or $wr == "" or $wert == "") {
	#include "par_menu.php";	
	echo $txt["parupdatefehler"]."<br>"."<br>";
	echo <<<HTML
<a href="update.php"> Zurück</a>
HTML;
	exit;
	
}else{
	

If ($zeit == ""){
	############ Ändern der Monatsdaten funktioniert!!	seit 26.11.2017
	
	
	//echo "Es wurde keine Zeit angegeben, es werden nur die Monatsdaten geändert!"."<br>";
	echo $txt["parupdatezeitinfo1"]."<br>";
	$datum00 = $datum." 00:00:00";
	
	
	mysqli_query($con, "DELETE FROM " . $table_prefix . "_maand WHERE Naam ='" . $wr . "' AND Datum_Maand='" . $datum00 . "'") or die("Query failed. ERROR: " . mysqli_error($con));

	$string11 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $datum . $wr . "','" . $datum00 . "'," . $wert . ",'" . $wr . "')";
	#echo $string1."<br>";
	mysqli_query($con, $string11) or die("Query failed. ERROR: string11 " . mysqli_error($con));

echo "<br>"."<br>"."<br>"."<br>";
//echo "Folgende Werte wurden geändert: "."<br>"."<br>";
echo $txt["parupdateready"]."<br>"."<br>";
echo $txt["omvormer"].": ".$wr."<br>";
echo $txt["datum"].": ".$datum."<br>";
echo $txt["waarde"].": ".$wert."<br>"."<br>";

############ Ende Ändern der Monatsdaten funktioniert!!	
	}
	else{
	
		#echo "Es wurde eine Zeit angegeben = ".$zeit."<br>";
		#echo "Datum welches eingegeben wurde jjjj-mm-dd  = ". $datum."<br>";
		#echo "Wechselrichter welcher angegeben wurde = " .$wr."<br>"."<br>";	


			If ($TagesErtrag == ""){
					$TagesErtrag =0;
					echo "Es wurd kein Tagesertrag angegeben = " .$TagesErtrag."<br>"."<br>";
					}else{
						echo "Es wurd ein Tagesertrag angegeben = " .$TagesErtrag."<br>"."<br>";
						}	

				$zeit=$zeit.":00";
				$wrdat= $datum." ".$zeit;

				$stringdelete = "DELETE FROM ".$table_prefix."_dag WHERE IndexDag='".$wrdat.$wr."'";


				$string="insert into ".$prefix."_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";	
				$string.="('".$wrdat.$wr."','".$wrdat."','".$wert."','".$TagesErtrag."','".$wr."')";
						
						
						#echo "<br>";
						#echo "Stringdelete = ".$stringdelete."<br>";
						#echo "String = ".$string."<br>";
				mysqli_query($con,$stringdelete)or die ('SQL Error - Der Eintrag '.$wrdat.$wr.' wurde nicht gefunden!'. mysql_error($con));
				mysqli_query($con,$string)or die("Query failed. ERROR: string " . mysqli_error($con));
						#die ('SQL Error konnte nichts ändern!'. mysql_error($con));
						
				echo "<br>"."<br>"."<br>"."<br>";
				//echo "Folgende Werte wurden geändert: "."<br>"."<br>";
				echo $txt["parupdateready"]."<br>"."<br>";
				echo $txt["omvormer"]."; ".$wr."<br>";
				echo $txt["datum"].": ".$datum."<br>";
				echo $txt["parupdatetime"].": ".$zeit."<br>";
				echo $txt["waarde"].": ".$wert."<br>"."<br>";
				
		}

}

?>


            </div>

			<?php 
			function controledatum($date){
				$j_m_d=explode("-",$date);
				if(!isset($j_m_d[0]))	return false;
				if(!isset($j_m_d[1]))	return false;
				if(!isset($j_m_d[2]))	return false;
			 	If(!checkdate($j_m_d[1],$j_m_d[2],$j_m_d[0])){
					return false;
				}else {
					return true;
				}
			}
			?>

<?php #include "footer.php";?>