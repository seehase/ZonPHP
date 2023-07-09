<?php
global $params, $colors, $new_version_label;
?>
<nav class="navbar navbar-expand-sm" style="background-color: <?= $colors['color_menubackground'] ?>;">
    <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                   aria-expanded="false" href="<?= HTML_PATH ?>index.php">
                    <img src="<?= HTML_PATH ?>inc/images/logo_reverse.svg" alt="ZonPHP logo" height="28">
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item"
                           href="<?= HTML_PATH ?>index.php"><?= getTxt("index") ?></a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                           href="<?= HTML_PATH ?>pages/day_overview.php"><?= getTxt("chart_dayoverview") ?></a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                           href="<?= HTML_PATH ?>pages/month_overview.php"><?= getTxt("chart_monthoverview") ?></a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                           href="<?= HTML_PATH ?>pages/year_overview.php"><?= getTxt("chart_yearoverview") ?></a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                           href="<?= HTML_PATH ?>pages/all_years_overview.php"><?= getTxt("chart_allyearoverview") ?></a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                           href="<?= HTML_PATH ?>pages/last_years_overview.php"><?= getTxt("chart_lastyearoverview") ?></a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                           href="<?= HTML_PATH ?>pages/cumulative_overview.php"><?= getTxt("chart_cumulativeoverview") ?></a>
                    </li>
                    <li><a class="dropdown-item"
                           href="<?= HTML_PATH ?>pages/top31.php"><?= getTxt("chart_31days") ?></a>
                    </li>
                </ul>

            </div>
            <span class="navbar-nav me-auto mb-2 mb-lg-0">
                &nbsp;&nbsp;<a id="headerinverter" href="<?= HTML_PATH ?>">  <?= $params['farm']['name'] ?> </a>
            </span>

            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                   aria-expanded="false">
                    <img src="<?= HTML_PATH ?>inc/images/themes_4x3.svg" class="theme" alt="Theme" title="Theme">
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <?php
                    $themes = $_SESSION['themes'];
                    foreach ($themes as $key => $theme) {
                        echo "\t" . '<li><a class="dropdown-item" href="?theme=' . $key . '" onclick="target=\'_self\'">' . $theme['info']['name'] . '</a></li>' . "\r\n";
                    }
                    ?>
                </ul>
            </div>
            &nbsp;&nbsp;
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                   aria-expanded="false">
                    <img src="<?= HTML_PATH ?>inc/images/<?= $_SESSION['language'] ?>.svg" class="flag" alt="English"
                         title="English">
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a class="dropdown-item" href="?language=en" onclick="target='_self'">
                            <img src="<?= HTML_PATH ?>inc/images/en.svg" class="flag" alt="English" title="English">&nbsp;English</a>
                    </li>
                    <li><a class="dropdown-item" href="?language=de" onclick="target='_self'">
                            <img src="<?= HTML_PATH ?>inc/images/de.svg" class="flag" alt="Deutsch" title="Deutsch">&nbsp;Deutsch</a>
                    </li>
                    <li><a class="dropdown-item" href="?language=nl" onclick="target='_self'">
                            <img src="<?= HTML_PATH ?>inc/images/nl.svg" class="flag" alt="Nederlands"
                                 title="Nederlands">&nbsp;Nederlands</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="?language=fr" onclick="target='_self'">
                            <img src="<?= HTML_PATH ?>inc/images/fr.svg" class="flag" alt="Français"
                                 title="Français">&nbsp;Français</a>
                    </li>
                </ul>
            </div>
            &nbsp;&nbsp;
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                   aria-expanded="false">&nbsp;&nbsp;?&nbsp;
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a class="dropdown-item"
                           href="<?= HTML_PATH ?>inc/destroy.php"><?= getTxt("clearsession") ?> </a>
                    </li>
                    <li><a class="dropdown-item"
                           href="<?= HTML_PATH ?>pages/validate.php"><?= getTxt("validateparams") ?> </a>
                    </li>
                    <?php
                    if (strlen($new_version_label) > 0) {
                        $newversion = getTxt("newversion");
                        echo <<<EOT
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" onclick="target='_blank'"
                           href="https://github.com/seehase/ZonPHP/releases">$newversion</a>
                        </li>
                      EOT;
                    }
                    ?>

                </ul>
            </div>
        </div>
    </div>
</nav>
