<?php
include_once "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";

include_once "inc/header.php";
?>
<?php include_once "menu.php"; ?>

<div id="page-content">

    <div id='id_about' class="bigCharts" style="<?= WINDOW_STYLE_BIG ?>">
        <div class="<?= HEADER_CLASS ?>"><h2>I N F O</h2> <?= $version ?></div>
        <div id='id_about_content' class="<?= CONTENT_CLASS ?>"
             style="background-color: #<?php echo $colors['color_chartbackground'] ?> ">

            <div style="height: auto;">
                <h3>Information and documentation can be found on github </h3> <a class="myButtona" href="https://github.com/seehase/ZonPHP"><b>https://github.com/seehase/ZonPHP</b></a>
                <hr>
            </div>

            <div>
                <h2> Weiterentwicklung von ZonPHP </h2>
                Ich nutze ZonPHP seit Jahren und bin begeistert von der der Software. Leider wurde sie bisher nicht
                mehr
                weiter
                entwickelt, so dass ich mich
                mal hingesetzt habe und nun eine neue Version vorstellen will <br/>
                Die ZonPHP 2016 basiert vollständig auf ZonPHP.V12.12 von rallyhammer und wurde nur erweitert <br/>
                Der größter Vorteil ist, dass jetzt vollständig auf FLASH verzichtet wird, damit kann nun ZonPHP
                auch auf MAC, iPhone oder Android ohne Probleme benutzt werden<br/>
                Das Layout habe ich angefangen etwas zu modernisieren, ansonsten sind noch kleiner Bugfixes
                eingeflossen.<br/>
                <br/>
                Ich würde mich sehr über Feedback freuen oder Anregungen zur Weiterentwicklung<br/>
                Fragen oder Anregungen gerne per Mail an <a href="mailto:zonphp@seehausen.org?Subject=ZonPHP"
                                                            target="_top">zonphp@seehausen.org</a>
                <br/><br/>
                Versionshistorie: <br/>
                <strong>ZonPHP.12.12:</strong> (2013-12-13)
                <ul>
                    <li>letzte offizielle Version von Rally</li>
                </ul>
                <strong>ZonPHP.2016.06.00:</strong> (2016-06-05)
                <ul>
                    <li>erste öffentliche Version ohne Flash</li>
                </ul>
                <strong>ZonPHP.2016.06.15: </strong> (2016-06-26)
                <ul>
                    <li>Update der Index.PHP, Charts werden nun asynchron nachgeladen</li>
                    <li>Support für Multi-Wechselrichter</li>
                    <li>Installationsroutinen überarbeitet</li>
                </ul>
                <strong>ZonPHP.2016.08.03: </strong> (2016-08-14)
                <ul>
                    <li>Gui angepasst, Drag&amp;Drop</li>
                    <li>Session Handling</li>
                    <li>Support für Hyperion.-Wechselrichter</li>
                </ul>
                <strong>ZonPHP.2016.09.02: </strong> (2016-09-02)
                <ul>
                    <li>Fixed session handling</li>
                </ul>
                <strong>ZonPHP.2016.12.04: </strong> (2016-12-04)
                <ul>
                    <li>Performace Optimierung und Caching</li>
                    <li>Integration von zusätzlichen Sensoren <a
                                href="http://www.arexx.com/templogger/html/en/index.php"
                                target="_blank">http://www.arexx.com/templogger/html/en/index.php</a>
                    </li>
                    <li>komplettes refactoring</li>
                    <li>neue Datei Struktur</li>
                    <li>neue Chart Library <a href="http://http://www.highcharts.com/" target="_blank">http://www.highcharts.com/</a>
                    </li>
                    <li>alle Grafike umgestellt</li>
                    <li>überflüssigen Code entfernt</li>
                    <li>bessere Konfigurierbarkeit</li>
                </ul>
                <strong>ZonPHP.2016.12.08: </strong> (2016-12-08)
                <ul>
                    <li>Installation überarbeite</li>
                    <li>Kompatibilität zu PHP 7 und MySQL 5.7 hergestellt</li>
                </ul>
                <strong>ZonPHP.2016.12.11: </strong> (2016-12-11)
                <ul>
                    <li>GUI einheitlich angepasst, auf allen Detail Seiten</li>
                    <li>Einheiten in den Chart korrigiert</li>
                    <li>Tooltips erweitert</li>
                    <li>Bilder für die Intallations Seite jetzt in den Parametern pflegbar</li>
                </ul>
                <strong>ZonPHP.2016.12.18: </strong> (2016-12-11)
                <ul>
                    <li>Mehrer Themes möglich, wenn jemand ein schönen Theme erstellt hat, bitte mir bescheid sagen,
                        dann baue ich es ein
                    </li>
                    <li>Alle Farben sind jetzt konfigurierbar</li>
                    <li>Anzeige welche Chats angezeigt werden, kann jetzt konfiguriert werden</li>
                </ul>
                <strong>ZonPHP.2016.12.24: </strong> (2016-12-24)
                <ul>
                    <li>Frohe Weihnachten an alle, besonderen Dank an Michael und Christian, für ihre Geduld und das
                        viele Testen
                    </li>
                    <li>Neues Menu, noch nicht perfekt aber gut bedienbar an Tablet und Handy</li>
                    <li>viele kleinen Bugfixes und Verbesserungen</li>
                    <li>Test von weewx</li>
                </ul>

                <strong>ZonPHP.2017.01.01: </strong> (2017-01-01)
                <ul>
                    <li>Integration von weewx zur Anzeige von Daten aus verschiedenen Wettersationen</li>
                    <li>Anschluss Wetterstation an RaspberryPI und Datensynchronisation zur Web Site von ZonPHP</li>
                    <li><a href="http://www.weewx.com/" target="_blank">http://www.weewx.com/</a></li>
                </ul>

                <strong>current Version ZonPHP.2017.01.12: </strong> (2017-01-01)
                <ul>
                    <li>Moved code and documentation to github <a href="https://github.com/seehase/ZonPHP"
                                                                  target="_blank">https://github.com/seehase/ZonPHP</a></li>
                </ul>

                comming next: <br/>
                <ul>
                    <li>weewx Daten besser visualisieren und Graphen für historische Werte</li>
                    <li>eure Ideen und Anforderungen.... bitte um Feedback</li>
                    <li>Graphen für mehrere WR im Vergleich</li>
                    <li>Bessere Konfiguration für mehrere WR</li>
                    <li>dynamisches laden von Daten Importern</li>
                </ul>
            </div>
            <br/>
            <br/>
            <hr>
            <div style="height: auto;">
                <a class="btn btn-primary" href="https://github.com/seehase/ZonPHP/archive/master.zip"><b>Download current
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
<?php include_once "inc/footer.php"; ?>

</body>
</html>
