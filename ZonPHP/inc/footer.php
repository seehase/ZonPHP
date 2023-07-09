<?php
global $params, $colors, $version, $new_version_label;
$footer_display_style = "clear:both; ";
if ($params['hideFooter']) {
    $footer_display_style = "display: none;";
}

?>

<div id="footer" style="background-color: <?= $colors['color_footerbackground'] . "; " . $footer_display_style ?>;">
    <?= "ZonPHP " . $version ?> &nbsp;
    <span class="smallFooter">
        &nbsp; - &nbsp;  <b>PHP8 compliant</b>&nbsp;- &nbsp;
        <a href="https://github.com/seehase/ZonPHP" onclick="target='_blank'">https://github.com/seehase/ZonPHP</a>
    </span>
</div>




