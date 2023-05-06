
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
            <h2 class="notopgap" align="center"><u><?php echo $txt["parloschen"];?></u></h2>
<?php


#$wrdat= $_GET['alter'];// Datum welches gelöscht werden soll
$jahr= $_GET['jahr'];	// Jahr welches gelöscht werden soll
$mon= $_GET['mon'];		// Monat welches gelöscht werden soll
$tag= $_GET['tag'];		// Tag welcher gelöscht werden soll
$wr= $_GET['wr'];		// Wechselrichter welcher gelöscht werden soll
#$monstop = $mon;
$fertig="0";

#if ($wrdat == "" ) 	$wrdat= $jahr;

if ($wr == "" or $jahr == "") 
{
	#include "par_menu.php";
	echo $txt["parloscheninput1"]. $jahr."<br>";
	echo $txt["parloscheninput2"].$wr."<br>"."<br>";	
	echo $txt["parloschenfehler"]."<br>"."<br>";
	
	echo <<<HTML
<a href="loschen.php"> Zurück</a>
HTML;
	exit;
}

#if ($wrdat == "" ) {
	$wrdat= $jahr."-".$mon."-".$tag;
		if ($mon =="") $wrdat= $jahr."-";
#}


	echo $txt["parloscheninput1"]. $wrdat."<br>";
	echo $txt["parloscheninput2"]. $wr."<br>"."<br>";	
?>
<?php 
$tabelle = $prefix."_dag";
$tabellemonat = $prefix."_maand";
##################################
if ($mon == "" and $fertig == 0){
		$delstring="DELETE FROM $tabelle WHERE Datum_Dag LIKE '$wrdat%' AND Naam like '$wr'";
	
	//mysqli_query($con, "DELETE FROM " . $table_prefix . "_maand WHERE Naam ='" . $wr . "' AND Datum_Maand='" . $datum00 . "'")
	
	$delstringmaand="DELETE FROM $tabellemonat WHERE Datum_Maand like '$wrdat%' AND Naam= '$wr'";
	
	//echo "Eintrag ".$delstring." wird gelöscht"."<br>"."<br>";
	//echo "Eintrag ".$delstringmaand." wird gelöscht"."<br>"."<br>";
	###### Löschen ######
	mysqli_query($con, $delstring) or die("Eintrag Tag konnte nicht gelöscht werden!! -> ".$delstring." <--" . mysqli_error($delstring));
	mysqli_query($con, $delstringmaand) or die("Eintrag Tag konnte nicht gelöscht werden!! -> ".$delstring." <--" . mysqli_error($delstring));
	$deldat = " Jahr";
	$fertig = "1";
}
#####################################
if ($tag == "" and $fertig == 0){
	$delstring="DELETE FROM $tabelle WHERE Datum_Dag LIKE '$wrdat%' AND Naam like '$wr'";
	
	//mysqli_query($con, "DELETE FROM " . $table_prefix . "_maand WHERE Naam ='" . $wr . "' AND Datum_Maand='" . $datum00 . "'")
	
	$delstringmaand="DELETE FROM $tabellemonat WHERE Datum_Maand like '$wrdat%' AND Naam= '$wr'";
	
	//$gelöscht.=$wrdat." , ";
	//echo "Eintrag ".$delstring." wird gelöscht"."<br>"."<br>";
	//echo "Eintrag ".$delstringmaand." wird gelöscht"."<br>"."<br>";
	###### Löschen ######
	mysqli_query($con, $delstring) or die("Eintrag Tag konnte nicht gelöscht werden!! -> ".$delstring." <--" . mysqli_error($delstring));
	mysqli_query($con, $delstringmaand) or die("Eintrag Tag konnte nicht gelöscht werden!! -> ".$delstring." <--" . mysqli_error($delstring));
	$deldat = " Monat";
	$fertig = "1";
}
######################################################

if ($tag !== "" and $fertig == 0){
	#$delstring="DELETE FROM ".$prefix."_dag WHERE Datum='".$wrdat." ".$wr."'";
	
	$delstring="DELETE FROM $tabelle WHERE Datum_Dag LIKE '$wrdat%' AND Naam like '$wr'";
	
	//mysqli_query($con, "DELETE FROM " . $table_prefix . "_maand WHERE Naam ='" . $wr . "' AND Datum_Maand='" . $datum00 . "'")
	
	$delstringmaand="DELETE FROM $tabellemonat WHERE Datum_Maand like '$wrdat%' AND Naam= '$wr'";
	
	//echo "Eintrag ".$delstring." wird gelöscht"."<br>"."<br>";
	//echo "Eintrag ".$delstringmaand." wird gelöscht"."<br>"."<br>";
	###### Löschen ######
	mysqli_query($con, $delstring) or die("Eintrag Tag konnte nicht gelöscht werden!! -> ".$delstring." <--" . mysqli_error($delstring));
	mysqli_query($con, $delstringmaand) or die("Eintrag Monat konnte nicht gelöscht werden!! -> ".$delstring." <--" . mysqli_error($delstring));
	$deldat = " Tag";
	$fertig = "1";
}


echo "<br>"."<br>"."<br>"."<br>";
echo $txt["parloschenready"]. $deldat ."<br>";
echo $txt["omvormer"].$wr."<br>";
echo $txt["verbruikdatum"].$wrdat."<br>"."<br>";
?>
        </div>
    </div>
</div>







			<?php 
			/*
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
			*/
			?>

