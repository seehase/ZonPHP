<?php

function loadParams(): array
{
    global $params;

    $iniString = readParameterFile();
    $params = parse_ini_string("$iniString",  true);

    vadidateParams($params);
    $_SESSION['params'] = $params;
    if ($params['check']['failed']) {
        header('location:' . HTML_PATH . 'pages/validate.php');
    }

    return $params;
}

function vadidateParams(&$params): void
{
    $params['check']['ERROR'] = array();
    $params['check']['WARN'] = array();
    $params['check']['INFO'] = array();
    $params['check']['failed'] = false;

    vadidateParamsGeneral($params);
    vadidateLayout($params);
    vadidateDatabse($params);
    vadidateImages($params);
    vadidateWeewx($params);
    vadidateEMU($params);
    $params['farm']['importer'] = $params['importer'];
    $params['farm']['installationDate'] = $params['installationDate'];
    vadidateFarm($params);

    $plantNames = preg_split('/\s*,\s*/', trim($params['plantNames']));
    $params['PLANT_NAMES'] = $plantNames;
    $_SESSION['PLANT_NAMES'] = $plantNames;

    vadidatePlants($params);

    $totalExpectedYield = 0.0;
    $expectedYield = array();
    $totalExpectedMonth = array();
    foreach ($plantNames as $plantName) {
        vadidatePlant($plantName, $params[$plantName]);
        $params['farm']['plants'][$plantName] = $params[$plantName];
        $params['PLANTS'][$plantName] = $params[$plantName];
        $params['PLANTS_KWP'][] = intval($params[$plantName]['capacity']);
        $totalExpectedMonth[0][$plantName] = 0;
        $values = json_decode('[' . $params[$plantName]['expectedYield'] . ']', true);
        $validatedValues = vadidateExpectedYield($plantName, $values);
        $params[$plantName]['referenceYield'] = $validatedValues;
        $totalSum = array_sum($validatedValues);
        foreach ($validatedValues as $id => $value) {
            $totalExpectedMonth[$id + 1][$plantName] = $value;
        }
        $params[$plantName]['totalExpectedYield'] = $totalSum;
        $totalExpectedYield += $totalSum;
        $expectedYield[] = $totalSum;
    }
    $params['totalExpectedMonth'] = $totalExpectedMonth;
    $params['totalExpectedYield'] = $totalExpectedYield;
    $params['expectedYield'] = $expectedYield;


    $cards = preg_split('/\s*,\s*/', trim($params['layout']['cards']));
    $_SESSION['CARDS'] = $cards;

    $params['userTheme'] = strtolower($params['userTheme']);
    $params['defaultLanguage'] = strtolower($params['defaultLanguage']);
    if (!isActive($params['defaultLanguage'])) {
        $params['defaultLanguage'] = "en";
    }
}


function vadidateParamsGeneral(&$params): void
{
    if (!isset($params['installationDate']) || !strtotime($params['installationDate'])) {
        addCheckMessage("WARN", "No installationDate or invalid date set in parameter.php, set default to '1970-01-01'");
        $params['installationDate'] = "1970-01-01";
    }
    if (!isset($params['plantNames'])) {
        addCheckMessage("ERROR", "No plants set in parameter.php, please set plantNames, default set to 'Plant1'");
        $params['plantNames'] = "Plant1";
    }
    if (!isset($params['defaultLanguage']) || !isActive($params['defaultLanguage'])) {
        addCheckMessage("INFO", "No default language, or invalid laguage set in parameter.php, set default to 'en' valid values are 'en', 'de', 'fr', 'nl' ");
        $params['defaultLanguage'] = "en";
    }
    if (!isset($params['userTheme']) || !file_exists(ROOT_DIR . "/themes/" . strtolower($params['userTheme']) . ".theme")) {
        addCheckMessage("INFO", "No userTheme set in parameter.php, or theme not found: set default to 'default'");
        $params['userTheme'] = "default";
    }
    if (!isset($params['displayInterval']) || intval($params['displayInterval']) == 0) {
        addCheckMessage("INFO", "No displayInterval set in parameter.php set default to '5'");
        $params['displayInterval'] = "5";
    }
    if (!isset($params['coefficient']) || intval($params['coefficient']) == 0) {
        addCheckMessage("INFO", "No coefficient set in parameter.php set default to '1'");
        $params['coefficient'] = "1";
    }
    if (!isset($params['importer']) || !file_exists(ROOT_DIR . "/importer/" . $params['importer'] . ".php")) {
        addCheckMessage("INFO", "No importer set in parameter.php set default to 'none'");
        $params['importer'] = "none";
    }
    if (!isset($params['autoReload']) || intval($params['autoReload']) < 0) {
        addCheckMessage("INFO", "No autoReload set in parameter.php set default to '300'");
        $params['autoReload'] = "300";
    }
    if (!isset($params['useWeewx'])) {
        addCheckMessage("INFO", "No useWeewx set in parameter.php set default to 'false'");
        $params['useWeewx'] = false;
    }
    if (!isset($params['useEMU'])) {
        addCheckMessage("INFO", "useEMU not set in parameter.php set default to 'false'");
        $params['useEMU'] = false;
    }
    if (!isset($params['checkVersion']) || boolval($params['checkVersion']) == 0) {
        addCheckMessage("INFO", "No checkVersion set in parameter.php set default to 'false'");
        $params['checkVersion'] = false;
    }
    if (!isset($params['googleTrackingId'])) {
        addCheckMessage("INFO", "No googleTrackingId set in parameter.php set default to ''");
        $params['googleTrackingId'] = "";
    }
    if (isset($params['overwrite_HTML_PATH']) && strlen($params['overwrite_HTML_PATH']) > 0) {
        // overwrite HTML_PATH
        addCheckMessage("INFO", "HTML_PATH overwritten and set to '" . $params['overwrite_HTML_PATH'] . "'");
        define('HTML_PATH', $params['overwrite_HTML_PATH']);
    }
}

function vadidateLayout(&$params): void
{
    if (!isset($params['layout']) || !isset($params['layout']['cards'])) {
        addCheckMessage("INFO", "No card layout defined in parameter.php set default to 'false'. Set to default values, to configure, set parameter accordingly");
        $params['layout']['cards'] = "day, month, year, allYears, cumulative, yearPerMonth, top, farm, plants, images";
    } else {
        $cards = preg_split('/\s*,\s*/', strtolower(trim($params['layout']['cards'])));
        foreach ($cards as $card) {
            switch ($card) {
                case "day":
                case "month":
                case "year":
                case "allyears":
                case "cumulative":
                case "yearpermonth":
                case "farm":
                case "images":
                case "top":
                case "plants":
                    break;
                default:
                    addCheckMessage("INFO", "unknown card: '" . $card . "' found, will be ignored");
            }
        }
    }
}

function vadidateDatabse(&$params): void
{
    if (!isset($params['database'])) {
        addCheckMessage("ERROR", "No database section found in parameters.php, please check settings", true);
    }
    if (!isset($params['database']['host'])) {
        addCheckMessage("ERROR", "['database']['host'] not set in parameter.php, please check settings", true);
    }
    if (!isset($params['database']['username'])) {
        addCheckMessage("ERROR", "['database']['username'] not set in parameter.php, please check settings", true);
    }
    if (!isset($params['database']['password'])) {
        addCheckMessage("ERROR", "['database']['password'] not set in parameter.php, please check settings", true);
    }
    if (!isset($params['database']['database'])) {
        addCheckMessage("ERROR", "['database']['database'] not set in parameter.php, please check settings", true);
    }
    if (!isset($params['database']['tablePrefix'])) {
        addCheckMessage("WARN", "['database']['tablePrefix'] not set in parameter.php, default set to 'tgeg'");
        $params['database']['tablePrefix'] = "tgeg";
    }
}

function vadidateWeewx(&$params): void
{
    if ($params['useWeewx'] && !isset($params['weewx'])) {
        addCheckMessage("ERROR", "No weewx section found and useWeewx = true, please check settings", true);
    } elseif ($params['useWeewx']) {
        if (!isset($params['weewx']['host'])) {
            addCheckMessage("ERROR", "['weewx']['host'] not set in parameter.php, please check settings", true);
        }
        if (!isset($params['weewx']['username'])) {
            addCheckMessage("ERROR", "['weewx']['username'] not set in parameter.php, please check settings", true);
        }
        if (!isset($params['weewx']['password'])) {
            addCheckMessage("ERROR", "['weewx']['password'] not set in parameter.php, please check settings", true);
        }
        if (!isset($params['weewx']['database'])) {
            addCheckMessage("ERROR", "['weewx']['database'] not set in parameter.php, please check settings", true);

        }
        if (!isset($params['weewx']['tableName'])) {
            addCheckMessage("INFO", "['weewx']['tableName'] not set in parameter.php, default set to 'archive'");
            $params['weewx']['tableName'] = "archive";
        }
        if (!isset($params['weewx']['tempColumn'])) {
            addCheckMessage("INFO", "['weewx']['tempColumn'] not set in parameter.php, default set to 'outTemp'");
            $params['weewx']['tempColumn'] = "outTemp";
        }
        if (!isset($params['weewx']['timestampColumn'])) {
            addCheckMessage("INFO", "['database']['timestampColumn'] not set in parameter.php, default set to 'dateTime'");
            $params['weewx']['timestampColumn'] = "dateTime";
        }
        if (!isset($params['weewx']['tempInFahrenheit'])) {
            addCheckMessage("INFO", "['database']['tempInFahrenheit'] not set in parameter.php, default set to 'true'");
            $params['weewx']['tempInFahrenheit'] = true;
        }
    }
}

function vadidateEMU($params): void
{
    if ($params['useEMU'] && !isset($params['EMU'])) {
        addCheckMessage("ERROR", "No EMU section found and useEMU = true, please check settings", true);
    } elseif ($params['useEMU']) {
        if (!isset($params['EMU']['path'])) {
            addCheckMessage("ERROR", "['EMU']['path'] not set in parameter.php, please check settings", true);
        }
        if (!isset($params['EMU']['offset'])) {
            addCheckMessage("ERROR", "['EMU']['offset'] not set in parameter.php, please check settings", true);
        }
        if (!isset($params['EMU']['webRoot'])) {
            addCheckMessage("ERROR", "['EMU']['webRoot']  not set in parameter.php, please check settings", true);
        }
        if (!isset($params['EMU']['webRoot'])) {
            addCheckMessage("ERROR", "['EMU']['webRoot']  not set in parameter.php, please check settings", true);
        }
        if (!isset($params['EMU']['PVO_API'])) {
            addCheckMessage("INFO", "['EMU']['PVO_API']  not set in parameter.php, please check settings", true);
        }
        if (!isset($params['EMU']['PVO_SYS_ID'])) {
            addCheckMessage("INFO", "['EMU']['PVO_SYS_ID']  not set in parameter.php, please check settings", true);
        }
    }
}

function vadidateFarm(&$params): void
{
    if (!isset($params['farm'])) {
        addCheckMessage("INFO", "['farm'] not set in parameter.php, using empty defaults");
        $params['farm']['name'] = "ZonPHP";
        $params['farm']['website'] = "";
        $params['farm']['location'] = "";
        $params['farm']['totalCapacity'] = "";
    }
    if (!isset($params['farm']['name'])) {
        addCheckMessage("INFO", "['farm']['name'] not set in parameter.php, setting default 'ZonPHP'");
        $params['farm']['name'] = "ZonPHP";
    }
    if (!isset($params['farm']['website'])) {
        addCheckMessage("INFO", "['farm']['website'] not set in parameter.php, setting default ''");
        $params['farm']['website'] = "";
    }
    if (!isset($params['farm']['location'])) {
        addCheckMessage("INFO", "['farm']['location'] not set in parameter.php, setting default ''");
        $params['farm']['location'] = "";
    }
    if (!isset($params['farm']['totalCapacity'])) {
        addCheckMessage("INFO", "['farm']['totalCapacity'] not set in parameter.php, setting default ''");
        $params['farm']['totalCapacity'] = "";
    }
}

function vadidatePlants(&$params): void
{
    foreach ($params['PLANT_NAMES'] as $plant) {
        if (!isset($params[$plant])) {
            addCheckMessage("ERROR", "['" . $plant . "'] section not set in parameter.php, setting default values");
            $params[$plant] = array();
        }
    }
}

function vadidatePlant($name, &$plant): void
{
    if (!isset($plant['name'])) {
        addCheckMessage("INFO", "['" . $name . "']['name'] not set in parameter.php, setting default ''");
        $plant['name'] = "";
    }
    if (!isset($plant['installationDate'])) {
        addCheckMessage("INFO", "['" . $name . "']['installationDate'] not set in parameter.php, setting default '1970-01-01'");
        $plant['installationDate'] = "1970-01-01";
    }
    if (!isset($plant['capacity'])) {
        addCheckMessage("INFO", "['" . $name . "']['capacity'] not set in parameter.php, setting default '1'");
        $plant['capacity'] = 1;
    }
    if (!isset($plant['importPrefix'])) {
        addCheckMessage("INFO", "['" . $name . "']['importPrefix'] not set in parameter.php, setting default ''");
        $plant['importPrefix'] = "";
    }
    if (!isset($plant['image'])) {
        addCheckMessage("INFO", "['" . $name . "']['image'] not set in parameter.php, setting default ''");
        $plant['image'] = "";
    }
    if (!isset($plant['website'])) {
        addCheckMessage("INFO", "['" . $name . "']['website'] not set in parameter.php, setting default ''");
        $plant['website'] = "";
    }
    if (!isset($plant['panels'])) {
        addCheckMessage("INFO", "['" . $name . "']['panels'] not set in parameter.php, setting default ''");
        $plant['panels'] = "";
    }
    if (!isset($plant['inverter'])) {
        addCheckMessage("INFO", "['" . $name . "']['inverter'] not set in parameter.php, setting default ''");
        $plant['inverter'] = "";
    }
    if (!isset($plant['orientation'])) {
        addCheckMessage("INFO", "['" . $name . "']['orientation'] not set in parameter.php, setting default ''");
        $plant['orientation'] = "";
    }
    if (!isset($plant['location'])) {
        addCheckMessage("INFO", "['" . $name . "']['location'] not set in parameter.php, setting default ''");
        $plant['location'] = "";
    }
    if (!isset($plant['expectedYield'])) {
        addCheckMessage("INFO", "['" . $name . "']['expectedYield'] not set in parameter.php, setting default values ''");
        $plant['expectedYield'] = "170,200,300,500,550,600,600,550,500,300,200,170";
    }
    if (!isset($plant['description'])) {
        addCheckMessage("INFO", "['" . $name . "']['description'] not set in parameter.php, setting default ''");
        $plant['description'] = "";
    }
}

function vadidateExpectedYield($name, $values)
{
    $default = array([170, 200, 300, 500, 550, 600, 600, 550, 500, 300, 200, 170]);
    if (count($values) != 12) {
        addCheckMessage("WARN", "['" . $name . "']['expectedYield'] does not contain a value per month, setting default values");
        return $default;
    } else {
        $failed = false;
        foreach ($values as $value) {
            if (!is_int($value)) {
                addCheckMessage("WARN", "['" . $name . "']['expectedYield'] does not contain illegal value: $value, setting default values");
                $failed = true;
            }
        }
        if ($failed) {
            return $default;
        } else {
            return $values;
        }
    }
}

function vadidateImages(&$params): void
{
    if (isset($params['images'])) {
        foreach ($params['images'] as $key => $image) {
            if (!isset($image['title'])) {
                addCheckMessage("INFO", "['images']['" . $key . "']['title'] not set in parameter.php, setting default ''");
                $image['title'] = "";
            }
            if (!isset($image['description'])) {
                addCheckMessage("INFO", "['images']['" . $key . "']['description'] not set in parameter.php, setting default ''");
                $image['description'] = "";
            }
            if (!isset($image['uri'])) {
                addCheckMessage("INFO", "['images']['" . $key . "']['uri'] not set in parameter.php, setting default ''");
                $image['uri'] = "";
            }
        }
    } else {
        $params['images'] = array();
    }
}
