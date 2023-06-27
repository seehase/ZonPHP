<a href='<?= HTML_PATH ?>index.php' style="position:absolute; top:16px; left:80px;  border:0"><img
            src="<?php echo HTML_PATH ?>inc/image/logo.svg" alt="ZonPHP logo" height="28"></a>
<?php if ($show_menu) echo '<label for="main-nav-check"  class="toggle" onclick="" title="Menu">&#x2261;</label>'; ?>
<?php if (isActive('nl')) echo "<a href='?language=nl' onclick=\"target='_self'\"><img src='" . HTML_PATH . "inc/image/blank.gif' 
 class='flag flag-nl' alt='Nederlands' title='Nederlands'></a>"; ?>
<?php if (isActive('en')) echo "<a href='?language=en' onclick=\"target='_self'\"><img src='" . HTML_PATH . "inc/image/blank.gif'
 class='flag flag-gb' alt='english' title='english'></a>" ?>
<?php if (isActive('fr')) echo "<a href='?language=fr' onclick=\"target='_self'\"><img src='" . HTML_PATH . "inc/image/blank.gif'
 class='flag flag-fr' alt='français' title='français'></a>" ?>
<?php if (isActive('de')) echo "<a href='?language=de' onclick=\"target='_self'\"><img src='" . HTML_PATH . "inc/image/blank.gif'
 class='flag flag-de' alt='deutsch' title='deutsch'></a>" ?>
<?php
echo '<p id="headerinverter" style="vertical-align: middle; position:absolute; top:16px; left:360px;">' .
    $params['plant']['name'] . '</p>';
?>
