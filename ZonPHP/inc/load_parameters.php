<?php

function loadParams()
{
    $params = parse_ini_file(ROOT_DIR . "/parameters.php", true);
    vadidateParams($params);
    $_SESSION['params'] = $params;
    return $params;
}

// FIXME: enhance validation
//   style-cards: remove duplicates, if null add all
//   importer check (ifExists)
/**
 *
 * Check parameter, and set defaults.
 * @param $params
 * @return void
 */


function vadidateParams(&$params)
{
    $params['plant']['importer'] = $params['importer'];
    $params['plant']['installationDate'] = $params['installationDate'];

    $plants = preg_split('/\s*,\s*/', trim($params['plants']));
    $_SESSION['PLANTS'] = $plants;

    $totalExpectedYield = 0.0;
    $expectedYield = array();
    $totalExpectedMonth = array();
    foreach ($plants as $plant) {
        $totalExpectedMonth[0][$plant] = 0;
        $values = json_decode('[' . $params[$plant]['expectedYield'] . ']', true);
        $params[$plant]['referenceYield'] = $values;
        $totalSum = array_sum($values);
        foreach ($values as $id => $value) {
            $totalExpectedMonth[$id + 1][$plant] = $value;
        }
        $params[$plant]['totalExpectedYield'] = $totalSum;
        $totalExpectedYield += $totalSum;
        $expectedYield[] = $totalSum;
    }
    $params['totalExpectedMonth'] = $totalExpectedMonth;
    $params['totalExpectedYield'] = $totalExpectedYield;
    $params['expectedYield'] = $expectedYield;


    $cards = preg_split('/\s*,\s*/', trim($params['layout']['cards']));
    $_SESSION['CARDS'] = $cards;
    // fixme: check if theme exists, else "default"
    $params['userTheme'] = strtolower($params['userTheme']);
    $params['defaultLanguage'] = strtolower($params['defaultLanguage']);
    if (!isActive($params['defaultLanguage'])) {
        $params['defaultLanguage'] = "en";
    }
}
