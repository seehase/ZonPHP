<?php

include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";

include_once "inc/header.php";


?>

    <div id="menus"><?php include_once "menu.php"; ?></div>
    <div id="content" style="float: left; top: 40px;  width: 100%; margin-left: 10px; height: auto; overflow: hidden;">

        <div id='id_links' class="bigCharts" style="<?= WINDOW_STYLE_BIG ?>">
            <div class="<?= HEADER_CLASS ?>"><h2>L I N K S</h2></div>
            <div id='id_links_content' class="<?= CONTENT_CLASS ?>" >
                <ul>
                    <li><a href="http://www.rallyhammer.be/RallyForum/index.php" TARGET="_blank">rallyhammer ZonePHP</a>
                    </li>
                    <li><a href="http://www.sonnenertrag.eu" TARGET="_blank">Sonnenertrag.eu</a></li>
                    <li><a href="http://www.albrechtreiber.de/index.php?id=datalogging"
                           TARGET="_blank">www.albrechtreiber.de</a>
                    </li>
                    <hr/>
                    samples
                    <li><a href="http://craeghs-syen.be/WEILLEN/index.php" TARGET="_blank">WEILLEN</a></li>
                    <li><a href="http://craeghs-syen.be/zon/index.php" TARGET="_blank">Zon</a></li>
                    <li><a href="http://www.fbussi.de/test/index.php" TARGET="_blank">Bussi</a></li>
                    <li><a href="http://www.marcelstoffels.eu/ZonPHPneu/" TARGET="_blank">Marcel</a></li>
                    <hr/>
                    download
                    <li><a href="http://solar.seehausen.org/downloads">download aktuelle Version ZonPHP</a></li>
                </ul>
            </div>
        </div>



<?php include_once "inc/footer.php"; ?>