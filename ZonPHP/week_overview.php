<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/week_chart.php";

?>



<?php include "menu.php"; ?>
<div id="page-content">

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 59px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
            <h2 align="center"><?php echo $txt["chart_weekoverview"]; ?> </h2>
        </div>

        <div id="week_chart_<?php echo $inverter ?>" style="clear:both; width:100%; height: 100%;"></div>
    </div>

    <div style="float: unset; margin-top: 5px;">
        <button id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
    </div>

    <div id="tabelgeg">
        <div id="toggeldiv" class="collapse1">
            <table style="text-align: center; font-family: Verdana; " border="0" cellpadding="0"
                   cellspacing="0">
                <tbody>
                <?php
                echo("<tr>
							<td><b>" . $txt["totaal"] . "</b></td>
							<td><b>" . number_format(array_sum($agegaantaldw), 0, ',', '.') . "</b></td>
							<td><b>" . number_format(array_sum($agegsomdw), 0, ',', '.') . "</b>kWh</td>
							<td></td>
							<td><b>" . number_format(max($agegmaxdw), 2, ',', '.') . "</b>kWh</td>
							<td><b>" . number_format(array_sum($agegsomdw) / array_sum($agegaantaldw), 2, ',', '.') . "</b>kWh</td>
						</tr>");
                ?>
                <tr>
                    <td width=70><?php echo $txt["dag"]; ?></td>
                    <td width=70><?php echo $txt["aantal"]; ?></td>
                    <td width=105><?php echo $txt["somkwh"]; ?></td>
                    <td width=105><?php echo $txt["procent"]; ?></td>
                    <td width=105><?php echo $txt["maxkwh"]; ?></td>
                    <td width=105><?php echo $txt["kwhdag"]; ?></td>
                </tr>
                <?php
                if ($datum != "Leeg") {
                    for ($i = 1; $i <= 7; $i++) {
                        if (array_key_exists($i, $agegaantaldw)) {
                            if ($aberekengem[$i - 1] == max($aberekengem)) {
                                echo("<tr>
										<td><b>" . $short_weekdays[$i] . "</b></td>
										<td><b>" . $agegaantaldw[$i] . "</b></td>
										<td><b>" . number_format($agegsomdw[$i], 0, ',', '.') . "</b></td>
										<td><b>" . number_format(100 * $agegsomdw[$i] / array_sum($agegsomdw), 2, ',', '.') . "</b></td>
										<td><b>" . number_format($agegmaxdw[$i], 2, ',', '.') . " </b></td>
										<td><b>" . number_format($agegsomdw[$i] / $agegaantaldw[$i], 2, ',', '.') . "</b></td>
									</tr>");
                            } else {
                                echo("<tr>
										<td>" . $short_weekdays[$i - 1] . "</td>
										<td>" . $agegaantaldw[$i] . "</td>
										<td>" . number_format($agegsomdw[$i], 0, ',', '.') . "</td>
										<td>" . number_format(100 * $agegsomdw[$i] / array_sum($agegsomdw), 2, ',', '.') . "</td>
										<td>" . number_format($agegmaxdw[$i], 2, ',', '.') . " </td>
										<td>" . number_format($agegsomdw[$i] / $agegaantaldw[$i], 2, ',', '.') . "</td>
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
        $("#resize").height(<?php echo $big_chart_height ?>);
    });
</script>

</div><!-- closing ".page-content" -->

</div><!-- closing ".container" -->

<?php include_once "inc/footer.php"; ?>

</body>
</html>


