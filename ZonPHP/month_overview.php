<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/month_chart.php";


$inverter = $_SESSION['Wie'];
if (isset($_POST['inverter'])) {
    $inverter = $_POST['inverter'];
}

$inverter_id = $inverter;
$add_params = "";
if ((isset($_POST['type']) && ($_POST['type'] == "all")) ||
    (isset($_GET['type']) && ($_GET['type'] == "all"))) {
    $inverter_id = "all";
    $add_params = "&type=all";
}

//  $chartdate = time();
$nextmonthvisible = false;
$nextmonth = strtotime("+1 month", $chartdate);
$nextmonthstring = strftime("%Y-%m-%d", strtotime("+1 month", $chartdate));
if ($nextmonth <= $date_maximum) {
    $nextmonthvisible = true;
}

$prevmonthvisible = false;
#$prevmonth = strftime("%Y-%m-%d", strtotime("-1 month", $chartdate));
$prevmonth = strtotime("-1 month", $chartdate);
$prevmonthstring = strftime("%Y-%m-%d", strtotime("-1 month", $chartdate));
$date_minimum1=strtotime("-1 month", $date_minimum);

if ($prevmonth >= $date_minimum1) {
    $prevmonthvisible = true;
}

$chartmonthdatestring = strftime("%Y-%m-01", strtotime("+0 month", $date_maximum));
#$nextmonth = strftime("%Y-%m-%d", strtotime("+1 month", $chartdate));
#$prevmonth = strftime("%Y-%m-%d", strtotime("-1 month", $chartdate));

?>

<?php include "menu.php"; ?>
<div id="page-content">

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 59px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <h2 align="center">
                <?php
                if ($prevmonthvisible) {
                echo '<a class="btn btn-primary" href="month_overview.php?maand=' . $prevmonthstring . $add_params . '"> < </a>';
				}
                echo " " . $datum . " ";
                if ($nextmonthvisible) {
                echo '<a class="btn btn-primary" href="month_overview.php?maand=' . $nextmonthstring . $add_params . '"> > </a>';
				}
                ?>
            </h2>
        </div>

        <div id="month_chart_<?php echo $inverter_id ?>" style="width:100%; !important; height:100%; !important;"></div>
    </div>

    <div style="float: unset; margin-top: 5px;">
        <button id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
		<a href="<?php echo "month_overview.php?jaar=".$chartmonthdatestring . $add_params ?>" target="_self"><button><?php echo $txt['back_to_today'] ?></button>
		</a>

        <a href="<?php echo "month_overview.php?jaar=".$chartmonthdatestring ?>" target="_self"><button><?php echo $txt['inverter'] ?></button>
        </a>
        <a href="<?php echo "month_overview.php?jaar=".$chartmonthdatestring."&type=all" ?>" target="_self"><button><?php echo $txt['all_inverters'] ?></button>
        </a>

    </div>

    <div id="kalender">
        <?php

        echo '<table><tbody>';
        echo '<tr>';
        echo '<td width=40><a href="month_overview.php?maand=' . date("Y-01-1", strtotime("-1 year", $chartdate)) . '"><b>' .
            (strftime("%Y", strtotime("-1 year", $chartdate))) . '</b></a></td>';
        $dstartjaar = strtotime(date("Y-01-01", $chartdate));
		$dstartmon = strtotime(date("Y-m-01", $chartdate));

        for ($i = 0; $i <= 11; $i++) {
            #echo '<td width=50><a href="month_overview.php?maand=' . date("Y-m-1", strtotime("+" . $i . " months", $dstartjaar)) . '">' .
             #   strftime("%b", strtotime("+" . $i . " months", $dstartjaar)) . '</a></td>';
				
			$dstartmon = strtotime("+" . $i . " months", $dstartjaar);
			
            
			if ($dstartmon <= $date_maximum) {
				#echo "kleiner";
				#echo $dstartmon." <= ".$date_maximum;
            echo '<td width=50><a href="month_overview.php?maand=' . date("Y-m-1", strtotime("+" . $i . " months", $dstartjaar)) . '">' .
                strftime("%b", strtotime("+" . $i . " months", $dstartjaar)) . '</a></td>';
				$nextyearview = true;
				
            } else {
                echo '<td width=50><span style="color: #C00000;"> ' . strftime("%b", strtotime("+" . $i . " months", $dstartjaar)) . '</span></td>';
				$nextyearview = false;
            }
				
        }
		if ($nextyearview ) {
			echo '<td width=40><a href="month_overview.php?maand=' . date("Y-01-1", strtotime("+1 year", $chartdate)) .
				'"><b>' . strftime("%Y", strtotime("+1 year", $chartdate)) . '</b></a></td>';
		}else{
			echo '<td width=50><span style="color: #C00000;"> ' .  strftime("%Y", strtotime("+1 year", $chartdate)) . '</span></td>';
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
                <?php
                if ($datum != $txt["nodata"] . date("M-Y", $chartdate)) {
                    echo("<tr>
						<td><b>" . $txt["totaal"] . "</b></td>
						<td><b>" . number_format(array_sum($agegevens), 0, ',', '.') . "</b>kWh</td>
						<td><b>" . number_format(array_sum($agegevens) * $current_euroval, 0, ',', '.') . "</b>&euro;</td>
						<td><b>" . number_format(100 * array_sum($agegevens) / ($frefmaand * count($adatum)), 1, ',', '.') . "</b>%</td>
						<td></td>
						<td><b>" . number_format(1000 * array_sum($agegevens) / $ieffectiefkwpiek, 1, ',', '.') . "</b>kWhp</td>
						</tr>");
                }
                ?>
                <tr>
                    <td width=90><?php echo $txt["datum"]; ?></td>
                    <td width=105><?php echo $txt["behaaldkwh"]; ?></td>
                    <td width=105><?php echo $txt["behaaldeuro"]; ?></td>
                    <td width=105><?php echo $txt["procentverwacht"]; ?></td>
                    <td width=105><?php echo $txt["procentmaand"]; ?></td>
                    <td width=105><?php echo $txt["genormeerdkwhp"]; ?></td>
                </tr>
                <?php
                $iaantaldagen = cal_days_in_month(CAL_GREGORIAN, date("m", $chartdate), date("Y", $chartdate));
                if ($geengevmaand != 0) {
                    for ($i = 1; $i <= $iaantaldagen; $i++) {
						
						$currentdatestring = date("Y-m-", $chartdate) . $i;
						$currentdate = strtotime($currentdatestring);
						if ($currentdate <= $date_maximum) {
							if ($param['izonphpse'] == 0) {
								$slinkseversiekl = "<a href='day_overview.php?dag=" . $i . date("-m-Y", $chartdate) . "'><b>" . $i . date("-m-Y", $chartdate) . "</b></a>";
								$slinkseversie = "<a href='day_overview.php?dag=" . $i . date("-m-Y", $chartdate) . "'>" . $i . date("-m-Y", $chartdate) . "</a>";
							} else {
								$slinkseversiekl = "<b>" . $i . date("-m-Y", $chartdate) . "</b>";
								$slinkseversie = "" . $i . date("-m-Y", $chartdate) . "";
							}
							if (!isset($agegevens[$i])) $agegevens[$i] = 0;
							if ($agegevens[$i] == max($agegevens)) {
								echo("<tr>
									<td>" . $slinkseversiekl . "</td>
									<td><b>" . number_format($agegevens[$i], 2, ',', '.') . "</b></td>
									<td><b>" . number_format($agegevens[$i] * $current_euroval, 2, ',', '.') . " &euro;</b></td>
									<td><b>" . number_format($agegevens[$i] / $frefmaand * 100, 1, ',', '.') . "</b></td>
									<td><b>" . number_format($agegevens[$i] / array_sum($agegevens) * 100, 1, ',', '.') . "</b></td>
									<td><b>" . number_format(1000 * $agegevens[$i] / $ieffectiefkwpiek, 2, ',', '.') . "</b></td>
									</tr>");
							} else {
								echo("<tr>
									<td>" . $slinkseversie . "</td>
									<td>" . number_format($agegevens[$i], 2, ',', '.') . "</td>
									<td>" . number_format($agegevens[$i] * $current_euroval, 2, ',', '.') . " &euro;</td>
									<td>" . number_format($agegevens[$i] / $frefmaand * 100, 1, ',', '.') . "</td>
									<td>" . number_format($agegevens[$i] / array_sum($agegevens) * 100, 1, ',', '.') . "</td>
									<td>" . number_format(1000 * $agegevens[$i] / $ieffectiefkwpiek, 2, ',', '.') . "</td>
									</tr>");
							}
						}
                    }
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