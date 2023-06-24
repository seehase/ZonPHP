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
                    &nbsp;<?php echo $txt["grafiekoverzicht"]; ?>&nbsp;&nbsp;&nbsp;&#9658;</label>
                <input type="checkbox" name="nav" id="fof" class="sub-nav-check">
                <ul id="fof-sub" class="sub-nav">
                    <li class="sub-heading"><?php echo $txt["grafiekoverzicht"]; ?><label for="fof" class="toggle"
                                                                                          onclick="" title="Back">&#9658;</label>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/day_overview.php"><?php echo $txt["chart_dayoverview"]; ?></a>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/month_overview.php"><?php echo $txt["chart_monthoverview"]; ?></a>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/year_overview.php"><?php echo $txt["chart_yearoverview"]; ?></a>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/all_years_overview.php"><?php echo $txt["chart_allyearoverview"]; ?></a>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/last_years_overview.php"><?php echo $txt["chart_lastyearoverview"]; ?></a>
                    </li>
                    <li>
                        <a href="<?php echo HTML_PATH ?>pages/cumulative_overview.php"><?php echo $txt["chart_cumulativeoverview"]; ?></a>
                    </li>
                    <li><a href="<?php echo HTML_PATH ?>pages/top31.php"><?php echo $txt["chart_31days"]; ?></a></li>
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
                    <li><a href="<?php echo HTML_PATH ?>install/par_welcome.php"><?php echo $txt["login"]; ?> </a></li>
                    <li><a href="<?php echo HTML_PATH ?>pages/show_plant.php"><?php echo $txt["installatie"]; ?></a>
                    </li>
                    <li><a href="https://github.com/seehase/ZonPHP/">sourcecode</a></li>
                    <li><a href="https://github.com/seehase/ZonPHP/archive/master.zip">download ZonPHP</a></li>
                    <li><a href="<?php echo HTML_PATH ?>inc/destroy.php"><?php echo $txt["clearsession"]; ?> </a></li>
                </ul>
            </li>
        </ul>
        <label class="toggle close-all" onclick="uncheckboxes('nav')">&times;</label>
    </div> <!-- closing "#menu" -->
</div>
<div class="container">
