<?php
/**
 * Created by PhpStorm.
 * User: Holger
 * Date: 17.12.2016
 * Time: 17:58
 */
if ($debugmode) error_log("calling load_colors");

if (!isset($_SESSION['theme'])) {
    if (isset($colors) && isset($colors['colortheme'])) {
        $_SESSION['theme'] = $colors['colortheme'];
    } else {
        $_SESSION['theme'] = "user";
    }
}

if (isset($_GET['theme'])) {
    $_SESSION['theme'] = $_GET['theme'];
    include_once "load_parameters.php";
}

if (!isset($colors)) {
    $colors = array();
}

// fallback for upgrading users
if (!isset ($colors['colortheme'])) $colors['colortheme'] = "user";
if (!isset ($colors['color_chartbackground'])) $colors['color_chartbackground'] = "888888";
if (!isset ($colors['color_background'])) $colors['color_background'] = "888888";
if (!isset ($colors['color_footerbackground'])) $colors['color_footerbackground'] = "C00000";
if (!isset ($colors['color_menubackground'])) $colors['color_menubackground'] = "ffffff";
if (!isset ($colors['color_menufont'])) $colors['color_menufont'] = "000000";
if (!isset ($colors['color_windowfont'])) $colors['color_windowfont'] = "ffffff";
if (!isset ($colors['color_windowcolor'])) $colors['color_windowcolor'] = "000000";
if (!isset ($colors['color_chart_reference_line'])) $colors['color_chart_reference_line'] = "FF0055";
if (!isset ($colors['color_chart_max_bar'])) $colors['color_chart_max_bar'] = "777777";
if (!isset ($colors['color_chart_max_line'])) $colors['color_chart_max_line'] = "777777";
if (!isset ($colors['color_chart_cum_line'])) $colors['color_chart_cum_line'] = "06990B";
if (!isset ($colors['color_chart_temp_line'])) $colors['color_chart_temp_line'] = "0E669E";
if (!isset ($colors['color_chart_expected_bar'])) $colors['color_chart_expected_bar'] = "AAAAAA";
if (!isset ($colors['color_chart_average_line'])) $colors['color_chart_average_line'] = "0AF02F";
if (!isset ($colors['color_chartbar1'])) $colors['color_chartbar1'] = "003399";
if (!isset ($colors['color_chartbar2'])) $colors['color_chartbar2'] = "3366AA";
if (!isset ($colors['color_inverter0_chartbar_min'])) $colors['color_inverter0_chartbar_min'] = "003399";
if (!isset ($colors['color_inverter0_chartbar_max'])) $colors['color_inverter0_chartbar_max'] = "3366AA";
if (!isset ($colors['color_inverter1_chartbar_min'])) $colors['color_inverter1_chartbar_min'] = "158EFF";
if (!isset ($colors['color_inverter1_chartbar_max'])) $colors['color_inverter1_chartbar_max'] = "715AAA";
if (!isset ($colors['color_inverter2_chartbar_min'])) $colors['color_inverter2_chartbar_min'] = "1A8A99";
if (!isset ($colors['color_inverter2_chartbar_max'])) $colors['color_inverter2_chartbar_max'] = "3829AA";
if (!isset ($colors['color_inverter3_chartbar_min'])) $colors['color_inverter3_chartbar_min'] = "0F9949";
if (!isset ($colors['color_inverter3_chartbar_max'])) $colors['color_inverter3_chartbar_max'] = "3366AA";
if (!isset ($colors['color_inverter4_chartbar_min'])) $colors['color_inverter4_chartbar_min'] = "7E1D99";
if (!isset ($colors['color_inverter4_chartbar_max'])) $colors['color_inverter4_chartbar_max'] = "AA1D66";
if (!isset ($colors['color_inverter5_chartbar_min'])) $colors['color_inverter5_chartbar_min'] = "5D7499";
if (!isset ($colors['color_inverter5_chartbar_max'])) $colors['color_inverter5_chartbar_max'] = "9138AA";
if (!isset ($colors['color_inverter6_chartbar_min'])) $colors['color_inverter6_chartbar_min'] = "003399";
if (!isset ($colors['color_inverter6_chartbar_max'])) $colors['color_inverter6_chartbar_max'] = "3366AA";
if (!isset ($colors['color_inverter7_chartbar_min'])) $colors['color_inverter7_chartbar_min'] = "003399";
if (!isset ($colors['color_inverter7_chartbar_max'])) $colors['color_inverter7_chartbar_max'] = "3366AA";
if (!isset ($colors['color_inverter8_chartbar_min'])) $colors['color_inverter8_chartbar_min'] = "003399";
if (!isset ($colors['color_inverter8_chartbar_max'])) $colors['color_inverter8_chartbar_max'] = "3366AA";
if (!isset ($colors['color_inverter9_chartbar_min'])) $colors['color_inverter9_chartbar_min'] = "003399";
if (!isset ($colors['color_inverter9_chartbar_max'])) $colors['color_inverter9_chartbar_max'] = "3366AA";
if (!isset ($colors['color_chartbar_piek1'])) $colors['color_chartbar_piek1'] = "730073";
if (!isset ($colors['color_chartbar_piek2'])) $colors['color_chartbar_piek2'] = "FF00FF";
if (!isset ($colors['color_image_windowtitle'])) $colors['color_image_windowtitle'] = HTML_PATH."/inc/styles/images/bg_black.png";
if (!isset ($colors['color_chart_labels_xaxis1'])) $colors['color_chart_labels_xaxis1'] = "323070";
if (!isset ($colors['color_chart_title_yaxis1'])) $colors['color_chart_title_yaxis1'] = "1D2599";
if (!isset ($colors['color_chart_title_yaxis2'])) $colors['color_chart_title_yaxis2'] = "0E6C7A";
if (!isset ($colors['color_chart_title_yaxis3'])) $colors['color_chart_title_yaxis3'] = "565699";
if (!isset ($colors['color_chart_labels_yaxis1'])) $colors['color_chart_labels_yaxis1'] = "3D3C4F";
if (!isset ($colors['color_chart_labels_yaxis2'])) $colors['color_chart_labels_yaxis2'] = "C5D6D1";
if (!isset ($colors['color_chart_labels_yaxis3'])) $colors['color_chart_labels_yaxis3'] = "C3D6A9";
if (!isset ($colors['color_chart_gridline_yaxis1'])) $colors['color_chart_gridline_yaxis1'] = "D1B3B0";
if (!isset ($colors['color_chart_gridline_yaxis2'])) $colors['color_chart_gridline_yaxis2'] = "C8D1B2";
if (!isset ($colors['color_chart_gridline_yaxis3'])) $colors['color_chart_gridline_yaxis3'] = "FFEBF2";
if (!isset ($colors['color_chart_text_title'])) $colors['color_chart_text_title'] = "1677B0";
if (!isset ($colors['color_chart_text_subtitle'])) $colors['color_chart_text_subtitle'] = "1C567D";
if (!isset ($colors['color_yearchart0'])) $colors['color_yearchart0'] = "1F3A93";
if (!isset ($colors['color_yearchart1'])) $colors['color_yearchart1'] = "4B77BE";
if (!isset ($colors['color_yearchart2'])) $colors['color_yearchart2'] = "2574A9";
if (!isset ($colors['color_yearchart3'])) $colors['color_yearchart3'] = "89C4F4";
if (!isset ($colors['color_yearchart4'])) $colors['color_yearchart4'] = "5C97BF";
if (!isset ($colors['color_text_link1'])) $colors['color_text_link1'] = "ffffff";
if (!isset ($colors['color_text_link2'])) $colors['color_text_link2'] = "c00000";
if (!isset ($colors['color_palettes'])) $colors['color_palettes'] = array(
    array('#9ccc65', '#8bc34a', '#7cb342'),
    array('#aed6f1', '#85c1e9', '#5dade2'),
    array('#f9e79f', '#f7dc6f', '#f4d03f'),
    array('#a3e4d7', '#76d7c4', '#48c9b0'),
    array('#d7bde2', '#c39bd3', '#af7ac5'),
    array('#ef5350', '#e53935', '#c62828'));

// load themes if not
if ($_SESSION['theme'] == "default") {
    $colors['color_background'] = "888888";
    $colors['color_footerbackground'] = "C00000";
    $colors['color_menubackground'] = "ffffff";
    $colors['color_menufont'] = "000000";
    $colors['color_windowfont'] = "ffffff";
    $colors['color_windowcolor'] = "000000";
    $colors['color_image_windowtitle'] = HTML_PATH."/inc/styles/images/bg_black.png";
    $colors['color_chartbackground'] = "888888";
    $colors['color_chartbar1'] = "003399";
    $colors['color_chartbar2'] = "3366AA";
    $colors['color_inverter0_chartbar_min'] = "003399";
    $colors['color_inverter0_chartbar_max'] = "3366AA";
    $colors['color_inverter1_chartbar_min'] = "158EFF";
    $colors['color_inverter1_chartbar_max'] = "715AAA";
    $colors['color_inverter2_chartbar_min'] = "1A8A99";
    $colors['color_inverter2_chartbar_max'] = "3829AA";
    $colors['color_inverter3_chartbar_min'] = "0F9949";
    $colors['color_inverter3_chartbar_max'] = "3366AA";
    $colors['color_inverter4_chartbar_min'] = "7E1D99";
    $colors['color_inverter4_chartbar_max'] = "AA1D66";
    $colors['color_inverter5_chartbar_min'] = "5D7499";
    $colors['color_inverter5_chartbar_max'] = "9138AA";
    $colors['color_inverter6_chartbar_min'] = "003399";
    $colors['color_inverter6_chartbar_max'] = "3366AA";
    $colors['color_inverter7_chartbar_min'] = "003399";
    $colors['color_inverter7_chartbar_max'] = "3366AA";
    $colors['color_inverter8_chartbar_min'] = "003399";
    $colors['color_inverter8_chartbar_max'] = "3366AA";
    $colors['color_inverter9_chartbar_min'] = "003399";
    $colors['color_inverter9_chartbar_max'] = "3366AA";
    $colors['color_chartbar_piek1'] = "F8F804";
    $colors['color_chartbar_piek2'] = "FF00FF";
    $colors['color_chart_average_line'] = "0AF02F";
    $colors['color_chart_reference_line'] = "FF0055";
    $colors['color_chart_cum_line'] = "212121";
    $colors['color_chart_max_line'] = "777777";
    $colors['color_chart_temp_line'] = "0E669E";
    $colors['color_chart_max_bar'] = "777777";
    $colors['color_chart_expected_bar'] = "C4C4C4";
    $colors['color_chart_text_title'] = "1677B0";
    $colors['color_chart_text_subtitle'] = "1C567D";
    $colors['color_chart_labels_xaxis1'] = "323070";
    $colors['color_chart_title_yaxis1'] = "1D2599";
    $colors['color_chart_title_yaxis2'] = "0E6C7A";
    $colors['color_chart_title_yaxis3'] = "565699";
    $colors['color_chart_labels_yaxis1'] = "3D3C4F";
    $colors['color_chart_labels_yaxis2'] = "C5D6D1";
    $colors['color_chart_labels_yaxis3'] = "C3D6A9";
    $colors['color_chart_gridline_yaxis1'] = "D1B3B0";
    $colors['color_chart_gridline_yaxis2'] = "C8D1B2";
    $colors['color_chart_gridline_yaxis3'] = "FFEBF2";
    $colors['color_yearchart0'] = "1F3A93";
    $colors['color_yearchart1'] = "4B77BE";
    $colors['color_yearchart2'] = "2574A9";
    $colors['color_yearchart3'] = "89C4F4";
    $colors['color_yearchart4'] = "5C97BF";
    $colors['color_text_link1'] = "ffffff";
    $colors['color_text_link2'] = "000000";
} else if ($_SESSION['theme'] == "theme1") {
    $colors['color_background'] = "000000";
    $colors['color_footerbackground'] = "b0b0b0";
    $colors['color_menubackground'] = "b0b0b0";
    $colors['color_menufont'] = "000000";
    $colors['color_windowfont'] = "ffffff";
    $colors['color_windowcolor'] = "616161";
    $colors['color_image_windowtitle'] = HTML_PATH."/inc/styles/images/bg_black.png";
    $colors['color_chartbackground'] = "616161";
    $colors['color_chartbar1'] = "f4ff1c";
    $colors['color_chartbar2'] = "FF000D";
    $colors['color_inverter0_chartbar_min'] = "f4ff1c";
    $colors['color_inverter0_chartbar_max'] = "FF000D";
    $colors['color_inverter1_chartbar_min'] = "BECE04";
    $colors['color_inverter1_chartbar_max'] = "AA3A38";
    $colors['color_inverter2_chartbar_min'] = "E8E40E";
    $colors['color_inverter2_chartbar_max'] = "DB480D";
    $colors['color_inverter3_chartbar_min'] = "003399";
    $colors['color_inverter3_chartbar_max'] = "3366AA";
    $colors['color_inverter4_chartbar_min'] = "003399";
    $colors['color_inverter4_chartbar_max'] = "3366AA";
    $colors['color_inverter5_chartbar_min'] = "003399";
    $colors['color_inverter5_chartbar_max'] = "3366AA";
    $colors['color_inverter6_chartbar_min'] = "003399";
    $colors['color_inverter6_chartbar_max'] = "3366AA";
    $colors['color_inverter7_chartbar_min'] = "003399";
    $colors['color_inverter7_chartbar_max'] = "3366AA";
    $colors['color_inverter8_chartbar_min'] = "003399";
    $colors['color_inverter8_chartbar_max'] = "3366AA";
    $colors['color_inverter9_chartbar_min'] = "003399";
    $colors['color_inverter9_chartbar_max'] = "3366AA";
    $colors['color_chartbar_piek1'] = "ff000D";
    $colors['color_chartbar_piek2'] = "A80000";
    $colors['color_chart_average_line'] = "15ff24";
    $colors['color_chart_reference_line'] = "ff000d";
    $colors['color_chart_cum_line'] = "212121";
    $colors['color_chart_max_line'] = "454545";
    $colors['color_chart_temp_line'] = "0E669E";
    $colors['color_chart_max_bar'] = "b0b0b0";
    $colors['color_chart_expected_bar'] = "d6d6d6";
    $colors['color_chart_text_title'] = "ffffff";
    $colors['color_chart_text_subtitle'] = "ffffff";
    $colors['color_chart_labels_xaxis1'] = "ffffff";
    $colors['color_chart_title_yaxis1'] = "ffffff";
    $colors['color_chart_title_yaxis2'] = "ffffff";
    $colors['color_chart_title_yaxis3'] = "ffffff";
    $colors['color_chart_labels_yaxis1'] = "ffffff";
    $colors['color_chart_labels_yaxis2'] = "ffffff";
    $colors['color_chart_labels_yaxis3'] = "ffffff";
    $colors['color_chart_gridline_yaxis1'] = "ffffff";
    $colors['color_chart_gridline_yaxis2'] = "ffffff";
    $colors['color_chart_gridline_yaxis3'] = "ffffff";
    $colors['color_yearchart0'] = "ff000d";
    $colors['color_yearchart1'] = "f4ff1c";
    $colors['color_yearchart2'] = "ff000d";
    $colors['color_yearchart3'] = "f4ff1c";
    $colors['color_yearchart4'] = "ff000d";
    $colors['color_text_link1'] = "ffffff";
    $colors['color_text_link2'] = "c00000";
    $colors['color_palettes'] = array(
        array('#f9e79f', '#f7dc6f', '#f4d03f'),
        array('#ff9e80', '#ff6e40', '#ff3d00'),
        array('#ff7043', '#f4511e', '#d84315'),
        array('#fff176', '#ffeb3b', '#fbc02d'),
        array('#ef5350', '#e53935', '#c62828'),
        array('#b0bec5', '#607d8b', '#37474f'));
} else if ($_SESSION['theme'] == "theme2") {
    $colors['color_background'] = "381C04";
    $colors['color_footerbackground'] = "E7A75B";
    $colors['color_menubackground'] = "B8895F";
    $colors['color_menufont'] = "000000";
    $colors['color_windowfont'] = "ffffff";
    $colors['color_windowcolor'] = "FFBF87";
    $colors['color_image_windowtitle'] = HTML_PATH."/inc/styles/images/bg_blueenergy.png";
    $colors['color_chartbackground'] = "FFDCBC";
    $colors['color_chartbar1'] = "FF1C33";
    $colors['color_chartbar2'] = "700000";
    $colors['color_chartbar_piek1'] = "FFE100";
    $colors['color_chartbar_piek2'] = "FF7E38";
    $colors['color_chart_average_line'] = "B88686";
    $colors['color_chart_reference_line'] = "4B3636";
    $colors['color_chart_cum_line'] = "212121";
    $colors['color_chart_max_line'] = "805E5E";
    $colors['color_chart_temp_line'] = "0E669E";
    $colors['color_chart_max_bar'] = "B88686";
    $colors['color_chart_expected_bar'] = "C89191";
    $colors['color_chart_text_title'] = "1677B0";
    $colors['color_chart_text_subtitle'] = "1C567D";
    $colors['color_chart_labels_xaxis1'] = "4FA2D6";
    $colors['color_chart_title_yaxis1'] = "1D2599";
    $colors['color_chart_title_yaxis2'] = "0E6C7A";
    $colors['color_chart_title_yaxis3'] = "565699";
    $colors['color_chart_labels_yaxis1'] = "3D3C4F";
    $colors['color_chart_labels_yaxis2'] = "C5D6D1";
    $colors['color_chart_labels_yaxis3'] = "C3D6A9";
    $colors['color_chart_gridline_yaxis1'] = "D1B3B0";
    $colors['color_chart_gridline_yaxis2'] = "C8D1B2";
    $colors['color_chart_gridline_yaxis3'] = "FFEBF2";
    $colors['color_yearchart0'] = "FF00D4";
    $colors['color_yearchart1'] = "000000";
    $colors['color_yearchart2'] = "620993";
    $colors['color_yearchart3'] = "480458";
    $colors['color_yearchart4'] = "9A01BE";
    $colors['color_text_link1'] = "ffffff";
    $colors['color_text_link2'] = "c00000";

} else if ($_SESSION['theme'] == "theme3") {
    $colors['color_background'] = "ffffff";
    $colors['color_footerbackground'] = "ffffff";
    $colors['color_menubackground'] = "ffffff";
    $colors['color_menufont'] = "000000";
    $colors['color_windowfont'] = "ffffff";
    $colors['color_windowcolor'] = "ffffff";
    $colors['color_image_windowtitle'] = HTML_PATH."/inc/styles/images/bg_black.png";
    $colors['color_chartbackground'] = "ffffff";
    $colors['color_chartbar1'] = "f4ff1c";
    $colors['color_chartbar2'] = "ff000d";
    $colors['color_chartbar_piek1'] = "ff000d";
    $colors['color_chartbar_piek2'] = "a80000";
    $colors['color_chart_average_line'] = "15ff24";
    $colors['color_chart_reference_line'] = "ff000d";
    $colors['color_chart_cum_line'] = "0d0bb0";
    $colors['color_chart_max_line'] = "454545";
    $colors['color_chart_temp_line'] = "0E669E";
    $colors['color_chart_max_bar'] = "9e9e9e";
    $colors['color_chart_expected_bar'] = "d6d6d6";
    $colors['color_chart_text_title'] = "000000";
    $colors['color_chart_text_subtitle'] = "000000";
    $colors['color_chart_labels_xaxis1'] = "000000";
    $colors['color_chart_title_yaxis1'] = "000000";
    $colors['color_chart_title_yaxis2'] = "000000";
    $colors['color_chart_title_yaxis3'] = "000000";
    $colors['color_chart_labels_yaxis1'] = "000000";
    $colors['color_chart_labels_yaxis2'] = "000000";
    $colors['color_chart_labels_yaxis3'] = "000000";
    $colors['color_chart_gridline_yaxis1'] = "000000";
    $colors['color_chart_gridline_yaxis2'] = "000000";
    $colors['color_chart_gridline_yaxis3'] = "000000";
    $colors['color_yearchart0'] = "FF00D0";
    $colors['color_yearchart1'] = "f4ff1c";
    $colors['color_yearchart2'] = "FF00D0";
    $colors['color_yearchart3'] = "f4ff1c";
    $colors['color_yearchart4'] = "FF00D0";
    $colors['color_text_link1'] = "000000";
    $colors['color_text_link2'] = "c00000";
    $colors['color_palettes'] = array(
        array('#ff7043', '#f4511e', '#d84315'),
        array('#81c784', '#43a047', '#2e7d32'),
        array('#e57373', '#f44336', '#c62828'),
        array('#4fc3f7', '#039be5', '#0277bd'),
        array('#4dd0e1', '#00acc1', '#006064'),
        array('#b0bec5', '#607d8b', '#37474f'));
} else if ($_SESSION['theme'] == "theme4") {
    $colors['color_background'] = "ffffff";
    $colors['color_footerbackground'] = "d6d6d6";
    $colors['color_menubackground'] = "d6d6d6";
    $colors['color_menufont'] = "000000";
    $colors['color_windowfont'] = "78cbff";
    $colors['color_windowcolor'] = "0003a6";
    $colors['color_image_windowtitle'] = HTML_PATH."/inc/styles/images/bg_darkblue.png";
    $colors['color_chartbackground'] = "78cbff";
    $colors['color_chartbar1'] = "00a3f6";
    $colors['color_chartbar2'] = "0003a6";
    $colors['color_inverter0_chartbar_min'] = "87CEEB";
    $colors['color_inverter0_chartbar_max'] = "00008B";
    $colors['color_inverter1_chartbar_min'] = "FFFF00";
    $colors['color_inverter1_chartbar_max'] = "FFD700";
    $colors['color_chartbar_piek1'] = "f3ff15";
    $colors['color_chartbar_piek2'] = "a80000";
    $colors['color_chart_average_line'] = "0003a6";
    $colors['color_chart_reference_line'] = "ff000d";
    $colors['color_chart_cum_line'] = "f3ff15";
    $colors['color_chart_max_line'] = "15ff24";
    $colors['color_chart_temp_line'] = "000000";
    $colors['color_chart_max_bar'] = "9e9e9e";
    $colors['color_chart_expected_bar'] = "d6d6d6";
    $colors['color_chart_text_title'] = "0003a6";
    $colors['color_chart_text_subtitle'] = "0003a6";
    $colors['color_chart_labels_xaxis1'] = "0003a6";
    $colors['color_chart_title_yaxis1'] = "0003a6";
    $colors['color_chart_title_yaxis2'] = "0003a6";
    $colors['color_chart_title_yaxis3'] = "0003a6";
    $colors['color_chart_labels_yaxis1'] = "0003a6";
    $colors['color_chart_labels_yaxis2'] = "0003a6";
    $colors['color_chart_labels_yaxis3'] = "0003a6";
    $colors['color_chart_gridline_yaxis1'] = "0003a6";
    $colors['color_chart_gridline_yaxis2'] = "0003a6";
    $colors['color_chart_gridline_yaxis3'] = "0003a6";
    $colors['color_yearchart0'] = "0003a6";
    $colors['color_yearchart1'] = "f3ff15";
    $colors['color_yearchart2'] = "a80000";
    $colors['color_yearchart3'] = "15ff24";
    $colors['color_yearchart4'] = "545454";
    $colors['color_text_link1'] = "000000";
    $colors['color_text_link2'] = "c00000";
    $colors['color_palettes'] = array(
        array('#9ccc65', '#8bc34a', '#7cb342'),
        array('#aed6f1', '#85c1e9', '#5dade2'),
        array('#f9e79f', '#f7dc6f', '#f4d03f'),
        array('#a3e4d7', '#76d7c4', '#48c9b0'),
        array('#d7bde2', '#c39bd3', '#af7ac5'),
        array('#ef5350', '#e53935', '#c62828'));
}

$_SESSION['colors'] = $colors;

?>
