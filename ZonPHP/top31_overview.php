<?php
include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

unset($agegevens);
unset($frefmaand);
unset($adatum);
unset($fgemiddelde);
unset($amaxref);
unset($adatum);

include_once "inc/header.php";
include_once "charts/top31_chart.php";

$myLabel = "Top ";

if (isset($_GET['Max_Min']) && $_GET['Max_Min'] == "top") {
    $currentview = "top";
    $myLabel = " " . $txt['beste'] . " - " . $txt["chart_31days"] . " ";
} else {
    $currentview = "flop";
    $myLabel = $txt['slechtste'] . "- " . $txt["chart_31days"];
}

?>


<?php include "menu.php"; ?>

<?php

    $paramstr_choose = '';
    $paramstr_day = '';
    # remove naam parameter
    if (sizeof($_GET) > 0){
        foreach ($_GET as $key => $value) {
            if ( !(($key == "naam") || ($key == "type")) ) {
                $paramstr_choose .=  $key . "=" . $value . "&";
            }
            if ( $key != "dag") {
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


<div id="page-content">

    <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 72px; ">
        <div id="week_chart_header" class="<?= HEADER_CLASS ?>">


            <h2 align="center"><?php echo $myLabel; ?></h2>
            <?php
                echo '<a class="btn btn-primary" href="top31_overview.php?Max_Min=flop">' . $txt['slechtste'] . "-" . $txt["chart_31days"] . ' </a> &nbsp;';
                echo '<a class="btn btn-primary" href="top31_overview.php?Max_Min=top">' . $txt['beste'] . "-" . $txt["chart_31days"] . ' </a>';
            ?>

        </div>

        <div id="<?php echo $currentview ?>31_chart_" style="width:100%; height:100%;"></div>
    </div>

    <div style="float: unset; margin-top: 5px;">
        <button class="btn btn-primary" id="toggelbutton"><?php echo $txt['showvalues'] ?></button>
    </div>

    <div id="tabelgeg">

        <div id="toggeldiv" class="collapse1">
            <table>
                <tbody>
                <tr>
                    <td colspan="2"><b><?php echo $txt["totaal"]; ?></b></td>
                    <td>
                        <b><?php echo(number_format(array_sum($agegevens), 0, ',', '.')) ?></b>kWh
                    </td>
                    <td><b><?php echo(number_format($fsomeuro, 0, ',', '.')) ?></b>&euro;
                    </td>
                    <td>
                        <b><?php echo(number_format(1000 * array_sum($agegevens) / $ieffectiefkwpiek, 1, ',', '.')) ?></b>kWhp
                    </td>
                </tr>
                <tr>
                    <td colspan="2" width=105><?php echo $txt["datum"]; ?></td>
                    <td width=105><?php echo $txt["behaaldkwh"]; ?></td>
                    <td width=105><?php echo $txt["behaaldeuro"]; ?></td>
                    <td width=105><?php echo $txt["genormeerdkwhp"]; ?></td>
                </tr>
                <?php
                $iteller = 1;
                foreach ($agegevens as $ddag => $fkw) {
                    if ($param['izonphpse'] == 0) {
                        $slinkseversiekl = "<a href='day_overview.php?dag=" . $ddag . "'><b>" . date("d-m-Y", strtotime($ddag)) . "</b></a>";
                        $slinkseversie = "<a href='day_overview.php?dag=" . $ddag . "'>" . date("d-m-Y", strtotime($ddag)) . "</a>";
                    } else {
                        $slinkseversiekl = "<b>" . date("d-m-Y", strtotime($ddag)) . "</b>";
                        $slinkseversie = "" . date("d-m-Y", strtotime($ddag)) . "";
                    }
                    if ($ddag == date("Y-m-d", time())) {
                        echo("<tr>
						<td><b>" . $iteller . "</b></td>
						<td>" . $slinkseversiekl . "</td>
						<td><b>" . number_format($fkw, 2, ',', '.') . "</b></td>
						<td><b>" . number_format($fkw * $ajaareuro[date("y", strtotime($ddag))], 2, ',', '.') . "</b> &euro;</td>
						<td><b>" . number_format(1000 * $fkw / $ieffectiefkwpiek, 2, ',', '.') . "</b></td>
						</tr>");
                        $iteller++;
                    } else {
                        echo("<tr>
						<td>" . $iteller . "</td>
						<td>" . $slinkseversie . "</td>
						<td>" . number_format($fkw, 2, ',', '.') . "</td>
						<td>" . number_format($fkw * $price_per_kwh, 2, ',', '.') . " &euro;</td>
						<td>" . number_format(1000 * $fkw / $ieffectiefkwpiek, 2, ',', '.') . "</td>
						</tr>");
                        $iteller++;
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