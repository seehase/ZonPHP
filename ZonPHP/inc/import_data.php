<?php
switch ($params['importer']) {
    case "none":
        break;
    case "Invullen_gegevens_xls":
        include ROOT_DIR."/importer/Invullen_gegevens_xls.php";
        break;
    case "Invullen_gegevens_suo":
        include ROOT_DIR."/importer/Invullen_gegevens_suo.php";
        break;
    case "Invullen_gegevens_suo_custom2":
        include ROOT_DIR."/importer/Invullen_gegevens_suo2.php";
        break;
    case "Invullen_gegevens_solarlog":
        include ROOT_DIR."/importer/Invullen_gegevens_solarlog.php";
        break;
    case "Invullen_gegevens_solarlogjs":
        include ROOT_DIR."/importer/Invullen_gegevens_solarlogjs.php";
        break;
    case "Invullen_gegevens_solarlogXomvorm":
        include ROOT_DIR."/importer/Invullen_gegevens_solarlogXomvorm.php";
        break;
    case "Invullen_gegevens_solarlogXomvormjs":
        include ROOT_DIR."/importer/Invullen_gegevens_solarlogXomvormjs.php";
        break;
    case "Invullen_gegevens_sunny_explorer":
        include ROOT_DIR."/importer/Invullen_gegevens_sunny_explorer.php";
        break;
    case "Invullen_gegevens_sunny_explorer_2WR":
        include ROOT_DIR."/importer/Invullen_gegevens_sunny_explorer_2WR.php";
        break;
    case "sunny_explorer_seehase":
        include ROOT_DIR."/importer/sunny_explorer_seehase.php";
        break;
    case "sunny_explorer_utf16":
        include ROOT_DIR."/importer/Invullen_gegevens_sunny_explorer_utf16.php";
        break;
    case "sunny_explorer_thomas":
        include ROOT_DIR."/importer/Invullen_gegevens_sunny_explorer_thomas.php";
        break;
    case "sunny_explorer_manuel":
        include ROOT_DIR."/importer/Invullen_gegevens_sunny_explorer_manuel.php";
        break;
    case "Invullen_gegevens_sunnybeam_bt":
        include ROOT_DIR."/importer/Invullen_gegevens_sunnybeam_bt.php";
        break;
    case "Invullen_gegevens_sunny_webbox_csv":
        include ROOT_DIR."/importer/Invullen_gegevens_sunny_webbox_csv.php";
        break;
    case "Import_sunny_webbox_csv_thorsten":
        include ROOT_DIR."/importer/sunny_webbox_csv_thorsten.php";
        break;
    case "Import_von_Hyperion":
        include ROOT_DIR."/importer/Import_von_Hyperion.php";
        break;
    case "Import_von_Hyperion_christian":
        include ROOT_DIR."/importer/stromzaehler.php";
        break;
    default:
        echo 'Verkeerd';
        break;
}
?>
