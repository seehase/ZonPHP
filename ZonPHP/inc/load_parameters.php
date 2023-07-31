<?php

function loadParams($htmlpath): array
{
    global $params;

    $iniString = readParameterFile();
    $params = parse_ini_string("$iniString", true);

    vadidateParams($params);
    $_SESSION['params'] = $params;
    if ($params['check']['failed']) {
        header('location:$htmlpath' . $htmlpath . 'pages/validate.php');
    }

    return $params;
}

function vadidateParams(&$params): void
{
    $params['check']['ERROR'] = array();
    $params['check']['WARN'] = array();
    $params['check']['INFO'] = array();
    $params['check']['failed'] = false;

    vadidateEMU($params);
    vadidateParamsGeneral($params);
    vadidateLayout($params);
    vadidateDatabse($params);
    vadidateImages($params);
    vadidateWeewx($params);
    $params['farm']['importer'] = $params['importer'];

    $plantNames = preg_split('/\s*,\s*/', trim($params['plantNames']));
    $params['PLANT_NAMES'] = $plantNames;
    $_SESSION['PLANT_NAMES'] = $plantNames;

    vadidatePlants($params);

    $totalExpectedYield = 0.0;
    $expectedYield = array();
    $totalExpectedMonth = array();
    $totalcapacity = 0;
    foreach ($plantNames as $plantName) {
        vadidatePlant($plantName, $params[$plantName]);
        $params['farm']['plants'][$plantName] = $params[$plantName];
        $params['PLANTS'][$plantName] = $params[$plantName];
        $params['PLANTS_KWP'][] = intval($params[$plantName]['capacity']);
        $totalcapacity += intval($params[$plantName]['capacity']);
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
    $params['totalCapacity'] = $totalcapacity;

    vadidateFarm($params);

    $cards = preg_split('/\s*,\s*/', trim($params['cards']));
    $_SESSION['CARDS'] = $cards;

    $params['userTheme'] = strtolower($params['userTheme']);
    $params['defaultLanguage'] = strtolower($params['defaultLanguage']);
    if (!isValidLanguage($params['defaultLanguage'])) {
        $params['defaultLanguage'] = "en";
    }
}


function vadidateParamsGeneral(&$params): void
{
    if (!isset($params['plantNames'])) {
        addCheckMessage("ERROR", "No plants set in parameter.php, please set plantNames, default set to 'Plant1'");
        $params['plantNames'] = "Plant1";
    }
    if (!isset($params['defaultLanguage']) || !isValidLanguage($params['defaultLanguage'])) {
        addCheckMessage("INFO", "No default language, or invalid laguage set in parameter.php, set default to 'en' valid values are 'en', 'de', 'fr', 'nl' ");
        $params['defaultLanguage'] = "en";
    }
    if (!isset($params['timeZone']) || !isValidTimezoneId($params['timeZone'])) {
        addCheckMessage("INFO", "No timezone , or invalid timezone set in parameter.php, set default to 'UTC'");
        $params['timeZone'] = "UTC";
    }
    if (!isset($params['userTheme']) || !file_exists(ROOT_DIR . "/themes/" . strtolower($params['userTheme']) . ".theme")) {
        addCheckMessage("INFO", "No userTheme set in parameter.php, or theme not found: set default to 'default'");
        $params['userTheme'] = "default";
    }
    if (!isset($params['importer']) || !file_exists(ROOT_DIR . "/importer/" . $params['importer'] . ".php")) {
        addCheckMessage("INFO", "No importer set in parameter.php set default to 'none'");
        $params['importer'] = "none";
    }
    if (!isset($params['autoReload']) || intval($params['autoReload']) < 0) {
        addCheckMessage("INFO", "No autoReload set in parameter.php set default to '300'");
        $params['autoReload'] = "300";
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
    if (!isset($params['importLocalDateAsUTC'])) {
        addCheckMessage("INFO", "'importLocalDateAsUTC' not set in parameter.php, set to default = false");
        $params['importLocalDateAsUTC'] = false;
    }

}

function vadidateLayout(&$params): void
{
    if (!isset($params['cards'])) {
        addCheckMessage("INFO", "No card layout defined in parameter.php set default to 'false'. Set to default values, to configure, set parameter accordingly");
        $params['cards'] = "day, month, year, allYears, cumulative, yearPerMonth, top, farm, plants, images";
    } else {
        $cards = preg_split('/\s*,\s*/', strtolower(trim($params['cards'])));
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
    if (!isset($params['database']['UTC_is_used'])) {
        addCheckMessage("WARN", "['database']['UTC_is_used'] not set in parameter.php, default set to 'false'");
        $params['database']['UTC_is_used'] = false;
    }
}

function vadidateWeewx(&$params): void
{
    if (isset($params['weewx']['enabled'])) {
        $params['useWeewx'] = $params['weewx']['enabled'];
    } else {
        $params['useWeewx'] = false;
    }

    if ($params['useWeewx']) {
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
    if (isset($params['EMU']['enabled'])) {
        $params['useEMU'] = $params['EMU']['enabled'];
    } else {
        $params['useEMU'] = false;
    }

    if ($params['useEMU']) {
        // no importer required set to none
        $params['importer'] = "none";
        // check other params
        if (!isset($params['EMU']['path_CSV_data'])) {
            addCheckMessage("ERROR", "['EMU']['path_CSV_data'] not set in parameter.php, please check settings", true);
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
    $params['farm']['name'] = $params['name'];
    $params['farm']['website'] = $params['website'];
    $params['farm']['location'] = $params['location'];
    $params['farm']['totalCapacity'] = $params['totalCapacity'];
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
    if (!isset($plant['capacity'])) {
        addCheckMessage("INFO", "['" . $name . "']['capacity'] not set in parameter.php, setting default '1'");
        $plant['capacity'] = 1;
    }
    if (!isset($plant['importPrefix'])) {
        addCheckMessage("INFO", "['" . $name . "']['importPrefix'] not set in parameter.php, setting default ''");
        $plant['importPrefix'] = "";
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
