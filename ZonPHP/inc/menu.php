<script>
    document.documentElement.className = 'js'; // adds class="js" to <html> element
    function uncheckboxes(nav) {
        var navarray = document.getElementsByName(nav);
        for (var i = 0; i < navarray.length; i++) {
            navarray[i].checked = false
        }
    }
</script>
<?php
$menu_display_style = "clear:both; ";
$show_menu = true;
if ($params['hideMenu'] == true) {
    $menu_display_style = "display: none;";
    $show_menu = false;
}
?>
<div>
    <input type="checkbox" name="nav" id="main-nav-check">
    <div id="menu" style="<?php echo $menu_display_style; ?>">
        <label for="main-nav-check" class="toggle" onclick="" title="Close"></label>
        <ul>
            <li><a href="#" style="display: flex">&nbsp;</a> <label for="fof" class="toggle-sub" onclick="">
                    &nbsp;<?php echo getTxt("grafiekoverzicht"); ?>&nbsp;&nbsp;&nbsp;&#9658;</label>
                <input type="checkbox" name="nav" id="fof" class="sub-nav-check">
                <ul id="fof-sub" class="sub-nav">
                    <li class="sub-heading"><?php echo getTxt("grafiekoverzicht"); ?><label for="fof" class="toggle"
                                                                                            onclick="" title="Back">&#9658;</label>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/day_overview.php"><?php echo getTxt("chart_dayoverview"); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/month_overview.php"><?php echo getTxt("chart_monthoverview"); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/year_overview.php"><?php echo getTxt("chart_yearoverview"); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/all_years_overview.php"><?php echo getTxt("chart_allyearoverview"); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/last_years_overview.php"><?php echo getTxt("chart_lastyearoverview"); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/cumulative_overview.php"><?php echo getTxt("chart_cumulativeoverview"); ?></a>
                    </li>
                    <li><a href="<?php echo HTML_PATH ?>pages/top31.php"><?php echo getTxt("chart_31days"); ?></a></li>
                </ul>
            </li>
            <li><a href="#" style="display: flex">&nbsp;</a> <label for="themes" class="toggle-sub" onclick="">&nbsp;Themes&nbsp;&nbsp;&nbsp;&#9658;</label>
                <input type="checkbox" name="nav" id="themes" class="sub-nav-check">
                <ul id="themes-sub" class="sub-nav">
                    <li class="sub-heading">Themes<label for="themes" class="toggle" onclick=""
                                                         title="Back">&#9658;</label>
                    </li>
                    <li><a href='?theme=user'>User</a></li>
                    <li><a href='?theme=default'>ZonPHP&nbsp;Default</a></li>
                    <li><a href='?theme=theme1'>DarkGreyFire</a></li>
                    <li><a href='?theme=theme2'>Julia</a></li>
                    <li><a href='?theme=theme3'>Fire</a></li>
                    <li><a href='?theme=theme4'>blue</a></li>
                </ul>
            </li>
            <li><a href="#" style="display: flex">&nbsp;</a> <label for="info" class="toggle-sub" onclick=""><span
                            style="text-align: right">&nbsp;Info&nbsp;&nbsp;&nbsp;&#9658;</span></label>
                <input type="checkbox" name="nav" id="info" class="sub-nav-check">
                <ul id="info-sub" class="sub-nav">
                    <li class="sub-heading"><?= $version ?><label for="info" class="toggle" onclick="" title="Back">&#9658;</label>
                    </li>
                    <li><a href="<?php echo HTML_PATH ?>install/par_welcome.php"><?php echo getTxt("login"); ?> </a>
                    </li>
                    <li><a href="<?php echo HTML_PATH ?>pages/show_plant.php"><?php echo getTxt("installatie"); ?></a>
                    </li>
                    <li><a href="https://github.com/seehase/ZonPHP/">sourcecode</a></li>
                    <li><a href="https://github.com/seehase/ZonPHP/archive/master.zip">download ZonPHP</a></li>
                    <li><a href="<?php echo HTML_PATH ?>inc/destroy.php"><?php echo getTxt("clearsession"); ?> </a></li>
                </ul>
            </li>
        </ul>
        <label class="toggle close-all" onclick="uncheckboxes('nav')">&times;</label>
    </div> <!-- closing "#menu" -->
</div>
<div class="container">
    <div id="header">

        <a href='<?php echo HTML_PATH ?>index.php' style="position:absolute; top:13px; left:80px;  border:0"><img
                    src="<?php echo HTML_PATH ?>/inc/image/logo.png" alt="ZonPHP logo"></a>
        <?php if ($show_menu) echo '<label for="main-nav-check" class="toggle" onclick="" title="Menu">&#x2261;</label>'; ?>
        <span style="margin-left: 230px;">&nbsp;</span>
        <?php if (isActive('nl')) echo "<a href='?taal=nl' onclick=\"target='_self'\"><img src='" . HTML_PATH . "inc/image/blank.gif' class='flag flag-nl' alt='Nederlands' title='Nederlands'></a>"; ?>
        <?php if (isActive('en')) echo "<a href='?taal=en' onclick=\"target='_self'\"><img src='" . HTML_PATH . "inc/image/blank.gif' class='flag flag-gb' alt='english' title='english'></a>" ?>
        <?php if (isActive('fr')) echo "<a href='?taal=fr' onclick=\"target='_self'\"><img src='" . HTML_PATH . "inc/image/blank.gif' class='flag flag-fr' alt='français' title='français'></a>" ?>
        <?php if (isActive('de')) echo "<a href='?taal=de' onclick=\"target='_self'\"><img src='" . HTML_PATH . "inc/image/blank.gif' class='flag flag-de' alt='deutsch' title='deutsch'></a>" ?>
        <?php
        if (count (PLANTS) == 1) {
            echo '<p id="headerinverter" style="margin: -24px 360px 10px">' .
                $params['plant']['name'] .
                '</p>';
        }
        $ligado = 0;
        ?>
    </div><!-- closing "#header" -->