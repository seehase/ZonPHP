<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/month_chart.php";

?>


<?php
$paramstr_choose = '';
$paramstr_day = '';
# remove naam parameter
if (sizeof($_GET) > 0){
    foreach ($_GET as $key => $value) {
        if ( !(($key == "naam") || ($key == "type")) ) {
            $paramstr_choose .=  $key . "=" . $value . "&";
        }
        if ( $key != "maand") {
            $paramstr_day .= $key . "=" . $value . "&";
        }
    }
}
if (strpos($paramstr_day, "?") == 0) {
    $paramstr_day = '?' . $paramstr_day;
}
if (strpos($paramstr_choose, "?") == 0) {
    $paramstr_choose = '?' . $paramstr_choose;
}
?>


<?php include "menu.php"; ?>
<div id="page-content">

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 66px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <div style="position: absolute; z-index: 50">
                <form action="<?php $_SERVER['PHP_SELF']; ?>" method="GET">
                    <button class="btn btn-primary" type="submit" id="txt" name="dag" style="margin-top: 15px;"
                            value="<?php echo date('Y-m-d', $chartdate); ?>"><?php echo $txt["terugnaarvandaag"] ?></button>
                </form>
            </div>
            <h2 align="center">
                <form action = "<?php $_SERVER['PHP_SELF']; ?>" method = "GET">
                <?php if(date('Y-m',$date_minimum)<date('Y-m',$chartdate))
						echo '<button class="btn btn-primary" type="submit" name="maand" value= ' . date('Y-m-d', strtotime("-1 months", $chartdate)) . '  >  < </button>';
					echo " ".$datum." ";
					 if(date('Y-m',$date_maximum)>date('Y-m',$chartdate))
						echo '<button class="btn btn-primary" type="submit" name="maand" value= ' . date('Y-m-d', strtotime("+1 months", $chartdate)) . '  >  > </button>';
					?>
					</form>	
            </h2>
        </div>

        <div id="month_chart_all" style="width:100%; !important; height:100%; !important;"></div>
    </div>


    <div style="float: unset; margin-top: 5px;">
        <button class="btn btn-primary" id="toggelbutton"><?php echo $txt['showvalues'] ?></button>

    </div>

    <div id="tabelgeg">
        <div id="toggeldiv" class="collapse1">
            <table>
                <tbody>
                <?php
                $frefmaand = 1;
                $divisor = 1;

                if (isset ($adatum) && ($frefmaand * count($adatum)) != 0)
                {
                    $divisor = $frefmaand * count($adatum);
                }
                if ($datum != $txt["nodata"] . date("M-Y", $chartdate)) {
                    echo("<tr>
						<td><b>" . $txt["totaal"] . "</b></td>
						<td><b>" . number_format(array_sum($agegevens), 0, ',', '.') . "</b>kWh</td>
						<td><b>" . number_format(array_sum($agegevens) * $current_euroval, 0, ',', '.') . "</b>&euro;</td>
						<td><b>" . number_format(100 * array_sum($agegevens) / $divisor, 1, ',', '.') . "</b>%</td>
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