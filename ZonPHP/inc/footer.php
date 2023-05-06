<?php
$show_footer = "display: none;";
if (isset($param['show_footer']))
{
    $show_footer = '';
}

if (strpos($version, "(dev)") > 0)
{
    $downloadlink = "https://github.com/seehase/ZonPHP/archive/development.zip";
}
else
{
    $downloadlink = "https://github.com/seehase/ZonPHP/archive/master.zip";
}


?>

<div id="footer" style="background-color: #<?php echo $colors['color_footerbackground'] ."; ". $show_footer ?>;">

    <font size="-3">
        <?php echo "ZonPHP " . $version ?> <b> wewwx integration - Non Flash Version</b>
        <a style="display:block;float:right;width:60%;margin-left:10px;" class="blink" href="<?php echo $downloadlink?>"><b><?php echo $new_version_label ?></b></a>
        <br />
        <a href="http://solar.seehausen.org/about.php">solar.seehausen.org</a>&nbsp;
        <a href="http://www.slaper.be/" onclick="target='_blank'">www.slaper.be</a>&nbsp;
        <a href="http://www.rallyhammer.be/RallyForum" onclick="target='_blank'">www.rallyhammer.be</a>&nbsp;
        <a href="https://github.com/seehase/ZonPHP" onclick="target='_blank'">https://github.com/seehase/ZonPHP</a>&nbsp;

    </font>

</div>




