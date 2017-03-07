<?php

include_once "../Parameters.php";
include_once "../inc/sessionstart.php";
include_once "../inc/connect.php";

if (!isset($_SESSION['passok']))
    header('location:install/par_welcome.php');

include "par_header.php";

// check if tables exists
$sqlpar = "SELECT *	FROM " . $table_prefix . "_parameters limit 1";
$result = mysqli_query($con, $sqlpar) or die(header('location:opstart_installatie.php?fout=table'));


$sqlrefer = "SELECT *
	FROM " . $table_prefix . "_euro 
	ORDER BY Datum_Euro ASC";

$resultrefer = mysqli_query($con, $sqlrefer) or die("Query failed. ERROR: " . mysqli_error($con));
if (mysqli_num_rows($resultrefer) == 0) {
    $arefer[date('Y', time())] = "Geen data";
    $areferdag[date('d/m/y G:i', time())] = "Geen data";
} else {
    while ($row = mysqli_fetch_array($resultrefer)) {
        $arefer[date('Y', strtotime($row['Datum_Euro']))] = $row['Geg_Euro'];
    }
}
$bkannietsave = 0;
foreach ($arefer as $ijaar => $feuro) {
    if (!empty($_POST[$ijaar])) {
        if (!is_numeric($_POST[$ijaar])) {
            $_POST[$ijaar] = $txt["verkeerd"];
            $bkannietsave = 1;
        }
    } else {
        if (!isset($arefer[$ijaar])) $arefer[$iijaar] = 0;
        $_POST[$ijaar] = $arefer[$ijaar];
    }
}
//echo '<pre>',print_r($_POST),'</pre>';
?>
<div id="menus">
    <?php include "par_menu.php"; ?>
</div>
<div id="container">
    <div id="bodytext">
        <div class="inside">
            <?php
            echo $txt["euro"] . " &euro;.<br /><br />";
            //if(isset($_GET['save'])){
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if ($bkannietsave == 0) {
                    $sqldel = "DELETE FROM " . $table_prefix . "_euro";
                    mysqli_query($con, $sqldel) or die("Query failed. sql_wis: " . mysqli_error($con));
                    $sqlsave = "INSERT INTO " . $table_prefix . "_euro(Datum_Euro,Geg_Euro)values";
                    foreach ($_POST as $ijaar => $feuro) {
                        $sqlsave .= "('" . $ijaar . "-01-01'," . $feuro . "),";
                    }
                    $sqlsave = substr($sqlsave, 0, -1);
                    mysqli_query($con, $sqlsave) or die("Query failed. sql_save: " . mysqli_error($con));
                    echo "<font size='0'color='#00AA00'>" . $txt["opgeslagen"] . "</font><br />";
                    $sqlrefer = "SELECT *
							FROM " . $table_prefix . "_euro 
							ORDER BY Datum_Euro ASC";

                    $resultrefer = mysqli_query($con, $sqlrefer) or die("Query failed. ERROR: " . mysqli_error($con));
                    if (mysqli_num_rows($resultrefer) == 0) {
                        $arefer[date('Y', time())] = "Geen data";
                        $areferdag[date('d/m/y G:i', time())] = "Geen data";
                    } else {
                        while ($row = mysqli_fetch_array($resultrefer)) {
                            $arefer[date('Y', strtotime($row['Datum_Euro']))] = $row['Geg_Euro'];
                        }
                    }
                } else
                    echo "<font size='0'color='#ff0000'>" . $txt["verkeerd"] . "</font><br />";//par_euro.php?save=ok
            }
            ?>
            <FORM METHOD="post" ACTION="">
                <table>
                    <tbody>
                    <tr>
                        <td><font size="-1"><?php echo $txt["datum"]; ?></font></td>
                        <td><font size="-1"><?php echo $txt["waarde"]; ?></font></td>
                        <td><font size="-1"><?php echo $txt["nieuwewaarde"]; ?></font></td>
                    </tr>
                    <?php
                    //foreach($arefer as $axas => $ayas){
                    foreach ($arefer as $ijaar => $feuro) {
                        if (!isset($arefer[$ijaar])) $arefer[$ijaar] = 0;
                        echo "<tr>
									<td><font size='-1'>" . $ijaar . "</font></td>
									<td><font size='-1'>" . $feuro . "</font></td>
									<td><input type='text' name='" . $ijaar . "' id='id" . $ijaar . "'value='";
                        if (!empty($_POST[$ijaar]))
                            echo $_POST[$ijaar];
                        echo "'></td>
									</tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <br />
                <INPUT TYPE="submit" VALUE="<?php echo $txt["save"]; ?>" onClick="return confirmSubmit()">
            </FORM>
        </div>
    </div>
</div>
</body>
</html>