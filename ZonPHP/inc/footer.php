<?php
$footer_display_style = "clear:both; ";
if (isset($param['hide_footer'])) {
    $footer_display_style = "display: none;";
}

if (strpos($version, "(dev)") > 0) {
    $downloadlink = "https://github.com/seehase/ZonPHP/archive/development.zip";
} else {
    $downloadlink = "https://github.com/seehase/ZonPHP/archive/master.zip";
}


?>

<div id="footer"
     style="background-color: #<?php echo $colors['color_footerbackground'] . "; " . $footer_display_style ?>;">

    <font size="-3">
        <?php echo "ZonPHP " . $version ?> <b> wewwx integration - Non Flash Version</b>
        <a style="display:block;float:right;width:60%;margin-left:10px;" class="blink"
           href="<?php echo $downloadlink ?>"><b><?php echo $new_version_label ?></b></a>
        <br/>
        <a href="http://solar.seehausen.org/about.php">solar.seehausen.org</a>&nbsp;
        <a href="https://github.com/seehase/ZonPHP" onclick="target='_blank'">https://github.com/seehase/ZonPHP</a>&nbsp;

    </font>

</div>




