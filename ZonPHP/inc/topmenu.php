<a href='<?php echo HTML_PATH ?>/index.php' style="position:absolute; top:16px; left:80px;  border:0"><img src="<?php echo HTML_PATH ?>/inc/image/logo.png" alt="ZonPHP logo"></a>
<?php if ($show_menu) echo '<label for="main-nav-check"  class="toggle" onclick="" title="Menu">&#x2261;</label>'; ?>
        <?php if (isset($param['lang_nl'])) echo "<a href='?taal=nl' onclick=\"target='_self'\"><img src='". HTML_PATH ."/inc/image/blank.gif' 
 class='flag flag-nl' alt='Nederlands' title='Nederlands'></a>"; ?>
<?php if (isset($param['lang_en'])) echo "<a href='?taal=en' onclick=\"target='_self'\"><img src='". HTML_PATH ."/inc/image/blank.gif'
 class='flag flag-gb' alt='english' title='english'></a>" ?>
<?php if (isset($param['lang_fr'])) echo "<a href='?taal=fr' onclick=\"target='_self'\"><img src='". HTML_PATH ."/inc/image/blank.gif'
 class='flag flag-fr' alt='français' title='français'></a>" ?>
<?php if (isset($param['lang_de'])) echo "<a href='?taal=de' onclick=\"target='_self'\"><img src='". HTML_PATH ."/inc/image/blank.gif'
 class='flag flag-de' alt='deutsch' title='deutsch'></a>" ?>
<?php
if ($iveromvormers == 1) {
echo '<p id="headerinverter" style="vertical-align: middle; position:absolute; top:16px; left:360px;">' .
$param['sNaamVoorOpWebsite'] .
                '</p>';
        }
$ligado = 0;
?>