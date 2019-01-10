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




$sqlpar = "SELECT * 
	FROM " . $table_prefix . "_parameters
	WHERE Variable = 'sNaamSaveDatabasest'";
$resultpar = mysqli_query($con, $sqlpar) or die("Query failed. ERROR: " . mysqli_error($con));
if (mysqli_num_rows($resultpar) != 0) {
    while ($row = mysqli_fetch_array($resultpar)) {
        $anamen = $row['Waarde'];
    }
    $akommanamen = explode(',', $anamen);
} else {
    die(header('location:opstart_installatie.php?fout=parameter'));
}

$current_name = $akommanamen[0];

if (isset($_GET['naam'])) {
    $current_name = $_GET['naam'];
}
#<p>Datum vom Tag der gelöscht werden soll (2015-01-31): <input type="text" name="alter" /></p>
?>

<div id="menus">
	<?php include "par_menu.php";?>
</div> 

<div id="container">
    <div id="bodytextparm">
        <div class="inside">
            <h2 class="notopgap" align="center"><u><?php echo $txt["parupdate"];?></u></h2>

  <div id="bodytextloschen">	
	<form action="actionupdate.php" method="get">

  <p> </p>
  <br>
  <?php echo $txt["parinverter"]; ?>
           <label>
             <select name="wr">
					<option  SELECTED>
                    <?php
                if (isset($_GET['naam'])) echo $_GET['naam'];
                foreach ($akommanamen as $key => $naam)
                    echo '<option value="' . $naam . '">' . $naam . ' </option>';
                
				?>
			        </select>

            
            <br/>
            <br/>
                <label><?php echo $txt["parupdatetag"]; ?> :</label>
                <select name="dag">
                    <option SELECTED><?php if (!empty($_POST['dag'])) echo $_POST['dag']; ?>
                        <?php
                        for ($i = 1; $i <= 31; $i++) {
                            echo '<option value="' . $i . '">' . $i . '</option>';
                        }
                        ?>
                </select>
                <select name="maand">
                    <option SELECTED><?php if (!empty($_POST['maand'])) echo $_POST['maand']; ?>
                        <?php
                        for ($i = 1; $i <= 12; $i++) {
                            echo '<option value="' . $i . '">' . $i . '</option>';
                        }
                        ?>
                </select>
                <select name="jaar">
                    <option SELECTED><?php if (!empty($_POST['jaar'])) echo $_POST['jaar']; ?>
                        <?php
                        for ($i = date('Y'); $i >= 2006; $i--) {
                            echo '<option value="' . $i . '">' . $i . '</option>';
                        }
                        ?>
                </select>
            


 <p><?php echo $txt["parupdatezeit"]?><input type="text" name="zeit" /></p>
 <p><?php echo $txt["parupdatezeitinfo"]?></p>
 
 <p><?php echo $txt["parupdatewert"]?><input type="text" name="wert" /></p>
 
 <p><?php echo $txt["parupdateertraginfo"]?></p>
 <p><?php echo $txt["parupdateertrag"]?><input type="text" name="ertrag" /></p>

  
 
 
 <p><input type="submit" VALUE="<?php echo $txt["save"]; ?>" /></p>  
</form>


</div>
        </div>
    </div>
</div>




<?php 
#echo "Wenn keine Zeit angegeben wird, werden nur die Monatsdaten geändert!!"
#include "footer.php";?>