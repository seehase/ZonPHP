<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";
include_once "inc/import_data.php";

include_once "inc/header.php";
include_once "charts/sensor_chart_period.php";


$id = "";
if (isset($_POST['id'])) {
    $id = $_POST['id'];
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

?>

<?php include_once "menu.php"; ?>
<div id="page-content">
    <script type="text/javascript">

        function getUrlVars() {
            var vars = {};
            var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,
                function (m, key, value) {
                    vars[key] = value;
                });
            return vars;
        }

        $(function () {
            $("#startdate, #enddate").datepicker({
                changeMonth: true,
                changeYear: true,
                showOn: "button",
                buttonImage: "inc/image/calendar.gif",
                buttonImageOnly: true,
                buttonText: "Select date",
            });
        });
        $(function () {
            $("#getDates").click(function () {
                var startdate = $("#startdate").val();
                var enddate = $("#enddate").val();

                var interval = $("#interval").val();
                var linetype = $("#linetype").val();

                var sensor = getUrlVars()["sensors"];
                var id = getUrlVars()["id"];
                var title = getUrlVars()["title"];
                var url = "sensor_overview_period.php?start=" + startdate + "&end=" + enddate + "&sensors=" + sensor + "&id=" + id + "&title=" + title + "&interval=" + interval + "&linetype=" + linetype;

                window.open(url, "_self");
            });
        });
        $(function () {
            $("#getToday").click(function () {
                var today = <?php echo '"' . strftime("%d-%m-%Y") . '"';?>;


                var sensor = getUrlVars()["sensors"];
                var id = getUrlVars()["id"];
                var title = getUrlVars()["title"];
                var url = "sensor_overview_period.php?start=" + today + "&end=" + today + "&sensors=" + sensor + "&id=" + id + "&title=" + title;

                window.open(url, "_self");
            });
        });
    </script>

    <?php
    $interval = 60;
    if (isset($_GET['interval'])) {
        $interval = intval($_GET['interval']);
    } else if (isset($_POST['interval'])) {
        $interval = intval($_POST['interval']);
    }
    $interval = $interval;  // * 60min


    $linetype = "avg";
    if (isset($_GET['linetype'])) {
        $linetype = $_GET['linetype'];
    } else if (isset($_POST['linetype'])) {
        $linetype = $_POST['linetype'];
    }
    ?>


    <div id="bodytext">

        <div id='resize' class="bigCharts" style="<?= WINDOW_STYLE_CHART ?>; padding-bottom: 103px; ">
            <div id="week_chart_header" class="<?= HEADER_CLASS ?>">
                <h2 align="center" ; style="margin-top: 0px;">
                    <?php echo $title;
                    echo " - " . strftime("%d-%m-%Y", $startdate) . " to " . strftime("%d-%m-%Y", $enddate);
                    ?>
                </h2>
                <p><input type="text" id="startdate" value="<?php echo strftime("%d-%m-%Y", $startdate); ?>"
                          readonly> -
                    <input type="text" id="enddate" value="<?php echo strftime("%d-%m-%Y", $enddate); ?>" readonly>
                    <button id="getToday" type="button">Today</button>
                    <br/>
                    Interval:
                    <select id="interval">
                        <option <?php if ($interval == "5") echo "selected "; ?> value="5">5min</option>
                        <option <?php if ($interval == "30") echo "selected "; ?> value="30">30min</option>
                        <option <?php if ($interval == "60") echo "selected "; ?> value="60">1h</option>
                        <option <?php if ($interval == "120") echo "selected "; ?> value="120">2h</option>
                        <option <?php if ($interval == "240") echo "selected "; ?> value="240">4h</option>
                        <option <?php if ($interval == "480") echo "selected "; ?> value="480">8h</option>
                        <option <?php if ($interval == "960") echo "selected "; ?> value="960">12h</option>
                        <option <?php if ($interval == "1440") echo "selected "; ?> value="1440">24h</option>
                    </select>
                    Type:
                    <select id="linetype">
                        <option <?php if ($linetype == "min") echo "selected "; ?> value="min">min</option>
                        <option <?php if ($linetype == "max") echo "selected "; ?> value="max">max</option>
                        <option <?php if ($linetype == "avg") echo "selected "; ?> value="avg">avg</option>
                    </select>
                    <button id="getDates" type="button">Refresh</button>
                </p>
            </div>

            <div id="sensor_chart_period_<?php echo $id ?>" style="width:100%; height:100%;"></div>
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