<?php
include_once "../Parameters.php";
include_once "../inc/sessionstart.php";
include_once "../inc/connect.php";

// check if tables exists
$sqlpar = "SELECT *	FROM " . $table_prefix . "_parameters limit 1";
$result = mysqli_query($con, $sqlpar) or die(header('location:opstart_installatie.php?fout=table'));


$statoe = 1;
// check if pass is ok
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

$day= $_GET['day'];
$mon= $_GET['month'];
$year= $_GET['year'];
$time= $_GET['time'];
$value= $_GET['value'];
$dayearnings = $_GET['earnings'];
$wr= $_GET['wr'];


	//Change the decimal point to decimal point
	$value = str_replace(',', '.', $value);

if ($day == "" or $mon == "" or $year == "" or $wr == "" or $value == "") {
	echo $txt["parupdateerror"]."<br>"."<br>";
?>	
<form>
<input type="button" value="<?php echo $txt["parback"]; ?>" onclick="window.location.href='update.php'" />
</form> 
<?php	
		
}else{

if ($day !== "") $day = str_pad($day, 2, 0, STR_PAD_LEFT);
if ($mon !== "") $mon = str_pad($mon, 2, 0, STR_PAD_LEFT);

$date= $year."-".$mon."-".$day;

If ($time == ""){
	echo $txt["parupdatetimeinfo1"]."<br>";
	$date00 = $date." 00:00:00";
	
	
	mysqli_query($con, "DELETE FROM " . $table_prefix . "_maand WHERE Naam ='" . $wr . "' AND Datum_Maand='" . $date00 . "'") or die("Query failed. ERROR: " . mysqli_error($con));

	$string11 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $date . $wr . "','" . $date00 . "'," . $value . ",'" . $wr . "')";
	mysqli_query($con, $string11) or die("Query failed. ERROR: string11 " . mysqli_error($con));

	echo "<br>"."<br>"."<br>"."<br>";
	echo $txt["parupdateready"]."<br>"."<br>";
	echo $txt["omvormer"].": ".$wr."<br>";
	echo $txt["datum"].": ".$date."<br>";
	echo $txt["waarde"].": ".$value."<br>"."<br>";
	}
	else{

			If ($dayearnings == "")	$dayearnings =0;
					
				$var=explode(":",$time);
				$timehr = $var[0];
				$timemin = $var[1];
				
				$timehr = str_pad($timehr, 2, 0, STR_PAD_LEFT);
				$timemin = str_pad($timemin, 2, 0, STR_PAD_LEFT);

				$time=$timehr.":".$timemin.":00";
				$wrdat= $date." ".$time;


				$stringdelete = "DELETE FROM ".$table_prefix."_dag WHERE IndexDag='".$wrdat.$wr."'";
				$string="insert into ".$table_prefix."_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";	
				$string.="('".$wrdat.$wr."','".$wrdat."','".$value."','".$dayearnings."','".$wr."')";
						
				mysqli_query($con,$stringdelete)or die ('Query failed. ERROR: string  '.$wrdat.$wr.' was not found!'. mysql_error($con));
				mysqli_query($con,$string)or die("Query failed. ERROR: string " . mysqli_error($con));
						
				echo "<br>"."<br>"."<br>"."<br>";
				echo $txt["parupdateready"]."<br>"."<br>";
				echo $txt["omvormer"].": ".$wr."<br>";
				echo $txt["datum"].": ".$date."<br>";
				echo $txt["parupdatetime"].": ".$time."<br>";
				echo $txt["waarde"].": ".$value."<br>";
				if ($dayearnings!== 0) echo $txt["parupdatedayearnings"].$dayearnings."<br>"."<br>"."<br>";
				
		}

}

?>
</div>
	</div>
    	</div>

