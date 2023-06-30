<?php
$footer_display_style = "clear:both; ";
if ($params['hideFooter'] == true) {
    $footer_display_style = "display: none;";
}

?>

<div id="footer"
     style="background-color: <?= $colors['color_footerbackground'] . "; " . $footer_display_style ?>;">

    <?= "ZonPHP " . $version ?> &nbsp; - &nbsp; <b>PHP8 compliant</b>
    <?= $new_version_label ?>
    &nbsp; - &nbsp;
    <a href="https://github.com/seehase/ZonPHP" onclick="target='_blank'">https://github.com/seehase/ZonPHP</a>
</div>




