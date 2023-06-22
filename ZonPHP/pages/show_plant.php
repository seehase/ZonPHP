<?php
include_once "../parameters.php";
include_once ROOT_DIR."/inc/sessionstart.php";
include_once ROOT_DIR."/inc/load_cache.php";
include_once ROOT_DIR."/inc/header.php";
if (!isset($param['image1'])) $param['image1'] = "inc/image/image1.jpg";
if (!isset($param['image2'])) $param['image2'] = "inc/image/image2.jpg";
?>
<?php include ROOT_DIR."/inc/sidemenu.php"; ?>
<div id="page-content">
    <div id='id_install' class="bigCharts" style="<?= WINDOW_STYLE_BIG ?>">
		<div id="menu_header" class="<?= MENU_CLASS ?>" style="height: 45px; background: #222; vertical-align: middle;">
		<?php include_once ROOT_DIR."/inc/topmenu.php"; ?>
		</div>


        <div class="<?= HEADER_CLASS ?>"><h2>I N S T A L L A T I O N</h2></div>
        <div id='id_install_content' class="<?= CONTENT_CLASS ?>">
            <div>
                <h1>Anlage von <?php echo $param['sNaamVoorOpWebsite'] ?></h1>
                <?php echo '
                Standort: ' . $param['sPlaats'] . '<br>
                Module: ' . $param['sSoort_pannel_aantal'] . '<br>
                Wechselrichter: ' . $param['sOmvormer'] . '<br>
                Inbetriebnahme: ' . date("Y-m-d", strtotime($dstartdatum)) . '<br>
                Ausrichtung: ' . $param['sOrientatie'] . '<br>
                Data Logger: ' . $param['sData_Captatie'] . '<br> <br>';
                ?>
            </div>
            <div id="foto" style="float:none;">
                <p>
                    <img src="<?php echo HTML_PATH ."/". $param['image1'] ?>" alt="<?php echo $txt["imagemissing"] ?>"
                         style="border: 2px solid #000000; border-radius: 10px 10px 10px 10px; width: 600px">
                </p>
                <p>
                    <img src="<?php echo HTML_PATH ."/".$param['image2'] ?>" alt="<?php echo $txt["imagemissing"] ?>"
                         style="border: 2px solid #000000; border-radius: 10px 10px 10px 10px;  width: 600px">
                </p>
            </div>

        </div>
    </div>
</div><!-- closing ".page-content" -->
<div id="spacer" style="float: left; height: 10px; width: 400px; display: block">
    <br>&nbsp;
</div>
</div><!-- closing ".container" -->
<?php include_once ROOT_DIR."/inc/footer.php"; ?>
</body>
</html>