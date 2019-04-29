<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

$chartdate=@mktime();

if(isset($_GET['dag']))
	$_SESSION['sesdag']=strtotime($_GET['dag']); 

if(isset($_SESSION['sesdag'])){	
	if(isset($_GET['+']))	
		$_SESSION['sesdag']=strtotime("+1 day", $_SESSION['sesdag']);
	if(isset($_GET['-']))
		$_SESSION['sesdag']=strtotime("-1 day", $_SESSION['sesdag']);
	if(isset($_GET['Vandaag']))
		$_SESSION['sesdag'] = $chartdate;
}
else
	$_SESSION['sesdag'] = $chartdate;
?>
<?php
include_once "inc/header.php";
include_once "charts/day_chart.php";
?>

<?php include_once "menu.php"; ?>

<script type="text/javascript">

    $(function () {

        $("#startdate, #enddate").datepicker({
            changeMonth: true,
            changeYear: true,
            showOn: "button",
            gotoCurrent: false,
            showButtonPanel: true,
            buttonImage: "inc/image/calendar.gif",
            buttonImageOnly: true,
            buttonText: "Select date",
            onSelect: function () {
                var url = "day_overview.php?dag=" + $("#startdate").val();
                window.open(url, "_self");
            },
        });
        var _gotoToday = jQuery.datepicker._gotoToday;
        jQuery.datepicker._gotoToday = function (a) {
            var target = jQuery(a);
            var inst = this._getInst(target[0]);
            _gotoToday.call(this, a);
            jQuery.datepicker._selectDate(a, jQuery.datepicker._formatDate(inst, inst.selectedDay, inst.selectedMonth, inst.selectedYear));
        };
    });
</script>

<div id="page-content">

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 72px; ">

        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <form action = "<?php $_SERVER['PHP_SELF']; ?>" method = "GET">
				<h2 align="center" class="notopgap" >	
					<?php if($date_minimum<$_SESSION['sesdag'])
						echo '<INPUT class="btn btn-primary" type="submit" name="-" value="<">';
						echo " ".$datum." ";
					 	if($date_maximum>$_SESSION['sesdag'])
							echo '<INPUT class="btn btn-primary" type="submit" name="+" value=">">';
					?>	
				</h2>		
			</form>			
        </div>


		<div id="mycontainer" style="width:100%; height:100%;"></div>

    </div>

    <div style="float: unset; margin-top: 5px;">
        <button class="btn btn-primary" id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
        <form action = "<?php $_SERVER['PHP_SELF']; ?>" method = "GET">
		<INPUT class="btn btn-primary" type="submit" name="Vandaag"  value="<?php echo $txt["terugnaarvandaag"]; ?>"><br>
	</form>
        
    </div>

    <div id="kalender">
        <?php
        $iaantaldagen = cal_days_in_month(CAL_GREGORIAN, date("m", $chartdate), date("Y", $chartdate));
        echo '<table><tbody>';
        echo '<tr>';
        echo '<td width=60><a href="day_overview.php?dag=' . date("Y-m-d", strtotime("-1 months", $chartdate)) . '"><b>' . strftime("%B", strtotime("-1 months", $chartdate)) . '</b></a></td>';
        $maxdayreached = false;
        for ($i = 1; $i <= $iaantaldagen; $i++) {

            $currentdatestring = date("Y-m-", $chartdate) . $i;
            $currentdate = strtotime($currentdatestring);
            if ($currentdate <= $date_maximum) {
                echo '<td width=25><b>' . strftime("%a", $currentdate) . '</b><br />';
                echo '<a href="day_overview.php?dag=' . date("Y-m-", $chartdate) . $i . '">' . $i . '</a></td>';
            } else {
                echo '<td width=25><b>' . strftime("%a", $currentdate) . '</b><br />';
                echo '<span style="color: #C00000;"> ' . $i . '</span></td>';
                $maxdayreached = true;
            }

        }
        if ($maxdayreached) {
            echo '<td width=60><span style="color: #C00000;"> <b>' . strftime("%B", strtotime("+1 months", $chartdate)) . '</b></span></td>';
        } else {
            echo '<td width=60><a href="day_overview.php?dag=' . date("Y-m-d", strtotime("+1 months", $chartdate)) . '"><b>' . strftime("%B", strtotime("+1 months", $chartdate)) . '</b></a></td>';
        }

        echo '</tr>';
        echo '</tbody>
		</table>';
        ?>
    </div>

    <div id="tabelgeg">
        <div id="toggeldiv" class="collapse1">
            <table>
                <tbody>
                <tr>
                    <td width=20><b><?php echo $txt["uur"]; ?></b></td>
                    <?php
                    for ($i = 0; $i < (60 / $param['isorteren']); $i++) {
                        $auurtabel[] = $param['isorteren'] * $i;
                        echo '<td width=60><b>' . $param['isorteren'] * $i . '</b></td>';
                    }
                    //echo "<pre>".print_r($auurtabel,true)."</pre>";
                    ?>
                </tr>
                <?php
                if ($geengevdag != 0) {
                    $bstart = true;
                    $tabelstr = "";
                    foreach ($agegevens as $tuur => $fw) {
                        $min = date("i", strtotime($tuur));
                        if ($min != 00) {
                            if ($bstart) {
                                $tabelstr .= "<td><b>" . date("H", strtotime($tuur)) . "</b></td>";
                                for ($i = 1; $i <= array_search($min, $auurtabel); $i++) {
                                    $tabelstr .= "<td>--</td>";
                                }
                                $bstart = false;
                            }
                            if ($agegevens[$tuur] == max($agegevens))
                                $tabelstr .= "<td><b>" . number_format($fw, 0, ",", ".") . "</b></td>";
                            else
                                $tabelstr .= "<td>" . number_format($fw, 0, ",", ".") . "</td>";
                        } else {
                            $bstart = false;
                            if ($tabelstr == "") $tabelstr = "<td></td>";
                            echo("<tr>" . $tabelstr . "</tr>");
                            $tabelstr = "";
                            $tabelstr .= "<td><b>" . date("H", strtotime($tuur)) . "</b></td>";
                            if ($agegevens[$tuur] == max($agegevens))
                                $tabelstr .= "<td><b>" . number_format($fw, 0, ",", ".") . "</b></td>";
                            else
                                $tabelstr .= "<td>" . number_format($fw, 0, ",", ".") . "</td>";
                        }
                    }
                    if ($tabelstr == "") $tabelstr = "<td></td>";
                    echo("<tr>" . $tabelstr . "</tr>");
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#resize ").height(<?php echo $big_chart_height ?>);
    });
</script>

</div><!-- closing ".page-content" -->

</div><!-- closing ".container" -->
<?php include_once "inc/footer.php"; ?>

</body>
</html>