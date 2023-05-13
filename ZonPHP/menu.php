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

                <li><a href="day_overview.php"><?php echo $txt['chart_dayoverview']; ?></a></li>
                <li><a href="month_overview.php"><?php echo $txt['chart_monthoverview']; ?></a></li>
                <li><a href="year_overview.php"><?php echo $txt['chart_yearoverview']; ?></a></li>
                <li><a href="all_years_overview.php"><?php echo $txt["chart_allyearoverview"]; ?></a></li>
                <li><a href="last_years_overview.php"><?php echo $txt["chart_lastyearoverview"]; ?></a></li>
                <li><a href="top31_overview.php?Max_Min=Top"><?php echo $txt["chart_31days"]; ?></a></li>
            </ul>
        </li>

        <?php if ($use_weewx == true)
            echo '
            <li><a href="#" style="display: flex">&nbsp;</a> <label for="weewx" class="toggle-sub" onclick="">&nbsp;weewx&nbsp;&nbsp;&#9658;</label>
            <input type="checkbox" name="nav" id="weewx" class="sub-nav-check"/>
            <ul id="weewx-sub" class="sub-nav">
                <li class="sub-heading">weewx<label for="weewx" class="toggle" onclick="" title="Back">&#9658;</label>
                </li>
                <li><a href="weewx_overview.php">weewx-overview</a></li>
                <li><a href="/weewx/index.html">weewx-website</a></li>                
            </ul>
        </li> ';
        ?>

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

