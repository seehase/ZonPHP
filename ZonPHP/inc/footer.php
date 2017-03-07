<?php
$show_footer = "display: none;";
if (isset($param['show_footer']))
{
    $show_footer = '';
}

?>

<div id="footer" style="background-color: #<?php echo $colors['color_footerbackground'] ."; ". $show_footer ?>;">

    <font size="-3">
        <?php echo "ZonPHP " . $version ?> <b> wewwx integration - Non Flash Version</b>
        <a style="display:block;float:right;width:60%;margin-left:10px;" class="blink" href="http://solar.seehausen.org/downloads/"><b>new version available!!!!</b></a>
        <br />
        <a href="http://solar.seehausen.org/about.php">solar.seehausen.org</a>
        <a href="http://www.slaper.be/" onclick="target='_blank'">www.slaper.be</a>
        <a href="http://www.rallyhammer.be/RallyForum" onclick="target='_blank'">www.rallyhammer.be</a>

    </font>

</div>




