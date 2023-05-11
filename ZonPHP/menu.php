<script type="text/javascript">
    document.documentElement.className = 'js'; // adds class="js" to <html> element
    function uncheckboxes(nav) {
        var navarray = document.getElementsByName(nav);
        for (var i = 0; i < navarray.length; i++) {
            navarray[i].checked = false
        }
    }

    $('#setQuickVar1').on('click', function() {
        var checkStatus = this.checked ? 'ON' : 'OFF';

        $.post("inc/toggle.php", {"quickVar1a": checkStatus},
            function(data) {
                $('#resultQuickVar1').html(data);
            });
    });



</script>

<?php
if (!isset($param['flyout'])) {
    $param['flyout'] = "0";
}

if (intval($param['flyout']) > 0) {
    echo "<div>";
}


?>
<input type="checkbox" name="nav" id="main-nav-check"/>
<div id="menu" style="clear:both; ">
    <label for="main-nav-check" class="toggle" onclick="" title="Close"></label>
    <ul>
        <li><a href="#" style="display: flex">&nbsp;</a> <label for="fof" class="toggle-sub" onclick="">
                &nbsp;<?php echo $txt["grafiekoverzicht"]; ?>&nbsp;&nbsp;&nbsp;&#9658;</label>
            <input type="checkbox" name="nav" id="fof" class="sub-nav-check"/>
            <ul id="fof-sub" class="sub-nav">
                <li class="sub-heading"><?php echo $txt["grafiekoverzicht"]; ?><label for="fof" class="toggle"
                                                                                      onclick="" title="Back">
                        &#9658;</label></li>
                <li><a href="powermeter_overview_period.php"><?php echo $txt["chart_powermeter"]; ?></a></li>
                <?php
                if ($param['izonphpse'] == 0)
                    echo '<li><a href="day_overview.php">' . $txt["dagoverzicht"] . '</a></li>';
                ?>
                <li><a href="month_overview.php"><?php echo $txt['chart_monthoverview']; ?></a></li>
                <li><a href="year_overview.php"><?php echo $txt['chart_yearoverview']; ?></a></li>
                <li><a href="all_years_overview.php"><?php echo $txt["chart_allyearoverview"]; ?></a></li>
                <li><a href="last_years_overview.php"><?php echo $txt["chart_lastyearoverview"]; ?></a></li>
                <li><a href="top31_overview.php?Max_Min=Top"><?php echo $txt["chart_31days"]; ?></a></li>
                <?php
                if (isset($param['external_sensors'])) {
                    echo '                
                <li>
                    <a href="sensor_gauge_overview.php?sensors=197086:1:outdoor:33cc33,196692:1:cellar:FD3C00,197190:1:indoor:323232,197086:3:outdoor:33cc33,196692:3:cellar:FD3C00,196692:3:indoor:323232,197190:3:indoor:323232&amp;title=Temp%20all&amp;id=holg">Gauge view</a>
                </li>
                <li>
                    <a href="sensor_overview.php?sensors=197190:1:outdoor:33cc33,196692:1:cellar:FD3C00,197086:1:indoor:323232&amp;title=Temp%20all&amp;id=holg">Temp
                        all Sensors</a></li>
                <li>
                    <a href="sensor_overview.php?sensors=197190:3:outdoor:33cc33,196692:3:cellar:FD3C00,197086:3:indoor:323232&amp;title=RH%20all">RH
                        all Sensors</a></li>
                <li>
                    <a href="sensor_overview.php?sensors=197190:1:outdoor%20C:33cc33,197190:3:outdoor%20RH:FD3C00&amp;title=Outdoor">Outdoor
                        Sensors</a></li>
                <li>
                    <a href="sensor_overview.php?sensors=197086:1:indoor%20C:33cc33,197086:3:indoor%20RH:FD3C00&amp;title=Indoor">Indoor
                        Sensors</a></li>
                <li>
                    <a href="sensor_overview.php?sensors=196692:1:Cellar%20C:33cc33,196692:3:Cellar%20RH:FD3C00&amp;title=Cellar">Cellar
                        Sensors</a></li>

                <li>
                    <a href="sensor_overview_period.php?start=2016-10-6&amp;end=2017-11-16&amp;sensors=197086:1:indoor%20C:33cc33,197086:3:indoor%20RH:FD3C00&amp;title=Indoor%20Period&amp;id=holg">Indoor
                        Period</a></li>
                <li>
                    <a href="sensor_overview_period.php?start=2016-10-6&amp;end=2017-11-16&amp;sensors=197190:1:outdoor:33cc33,196692:1:cellar:FD3C00,197086:1:indoor:323232&amp;title=Temperature%20Period&amp;id=holg">Temp
                        Period</a></li>
                <li>
                    <a href="sensor_overview_period.php?start=2016-10-6&amp;end=2017-11-16&amp;sensors=197190:3:outdoor:33cc33,196692:3:cellar:FD3C00,197086:3:indoor:323232&amp;title=Humidity%20Period&amp;id=holg">RH
                        Period</a></li>
                ';
                }
                ?>

            </ul>
        </li>

        <?php if ($use_weewx == true)
            echo '
            <li><a href="#" style="display: flex">&nbsp;</a> <label for="weewx" class="toggle-sub" onclick="">&nbsp;weewx&nbsp;&nbsp;&#9658;</label>
            <input type="checkbox" name="nav" id="weewx" class="sub-nav-check"/>
            <ul id="weewx-sub" class="sub-nav">
                <li class="sub-heading">weewx<label for="weewx" class="toggle" onclick="" title="Back">&#9658;</label>
                </li>
                <li><a href="weewx_overview.php">' . $txt["weewx"] . '-overview</a></li>
                <li><a href="/weewx/index.html">' . $txt["weewx"] . '-website</a></li>                
            </ul>
        </li> ';
        ?>

        <?php if (isset($param['wunderground_stationID']) && strlen($param['wunderground_stationID']) > 0)
            echo '
            <li><a href="#" style="display: flex">&nbsp;</a> <label for="wunderground" class="toggle-sub" onclick="">&nbsp;wunderground&nbsp;&nbsp;&#9658;</label>
            <input type="checkbox" name="nav" id="wunderground" class="sub-nav-check"/>
            <ul id="wunderground-sub" class="sub-nav">
                <li class="sub-heading">wounderground<label for="wunderground" class="toggle" onclick="" title="Back">&#9658;</label>
                </li>
                <li><a href="https://www.wunderground.com" onclick="target=\'_blank\'">' . 'wounderground</a></li>
                <li><a href="https://www.wunderground.com/personal-weather-station/dashboard?ID=' . $param['wunderground_stationID'] .
                '" onclick="target=\'_blank\'">' . 'Your PWS</a></li>
            </ul>
        </li> ';
        ?>




        <li><a href="#" style="display: flex">&nbsp;</a> <label for="links" class="toggle-sub" onclick="">
                &nbsp;Links&nbsp;&nbsp;&nbsp;&#9658;</label>
            <input type="checkbox" name="nav" id="links" class="sub-nav-check"/>
            <ul id="links-sub" class="sub-nav">
                <li class="sub-heading">Links<label for="links" class="toggle" onclick="" title="Back">
                        &#9658;</label></li>

                <li><a href="https://github.com/seehase/ZonPHP" target="_blank">github - ZonPHP</a>
                </li>
                <li><a href="http://www.albrechtreiber.de/index.php?id=datalogging">www.albrechtreiber.de</a></li>
                <li><a href="http://www.sonnenertrag.eu/de/ingolstadt/seehase/17395/17043.html"
                       onclick="target='_blank'">Sonnenertrag EU Seehase</a></li>
                <li><a href="http://www.pv-log.com/photovoltaikanlage-seehase" onclick="target='_blank'">PV-Anlagenvergleich</a>
                </li>

                <li><a href="#">&nbsp;</a> <label for="samples" class="toggle-sub" onclick="">&nbsp;samples&nbsp;&nbsp;&#9658;</label>
                    <input type="checkbox"  name="nav" id="samples" class="sub-nav-check"/>
                    <ul id="samples-sub" class="sub-nav">
                        <li class="sub-heading" >samples<label style="margin-top: -5px;" for="samples" class="toggle" onclick="" title="Back">
                                &#9658;</label></li>
                        <li><a href="http://craeghs-syen.be/WEILLEN/index.php" onclick="target='_blank'">WEILLEN</a>
                        </li>
                        <li><a href="http://craeghs-syen.be/zon/index.php" onclick="target='_blank'">Zon</a></li>
                        <li><a href="http://www.fbussi.de/test/index.php" onclick="target='_blank'">Bussi</a></li>
                        <li><a href="http://www.marcelstoffels.eu/ZonPHPneu/" onclick="target='_blank'">Marcel</a></li>
                        <li><a href="http://www.pvlueck.bplaced.net" onclick="target='_blank'">PV Lueck</a></li>
                    </ul>
                </li>

            </ul>
        </li>


        <li><a href="#" style="display: flex">&nbsp;</a> <label for="themes" class="toggle-sub" onclick="">&nbsp;Themes&nbsp;&nbsp;&nbsp;&#9658;</label>
            <input type="checkbox" name="nav" id="themes" class="sub-nav-check"/>
            <ul id="themes-sub" class="sub-nav">
                <li class="sub-heading">Themes<label for="themes" class="toggle" onclick="" title="Back">&#9658;</label>
                </li>
                <li><a href='?theme=user'>User</a></li>
                <li><a href='?theme=default'>ZonPHP&nbsp;Default</a></li>
                <li><a href='?theme=theme1'>DarkGreyFire</a></li>
                <li><a href='?theme=theme2'>Julia</a></li>
                <li><a href='?theme=theme3'>Fire</a></li>
                <li><a href='?theme=theme4'>blue</a></li>
            </ul>
        </li>

        <li><a href="#" style="display: flex">&nbsp;</a> <label for="info" class="toggle-sub" onclick=""><span style="text-align: right">&nbsp;Info&nbsp;&nbsp;&nbsp;&#9658;</span></label>
            <input type="checkbox" name="nav" id="info" class="sub-nav-check"/>
            <ul id="info-sub" class="sub-nav">
                <li class="sub-heading"><?= $version ?><label for="info" class="toggle" onclick="" title="Back">
                        &#9658;</label>
                </li>
                <li><a href="install/par_welcome.php"><?php echo $txt["login"]; ?> </a></li>
                <?php if ($param['idagboek'] == 1)
                    echo '<li><a href="mydiary.php">' . $txt["dagboekmenu"] . '</a></li>';
                ?>
                <li><a href="about.php">About</a></li>
                <li><a href="installation.php"><?php echo $txt["installatie"]; ?></a></li>
                <li><a href="https://github.com/seehase/ZonPHP/">sourcecode</a></li>
                <li><a href="https://github.com/seehase/ZonPHP/archive/master.zip">download ZonPHP</a></li>
                <li><a href="inc/destroy.php"><?php echo $txt["clearsession"]; ?> </a></li>

            </ul>
        </li>

    </ul>
    <label class="toggle close-all" onclick="uncheckboxes('nav')">&times;</label>

</div> <!-- closing "#menu" -->

<?php
if (intval($param['flyout']) === 2) {
    echo "</div>";
}
?>

<div class="container">
    <div id="header">
        
        <input   type="image" src="inc/image/logo.png"   onClick="location.href='index.php'" style="position:absolute; top:21px; left:136px;  border:0" value='Vandaag'>
        
        <label for="main-nav-check" class="toggle" onclick="" title="Menu">&#x2261;</label>
        
        
        <span style="margin-left: 330px;">&nbsp;</span>
        <?php if (isset($param['lang_nl'])) echo "<a href='?taal=nl' onclick=\"target='_self'\"><img src='inc/image/blank.gif' class='flag flag-nl' alt='Nederlands' title='Nederlands'/></a>"; ?>
        <?php if (isset($param['lang_en'])) echo "<a href='?taal=en' onclick=\"target='_self'\"><img src='inc/image/blank.gif' class='flag flag-gb' alt='english' title='english'/></a>" ?>
        <?php if (isset($param['lang_fr'])) echo "<a href='?taal=fr' onclick=\"target='_self'\"><img src='inc/image/blank.gif' class='flag flag-fr' alt='française' title='française'/></a>" ?>
        <?php if (isset($param['lang_de'])) echo "<a href='?taal=de' onclick=\"target='_self'\"><img src='inc/image/blank.gif' class='flag flag-de' alt='deutsch' title='deutsch'/></a>" ?>

        <?php

        if ($iveromvormers == 1) {
            echo '<p id="headerinverter" style="margin: -22px 460px 10px">' .
                $param['sNaamVoorOpWebsite'] .
                '</p>';
        }

        $ligado = 0;

        ?>

    </div><!-- closing "#header" -->

<?php
if (intval($param['flyout']) == 1) {
    echo "</div>";
}
?>

