<?php
include_once "../parameters.php";
include_once ROOT_DIR . "/inc/sessionstart.php";
include_once ROOT_DIR . "/inc/load_cache.php";

include_once ROOT_DIR . "/inc/header.php";
?>
<?php include_once ROOT_DIR . "/inc/menu.php"; ?>

<div id="page-content">

    <div id='id_about' class="bigCharts" style="<?= WINDOW_STYLE_BIG ?>">
        <div class="<?= HEADER_CLASS ?>"><h2>I N F O</h2> <?= $version ?></div>
        <div id='id_about_content' class="<?= CONTENT_CLASS ?>"
             style="background-color: #<?php echo $colors['color_chartbackground'] ?> ">

            <div style="height: auto;">
                <h3>Information and documentation can be found on github </h3> <a class="myButtona"
                                                                                  href="https://github.com/seehase/ZonPHP"><b>https://github.com/seehase/ZonPHP</b></a>
                <hr>
            </div>

            <div>
                <h2> Development of ZonPHP </h2>
                I uses ZonPHP since many years, unfortunately it was not maintained anymore, so I decided to continue
                and refactor the code
                First major change was to remove Adobe Flash and use JavaScript chart library
                The project is OpenSource, if you want to participate, just contribute on GitHub
                <a href="https://github.com/seehase/ZonPHP"><b>https://github.com/seehase/ZonPHP</b></a>
                <br/>
                Questions or feedback <a href="mailto:zonphp@seehausen.org?Subject=ZonPHP"
                                         target="_top">zonphp@seehausen.org</a>
                <br/><br/>
                Versionshistory: <br/>
                <strong>ZonPHP.12.12:</strong> (2013-12-13)
                <ul>
                    <li>Last official version from Rally</li>
                </ul>
                <strong>ZonPHP.2016.06.00:</strong> (2016-06-05)
                <ul>
                    <li>First FLASH-FREE version</li>
                </ul>
                <strong>ZonPHP.2016.06.15: </strong> (2016-06-26)
                <ul>
                    <li>Reworked installation</li>
                </ul>
                <strong>ZonPHP.2016.08.03: </strong> (2016-08-14)
                <ul>
                    <li>Session Handling</li>
                    <li>Support for Hyperion-converter</li>
                </ul>
                <strong>ZonPHP.2016.09.02: </strong> (2016-09-02)
                <ul>
                    <li>Fixed session handling</li>
                </ul>
                <strong>ZonPHP.2016.12.04: </strong> (2016-12-04)
                <ul>
                    <li>Performace optimization and caching</li>
                    <li>complete refactoring</li>
                    <li>New Chart Library <a href="http://http://www.highcharts.com/" target="_blank">http://www.highcharts.com/</a>
                    </li>
                    <li>Improved config</li>
                </ul>
                <strong>ZonPHP.2016.12.08: </strong> (2016-12-08)
                <ul>
                    <li>Compatibility with PHP 7 and MySQL 5.7</li>
                </ul>
                <strong>ZonPHP.2016.12.18: </strong> (2016-12-11)
                <ul>
                    <li>Introduced "Themes"</li>
                </ul>
                <strong>ZonPHP.2017.01.01: </strong> (2017-01-01)
                <ul>
                    <li>Integration of weewx to show weather data</li>
                    <li><a href="http://www.weewx.com/" target="_blank">http://www.weewx.com/</a></li>
                </ul>

                <strong>Version ZonPHP.2017.01.12: </strong> (2017-01-01)
                <ul>
                    <li>Moved code and documentation to github <a href="https://github.com/seehase/ZonPHP"
                                                                  target="_blank">https://github.com/seehase/ZonPHP</a>
                    </li>
                </ul>

                <strong>current Version ZonPHP.2023.05.01: </strong> (v2023.05.01)
                <ul>
                    <li>PHP8 compliant</li>
                    <li>Cleanup code</li>
                    <li>Multi converter support</li>
                </ul>
            </div>
            <br/>
            <br/>
            <hr>
            <div style="height: auto;">
                <a class="btn btn-zonphp" href="https://github.com/seehase/ZonPHP/archive/master.zip"><b>Download
                        current
                        Version
                        ZonPHP from github</b></a>
                <hr>
            </div>
            <br/>

        </div>
    </div>
</div><!-- closing ".page-content" -->
<div id="spacer" style="float: left; height: 1px; width: 400px; display: block">
    <br/>&nbsp;
</div>


</div><!-- closing ".container"  opened in menu.php-->
<?php include_once ROOT_DIR . "/inc/footer.php"; ?>

</body>
</html>
