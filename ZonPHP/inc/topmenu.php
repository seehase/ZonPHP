<style>
    @media screen and (min-width: 593px) {
        .dropdown .dropdown-menu.show {
            display: none !important;
        }

        .dropdown:hover .dropdown-menu {
            display: block !important;
        }

        .dropdown:focus .dropdown-menu {
            display: block !important;
        }
    }
</style>

<nav class="navbar navbar-expand-lg" style="background-color: <?= $colors['color_menubackground'] ?>;">
    <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <span class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
               aria-expanded="false" href="<?= HTML_PATH ?>index.php">
                <img src="<?= HTML_PATH ?>inc/image/logo_reverse.svg" alt="ZonPHP logo" height="28">
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="<?= HTML_PATH ?>index.php">Home</a>
                </li>
                <hr>
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
                       href="<?= HTML_PATH ?>pages/top31.php"><?= getTxt("chart_31days") ?></a></li>
            </ul>

                        </span>
            <span class="navbar-nav me-auto mb-2 mb-lg-0">
                &nbsp;&nbsp;<span id="headerinverter" style>  <?= $params['plant']['name'] ?> </span>
            </span>

            <span class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                   aria-expanded="false">
                    <img src="<?= HTML_PATH ?>inc/image/themes.svg" class="flag" alt="English" title="English">
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a class="dropdown-item" href='?theme=user'>User</a></li>
                    <li><a class="dropdown-item" href='?theme=default'>ZonPHP&nbsp;Default</a></li>
                    <li><a class="dropdown-item" href='?theme=theme1'>DarkGreyFire</a></li>
                    <li><a class="dropdown-item" href='?theme=theme2'>Julia</a></li>
                    <li><a class="dropdown-item" href='?theme=theme3'>Fire</a></li>
                    <li><a class="dropdown-item" href='?theme=theme4'>blue</a></li>
                    <hr>
                    <li><a class="dropdown-item"
                           href="<?= HTML_PATH ?>inc/destroy.php"><?= getTxt("clearsession") ?> </a>
                    </li>
                </ul>
            </span>
            &nbsp;&nbsp;
            <span class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                   aria-expanded="false">
                    <img src="<?= HTML_PATH ?>inc/image/<?= $language ?>.svg" class="flag" alt="English"
                         title="English">
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a class="dropdown-item" href="?language=en" onclick="target='_self'">
                            <img src="<?= HTML_PATH ?>inc/image/en.svg" class="flag" alt="English" title="English">&nbsp;English
                    </li>
                    <li><a class="dropdown-item" href="?language=de" onclick="target='_self'">
                            <img src="<?= HTML_PATH ?>inc/image/de.svg" class="flag" alt="Deutsch" title="Deutsch">&nbsp;Deutsch
                    </li>
                    <li><a class="dropdown-item" href="?language=nl" onclick="target='_self'">
                            <img src="<?= HTML_PATH ?>inc/image/nl.svg" class="flag" alt="Nederlands"
                                 title="Nederlands">&nbsp;Nederlands
                    </li>
                    <li><a class="dropdown-item" href="?language=fr" onclick="target='_self'">
                            <img src="<?= HTML_PATH ?>inc/image/fr.svg" class="flag" alt="Francaise"
                                 title="Francaise">&nbsp;Francaise
                    </li>
                </ul>
            </span>
        </div>
    </div>
</nav>
