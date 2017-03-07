<?php

include_once "../Parameters.php";
include_once "../inc/sessionstart.php";

if (!isset($_SESSION['passok']))
    header('location:par_welcome.php');

include_once "../inc/connect.php";

// check if tables exists and save next_mail_threshold value
$sqlpar = "SELECT Waarde FROM " . $table_prefix . "_parameters where variable = \"next_mail_threshold\" limit 1";
$result = mysqli_query($con, $sqlpar) or die(header('location:opstart_installatie.php?fout=table'));

$_POST['next_mail_threshold'] = 1;
if (mysqli_num_rows($result) != 0) {
    $row = mysqli_fetch_array($result);
    $_POST['next_mail_threshold'] = $row['Waarde'];
}


$statoe = 1;

include_once "par_header.php";

?>

<div id="menus">
    <?php include "par_menu.php"; ?>
</div>
<div id="container">
    <div id="bodytextparm">
        <div class="inside">
            <h2 class="notopgap" align="center"><u>Parameters</u></h2>
            <?php

            $vlag = 0;

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $txt["watverkeerd"] = "";
                foreach ($_POST as $key => $waarden) {
                    if (strlen($waarden) == 0 || ($_POST[$key] == $txt["verkeerd"])) {
                        if ($key == 'plantname' || $key == 'google_tracking') {
                            // plantname, google_tracking ... could be empty
                        } else {
                            $_POST[$key] = $txt["verkeerd"];
                            $txt["watverkeerd"] .= "<br/>" . $key;
                            $vlag = 1;
                        }
                    }
                }
                if ($vlag == 1){
                    echo "<font size='0'color='#c00000'>" . $txt["verkeerd"] . $txt["watverkeerd"] . "</font><br/>";
                }
            }
            if (($_SERVER['REQUEST_METHOD'] == 'POST') && $vlag == 0) {
                $sqldel = "DELETE FROM " . $table_prefix . "_parameters";
                $resultdel = mysqli_query($con, $sqldel);
                if (!$resultdel)
                    echo "<font size='0'color='#ff0000'>" . $txt["verkeerd"] . $txt["tabelverkeerd"] . $table_prefix . $txt["tabelverkeerd1"] . "</font><br/>";
                else {
                    $sqlsave = "INSERT INTO " . $table_prefix . "_parameters(Variable,Waarde)values";
                    foreach ($_POST as $key => $waarden) {
                        $sqlsave .= "('" . $key . "','" . $waarden . "'),";
                    }
                    $sqlsave = substr($sqlsave, 0, -1);
                    mysqli_query($con, $sqlsave) or die("Query failed. sql_save: " . mysqli_error($con));
                    echo "<font size='0'color='#00AA00'>" . $txt["opgeslagen"] . "</font><br/>";
                }
            }


            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                if (isset($table_prefix)) {
                    $sqlrefer = "SELECT * FROM " . $table_prefix . "_parameters";

                    $resultrefer = mysqli_query($con, $sqlrefer) or die("Query failed. ERROR: " . mysqli_error($con));
                    if (mysqli_num_rows($resultrefer) != 0) {
                        while ($row = mysqli_fetch_array($resultrefer)) {
                            $_POST[$row['Variable']] = $row['Waarde'];
                        }
                    }
                }
            }
            $_SESSION['lastupdate'] = 0;
            ?>

            <FORM name="formulier" METHOD="post" ACTION="">

                <hr>
                <?php echo $txt["kiestijd"]; ?><br/>
                <select NAME="isorteren">
                    <option SELECTED><?php if (!empty($_POST['isorteren'])) echo $_POST['isorteren']; else echo "5" ?>
                    <option>1
                    <option>2
                    <option>3
                    <option>4
                    <option>5
                    <option>6
                    <option>10
                    <option>12
                    <option>15
                    <option>20
                    <option>30
                    <option>60
                </select>
                <br/>
                <hr>
                <?php echo $txt["prefix"]; ?><br/>
                <?php echo $txt["prefix_name"] . ": <strong>" . $table_prefix . "</strong>"; ?>
                <br/>
                <hr>
                <label><?php echo $txt["startdatum"]; ?> :</label>
                <select name="dag">
                    <option SELECTED><?php if (!empty($_POST['dag'])) echo $_POST['dag']; ?>
                        <?php
                        for ($i = 1; $i <= 31; $i++) {
                            echo '<option value="' . $i . '">' . $i . '</option>';
                        }
                        ?>
                </select>
                <select name="maand">
                    <option SELECTED><?php if (!empty($_POST['maand'])) echo $_POST['maand']; ?>
                        <?php
                        for ($i = 1; $i <= 12; $i++) {
                            echo '<option value="' . $i . '">' . $i . '</option>';
                        }
                        ?>
                </select>
                <select name="jaar">
                    <option SELECTED><?php if (!empty($_POST['jaar'])) echo $_POST['jaar']; ?>
                        <?php
                        for ($i = date('Y'); $i >= 2006; $i--) {
                            echo '<option value="' . $i . '">' . $i . '</option>';
                        }
                        ?>
                </select>
                <br/>
                <hr>
                <?php echo $txt["available_languages"]; ?>?<br>
                <input type='checkbox' name='lang_de'
                       size='5' <?php if (isset($_POST['lang_de'])) echo "checked"; ?> >DE <img
                        src="../inc/image/blank.gif" class="flag flag-de" alt=""/>
                <br/>
                <input type='checkbox' name='lang_nl'
                       size='5' <?php if (isset($_POST['lang_nl'])) echo "checked"; ?> >NL <img
                        src="../inc/image/blank.gif" class="flag flag-nl" alt=""/>
                <br/>
                <input type='checkbox' name='lang_fr'
                       size='5' <?php if (isset($_POST['lang_fr'])) echo "checked"; ?> >FR <img
                        src="../inc/image/blank.gif" class="flag flag-fr" alt=""/>
                <br/>
                <input type='checkbox' name='lang_en'
                       size='5' <?php if (isset($_POST['lang_en'])) echo "checked"; ?> >EN <img
                        src="../inc/image/blank.gif" class="flag flag-gb" alt=""/>
                <br/>
                <input type='checkbox' name='lang_at'
                       size='5' <?php if (isset($_POST['lang_at'])) echo "checked"; ?> >AT <img
                        src="../inc/image/blank.gif" class="flag flag-at" alt=""/>
                <br/>

                <hr>

                <?php echo $txt["keuzenaam"]; ?> (,) bv: SLAPER,SIRA,FJORD<br/>
                <input type='text' name='sNaamSaveDatabasest'
                       value='<?php if (!empty($_POST['sNaamSaveDatabasest'])) echo $_POST['sNaamSaveDatabasest']; ?>'
                       size='63'>
                <br/>
                <hr>
                <?php echo $txt["watpiek"]; ?> (,) bv: 4500,10210,6880<br/>
                <input type='text' name='ieffectief_kwpiekst'
                       value='<?php if (!empty($_POST['ieffectief_kwpiekst'])) echo $_POST['ieffectief_kwpiekst']; else echo "5000" ?>'
                       size='63'>
                <br/>
                <hr>
                <?php echo $txt["coefficient"]; ?>.<br/>
                <input type='text' name='coefficient'
                       value='<?php if (!empty($_POST['coefficient'])) echo $_POST['coefficient']; else echo "1" ?>'
                       size='20'>
                <br/>
                <hr>
                <?php
                if (empty($_POST['sInvullen_gegevens'])) {
                    $_POST['sInvullen_gegevens'] = "Invullen_gegevens_sunny_explorer";
                }
                $selectedInverter = $_POST['sInvullen_gegevens'];
                echo $txt["inlezenbestand"];
                ?>:

                <select NAME="sInvullen_gegevens">
                    <option <?php if ($selectedInverter == "Invullen_gegevens_suo") echo "selected "; ?> >
                        Invullen_gegevens_suo
                    </option>
                    <option <?php if ($selectedInverter == "Invullen_gegevens_suo_custom2") echo "selected "; ?>>
                        Invullen_gegevens_suo_custom2
                    </option>
                    <option <?php if ($selectedInverter == "Invullen_gegevens_xls") echo "selected "; ?>>
                        Invullen_gegevens_xls
                    </option>
                    <option <?php if ($selectedInverter == "Invullen_gegevens_solarlog") echo "selected "; ?>>
                        Invullen_gegevens_solarlog
                    </option>
                    <option <?php if ($selectedInverter == "Invullen_gegevens_solarlogjs") echo "selected "; ?>>
                        Invullen_gegevens_solarlogjs
                    </option>
                    <option <?php if ($selectedInverter == "Invullen_gegevens_solarlogXomvorm") echo "selected "; ?>>
                        Invullen_gegevens_solarlogXomvorm
                    </option>
                    <option <?php if ($selectedInverter == "Invullen_gegevens_solarlogXomvormjs") echo "selected "; ?>>
                        Invullen_gegevens_solarlogXomvormjs
                    </option>
                    <option <?php if ($selectedInverter == "Invullen_gegevens_sunny_explorer") echo "selected "; ?>>
                        Invullen_gegevens_sunny_explorer
                    </option>
                    <option <?php if ($selectedInverter == "Import_von_Hyperion") echo "selected "; ?>>
                        Import_von_Hyperion
                    </option>
                    <option <?php if ($selectedInverter == "Import_von_Hyperion_christian") echo "selected "; ?>>
                        Import_von_Hyperion_christian
                    </option>
                    <option <?php if ($selectedInverter == "sunny_explorer_manuel") echo "selected "; ?>>
                        sunny_explorer_manuel
                    </option>
                    <option <?php if ($selectedInverter == "sunny_explorer_seehase") echo "selected "; ?>>
                        sunny_explorer_seehase
                    </option>
                    <option <?php if ($selectedInverter == "Invullen_gegevens_sunnybeam_bt") echo "selected "; ?>>
                        Invullen_gegevens_sunnybeam_bt
                    </option>
                    <option <?php if ($selectedInverter == "Invullen_gegevens_sunny_webbox_csv") echo "selected "; ?>>
                        Invullen_gegevens_sunny_webbox_csv
                    </option>
                    <option <?php if ($selectedInverter == "Import_sunny_webbox_csv_thorsten") echo "selected "; ?>>
                        Import_sunny_webbox_csv_thorsten
                    </option>


                </select>
                <?php
                $pageURL = 'http://';
                $pageURL .= $_SERVER["SERVER_NAME"] . '/';
                ?>
                <div id="toverdiv"><?php echo $txt["plantname"]; ?>
                    <input type="text" name='plantname'
                           value='<?php if (empty($_POST['plantname'])) echo ""; else echo $_POST['plantname']; ?>'
                           size='20'>
                </div>
                <hr>
                Link to your website: <a href="http://solar.seehausen.org"
                                         TARGET="_blank">http://solar.seehausen.org/</a><br/>

                <input type='text' name='sURL_link'
                       value='<?php if (empty($_POST['sURL_link'])) echo $pageURL; else echo $_POST['sURL_link']; ?>'
                       size='63'>
                <br/>
                <hr>
                <?php echo $txt["gegevensinstallatie"]; ?>.<br/>
                <table>
                    <tbody>

                    <tr>
                        <td><?php echo $txt["naamwebsite"]; ?>:</td>
                        <td><input type='text' name='sNaamVoorOpWebsite'
                                   value='<?php if (!empty($_POST['sNaamVoorOpWebsite'])) echo $_POST['sNaamVoorOpWebsite']; else echo "your name" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["firmanaam"]; ?>:</td>
                        <td><input type='text' name='sNaam_Installateur'
                                   value='<?php if (!empty($_POST['sNaam_Installateur'])) echo $_POST['sNaam_Installateur']; else echo "your installateur" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["websitefirma"]; ?>:</td>
                        <td><input type='text' name='sWebsite_Installateur'
                                   value='<?php if (!empty($_POST['sWebsite_Installateur'])) echo $_POST['sWebsite_Installateur']; else echo "your website admin" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["aantalsoortpaneel"]; ?>:</td>
                        <td><input type='text' name='sSoort_pannel_aantal'
                                   value='<?php if (!empty($_POST['sSoort_pannel_aantal'])) echo $_POST['sSoort_pannel_aantal']; else echo "your panels" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["Omvormer"]; ?>:</td>
                        <td><input type='text' name='sOmvormer'
                                   value='<?php if (!empty($_POST['sOmvormer'])) echo $_POST['sOmvormer']; else echo " your converter" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["orientatie"]; ?>:</td>
                        <td><input type='text' name='sOrientatie'
                                   value='<?php if (!empty($_POST['sOrientatie'])) echo $_POST['sOrientatie']; else echo "your orientation" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["datacaptatie"]; ?>:</td>
                        <td><input type='text' name='sData_Captatie'
                                   value='<?php if (!empty($_POST['sData_Captatie'])) echo $_POST['sData_Captatie']; else echo "your data capture" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["woonplaats"]; ?>:</td>
                        <td><input type='text' name='sPlaats'
                                   value='<?php if (!empty($_POST['sPlaats'])) echo $_POST['sPlaats']; else echo "your location" ?>'
                                   size='63'>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["image1"]; ?>:</td>
                        <td><input type='text' name='image1'
                                   value='<?php if (!empty($_POST['image1'])) echo $_POST['image1']; else {
                                       $_POST['image1'] = "inc/image/image1.jpg";
                                       echo $_POST['image1'];
                                   } ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["image2"]; ?>:</td>
                        <td><input type='text' name='image2'
                                   value='<?php if (!empty($_POST['image2'])) echo $_POST['image2']; else {
                                       $_POST['image2'] = "inc/image/image2.jpg";
                                       echo $_POST['image2'];
                                   } ?>'
                                   size='63'></td>
                    </tr>

                    </tbody>
                </table>
                <hr>
                <?php echo $txt["dagboek"]; ?>?
                <input type="radio" name="idagboek" value="1" <?php if (isset($_POST['idagboek'])) {
                    if ($_POST['idagboek'] == 1) echo "checked";
                } ?>> <?php echo $txt["YES"] ?>
                <input type="radio" name="idagboek" value="0" <?php if (isset($_POST['idagboek'])) {
                    if ($_POST['idagboek'] == 0) echo "checked";
                } else echo "checked"; ?>><?php echo $txt["NO"] ?>
                <br/>

                <hr>
                <?php echo $txt["flyout_menu"]; ?>?
                <br>
                <input type="radio" name="flyout" value="0" <?php if (isset($_POST['flyout'])) {
                    if ($_POST['flyout'] == 0) echo "checked";
                } ?>> <?php echo $txt["flyout0"] ?>
                <input type="radio" name="flyout" value="1" <?php if (isset($_POST['flyout'])) {
                    if ($_POST['flyout'] == 1) echo "checked";
                } else echo "checked"; ?>><?php echo $txt["flyout1"] ?>
                <input type="radio" name="flyout" value="2" <?php if (isset($_POST['flyout'])) {
                    if ($_POST['flyout'] == 2) echo "checked";
                } else echo "checked"; ?>><?php echo $txt["flyout2"] ?>
                <br>
                <?php echo $txt["show_footer"]; ?>?
                <input type='checkbox' name='show_footer'
                       size='5' <?php if (isset($_POST['show_footer'])) echo "checked"; ?> >
                <br/>

                <hr>
                <?php echo $txt["no_units"]; ?>?
                <input type='checkbox' name='no_units'
                       size='5' <?php if (isset($_POST['no_units'])) echo "checked"; ?> >
                <br/>

                <hr>
                <?php echo $txt["external_sensors"]; ?>?
                <input type='checkbox' name='external_sensors'
                       size='5' <?php if (isset($_POST['external_sensors'])) echo "checked"; ?> >
                <br/>
                <?php echo $txt["external_sensors_for_daychart"]; ?>?
                <input type='checkbox' name='external_sensors_for_daychart'
                       size='5' <?php if (isset($_POST['external_sensors_for_daychart'])) echo "checked"; ?> >
                <br/>

                <hr>
                <?php echo $txt["choose_charts"]; ?><br/>
                <table>
                    <thead>
                    <tr>
                        <td style="width: 300px"><?php echo $txt["name"]; ?></td>
                        <td><?php echo $txt["show"]; ?></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?php echo $txt["chart_gauge"]; ?></td>
                        <td><input type='checkbox' name='chart_gauge'
                                   size='5' <?php if (isset($_POST['chart_gauge'])) echo "checked"; ?> ></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["chart_solar_temp"]; ?></td>
                        <td><input type='checkbox' name='chart_solar_temp'
                                   size='5' <?php if (isset($_POST['chart_solar_temp'])) echo "checked"; ?> ></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["chart_monthoverview"]; ?></td>
                        <td><input type='checkbox' name='chart_monthoverview'
                                   size='5' <?php if (isset($_POST['chart_monthoverview'])) echo "checked"; ?> ></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["chart_indoor"]; ?></td>
                        <td><input type='checkbox' name='chart_indoor'
                                   size='5' <?php if (isset($_POST['chart_indoor'])) echo "checked"; ?> ></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["chart_yearoverview"]; ?></td>
                        <td><input type='checkbox' name='chart_yearoverview'
                                   size='5' <?php if (isset($_POST['chart_yearoverview'])) echo "checked"; ?> ></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["chart_allyearoverview"]; ?></td>
                        <td><input type='checkbox' name='chart_allyearoverview'
                                   size='5' <?php if (isset($_POST['chart_allyearoverview'])) echo "checked"; ?> ></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["chart_lastyearoverview"]; ?></td>
                        <td><input type='checkbox' name='chart_lastyearoverview'
                                   size='5' <?php if (isset($_POST['chart_lastyearoverview'])) echo "checked"; ?> ></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["chart_weekoverview"]; ?></td>
                        <td><input type='checkbox' name='chart_weekoverview'
                                   size='5' <?php if (isset($_POST['chart_weekoverview'])) echo "checked"; ?> ></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["chart_31days"]; ?></td>
                        <td><input type='checkbox' name='chart_31days'
                                   size='5' <?php if (isset($_POST['chart_31days'])) echo "checked"; ?> ></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["chart_all_temp"]; ?></td>
                        <td><input type='checkbox' name='chart_all_temp'
                                   size='5' <?php if (isset($_POST['chart_all_temp'])) echo "checked"; ?> ></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["chart_all_humidity"]; ?></td>
                        <td><input type='checkbox' name='chart_all_humidity'
                                   size='5' <?php if (isset($_POST['chart_all_humidity'])) echo "checked"; ?> ></td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["chart_weewx"]; ?></td>
                        <td><input type='checkbox' name='chart_weewx'
                                   size='5' <?php if (isset($_POST['chart_weewx'])) echo "checked"; ?> ></td>
                    </tr>

                    </tbody>
                </table>
                <hr>
                <?php echo $txt["zonphpse"]; ?>
                <input type="radio" name="izonphpse" value="1" <?php if (isset($_POST['izonphpse'])) {
                    if ($_POST['izonphpse'] == 1) echo "CHECKED";
                } ?>> <?php echo $txt["YES"] ?>
                <input type="radio" name="izonphpse" value="0" <?php if (isset($_POST['izonphpse'])) {
                    if ($_POST['izonphpse'] == 0) echo "CHECKED";
                } else echo "CHECKED"; ?>><?php echo $txt["NO"] ?>
                <br/>
                <hr>
                <?php echo $txt["dagnachttarief"]; ?>
                <input type="radio" name="iTonendagnacht" value="1"<?php if (isset($_POST['iTonendagnacht'])) {
                    if ($_POST['iTonendagnacht'] == 1) echo "CHECKED";
                } else echo "CHECKED"; ?>> <?php echo $txt["YES"] ?>
                <input type="radio" name="iTonendagnacht" value="0"<?php if (isset($_POST['iTonendagnacht'])) {
                    if ($_POST['iTonendagnacht'] == 0) echo "CHECKED";
                } ?>><?php echo $txt["NO"] ?>
                <br/>
                <hr>

                <?php echo $txt["uwmailadres"]; ?>:
                <input type='text' name='email'
                       value='<?php if (!empty($_POST['email'])) echo $_POST['email']; else echo "nobody@localhost" ?>'
                       size='63'><br/>
                <?php echo $txt["intervalmail"]; ?>:
                <input type='text' name='mailinterval'
                       value='<?php if (!empty($_POST['mailinterval'])) echo $_POST['mailinterval']; else echo 1000 ?>'>
                <br/> <hr>
                <?php echo $txt["google_tracking"]; ?>:
                <input type='text' name='google_tracking' size='40'
                       value='<?php if (!empty($_POST['google_tracking'])) echo $_POST['google_tracking'] ?>'>
                <br/>
                <hr>


                <table>
                    <tbody>
                    <tr>
                        <td><?php echo $txt["color_background"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_background" id="color_background" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_background']))
                                echo $_POST['color_background'];
                            else {
                                $_POST['color_background'] = "#888888";
                                echo $_POST['color_background'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_footerbackground"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_footerbackground" id="color_footerbackground"
                                   size="6" maxlength="6"
                                   value="
				<?php
                                   if (!empty($_POST['color_footerbackground']))
                                       echo $_POST['color_footerbackground'];
                                   else {
                                       $_POST['color_footerbackground'] = "#C00000";
                                       echo $_POST['color_footerbackground'];
                                   }
                                   ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_menubackground"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_menubackground" id="color_menubackground"
                                   size="6" maxlength="6" value="
				<?php
                            if (!empty($_POST['color_menubackground']))
                                echo $_POST['color_menubackground'];
                            else {
                                $_POST['color_menubackground'] = "#ffffff";
                                echo $_POST['color_menubackground'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_menufont"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_menufont" id="color_menufont" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_menufont']))
                                echo $_POST['color_menufont'];
                            else {
                                $_POST['color_menufont'] = "#000000";
                                echo $_POST['color_menufont'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_windowfont"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_windowfont" id="color_windowfont" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_windowfont']))
                                echo $_POST['color_windowfont'];
                            else {
                                $_POST['color_windowfont'] = "#ffffff";
                                echo $_POST['color_windowfont'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_windowcolor"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_windowcolor" id="color_windowcolor" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_windowcolor']))
                                echo $_POST['color_windowcolor'];
                            else {
                                $_POST['color_windowcolor'] = "#000000";
                                echo $_POST['color_windowcolor'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_image_windowtitle"]; ?>:</td>
                        <td>
                            <input type='text' name='color_image_windowtitle' id="color_image_windowtitle" size='50'
                                   value="<?php if (!empty($_POST['color_image_windowtitle']))
                                       echo $_POST['color_image_windowtitle'];
                                   else {
                                       $_POST['color_image_windowtitle'] = "inc/image/bg_black.png";
                                       echo $_POST['color_image_windowtitle'];
                                   }
                                   ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["achtergrondflash"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chartbackground" id="color_chartbackground"
                                   size="6" maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chartbackground']))
                                echo $_POST['color_chartbackground'];
                            else {
                                $_POST['color_chartbackground'] = "#888888";
                                echo $_POST['color_chartbackground'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["klbalkgr"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chartbar1" id="color_chartbar1" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chartbar1']))
                                echo $_POST['color_chartbar1'];
                            else {
                                $_POST['color_chartbar1'] = "#003399";
                                echo $_POST['color_chartbar1'];
                            }
                            ?>">
                            <input class="jscolor" type="text" name="color_chartbar2" id="color_chartbar2" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chartbar2']))
                                echo $_POST['color_chartbar2'];
                            else {
                                $_POST['color_chartbar2'] = "#3366AA";
                                echo $_POST['color_chartbar2'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["klbalkgrpiek"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chartbar_piek1" id="color_chartbar_piek1"
                                   size="6" maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chartbar_piek1']))
                                echo $_POST['color_chartbar_piek1'];
                            else {
                                $_POST['color_chartbar_piek1'] = "#730073";
                                echo $_POST['color_chartbar_piek1'];
                            }
                            ?>">
                            <input class="jscolor" type="text" name="color_chartbar_piek2" id="color_chartbar_piek2"
                                   size="6" maxlength="6"
                                   value="
				<?php
                                   if (!empty($_POST['color_chartbar_piek2']))
                                       echo $_POST['color_chartbar_piek2'];
                                   else {
                                       $_POST['color_chartbar_piek2'] = "#FF00FF";
                                       echo $_POST['color_chartbar_piek2'];
                                   }
                                   ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_chart_avarage_line"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_avarage_line"
                                   id="color_chart_avarage_line" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_avarage_line']))
                                echo $_POST['color_chart_avarage_line'];
                            else {
                                $_POST['color_chart_avarage_line'] = "#0AF02F";
                                echo $_POST['color_chart_avarage_line'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_chart_reference_line"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_reference_line"
                                   id="color_chart_reference_line"
                                   size="6" maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_reference_line']))
                                echo $_POST['color_chart_reference_line'];
                            else {
                                $_POST['color_chart_reference_line'] = "#FF0055";
                                echo $_POST['color_chart_reference_line'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_chart_cum_line"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_cum_line" id="color_chart_cum_line"
                                   size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_cum_line']))
                                echo $_POST['color_chart_cum_line'];
                            else {
                                $_POST['color_chart_cum_line'] = "#777777";
                                echo $_POST['color_chart_cum_line'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_chart_max_line"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_max_line" id="color_chart_max_line"
                                   size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_max_line']))
                                echo $_POST['color_chart_max_line'];
                            else {
                                $_POST['color_chart_max_line'] = "#777777";
                                echo $_POST['color_chart_max_line'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_chart_temp_line"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_temp_line" id="color_chart_temp_line"
                                   size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_temp_line']))
                                echo $_POST['color_chart_temp_line'];
                            else {
                                $_POST['color_chart_temp_line'] = "#0E669E";
                                echo $_POST['color_chart_temp_line'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_chart_max_bar"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_max_bar" id="color_chart_max_bar"
                                   size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_max_bar']))
                                echo $_POST['color_chart_max_bar'];
                            else {
                                $_POST['color_chart_max_bar'] = "#777777";
                                echo $_POST['color_chart_max_bar'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_chart_expected_bar"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_expected_bar"
                                   id="color_chart_expected_bar" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_expected_bar']))
                                echo $_POST['color_chart_expected_bar'];
                            else {
                                $_POST['color_chart_expected_bar'] = "#aaaaaa";
                                echo $_POST['color_chart_expected_bar'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_chart_text_title"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_text_title"
                                   id="color_chart_text_title" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_text_title']))
                                echo $_POST['color_chart_text_title'];
                            else {
                                $_POST['color_chart_text_title'] = "#151515";
                                echo $_POST['color_chart_text_title'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_chart_text_subtitle"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_text_subtitle"
                                   id="color_chart_text_subtitle" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_text_subtitle']))
                                echo $_POST['color_chart_text_subtitle'];
                            else {
                                $_POST['color_chart_text_subtitle'] = "#151515";
                                echo $_POST['color_chart_text_subtitle'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_chart_labels_xaxis1"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_labels_xaxis1"
                                   id="color_chart_labels_xaxis1" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_labels_xaxis1']))
                                echo $_POST['color_chart_labels_xaxis1'];
                            else {
                                $_POST['color_chart_labels_xaxis1'] = "#1D1999";
                                echo $_POST['color_chart_labels_xaxis1'];
                            }
                            ?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $txt["color_chart_labels_yaxis1"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_labels_yaxis1"
                                   id="color_chart_labels_yaxis1" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_labels_yaxis1']))
                                echo $_POST['color_chart_labels_yaxis1'];
                            else {
                                $_POST['color_chart_labels_yaxis1'] = "#1D1999";
                                echo $_POST['color_chart_labels_yaxis1'];
                            }
                            ?>">

                            <input class="jscolor" type="text" name="color_chart_labels_yaxis2"
                                   id="color_chart_labels_yaxis2" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_labels_yaxis2']))
                                echo $_POST['color_chart_labels_yaxis2'];
                            else {
                                $_POST['color_chart_labels_yaxis2'] = "#1D1999";
                                echo $_POST['color_chart_labels_yaxis2'];
                            }
                            ?>">

                            <input class="jscolor" type="text" name="color_chart_labels_yaxis3"
                                   id="color_chart_labels_yaxis3" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_labels_yaxis3']))
                                echo $_POST['color_chart_labels_yaxis3'];
                            else {
                                $_POST['color_chart_labels_yaxis3'] = "#1D1999";
                                echo $_POST['color_chart_labels_yaxis3'];
                            }
                            ?>">
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $txt["color_chart_title_yaxis1"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_title_yaxis1"
                                   id="color_chart_title_yaxis1" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_title_yaxis1']))
                                echo $_POST['color_chart_title_yaxis1'];
                            else {
                                $_POST['color_chart_title_yaxis1'] = "#151515";
                                echo $_POST['color_chart_title_yaxis1'];
                            }
                            ?>">

                            <input class="jscolor" type="text" name="color_chart_title_yaxis2"
                                   id="color_chart_title_yaxis2" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_title_yaxis2']))
                                echo $_POST['color_chart_title_yaxis2'];
                            else {
                                $_POST['color_chart_title_yaxis2'] = "#4FA2D6";
                                echo $_POST['color_chart_title_yaxis2'];
                            }
                            ?>">

                            <input class="jscolor" type="text" name="color_chart_title_yaxis3"
                                   id="color_chart_title_yaxis3" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_title_yaxis3']))
                                echo $_POST['color_chart_title_yaxis3'];
                            else {
                                $_POST['color_chart_title_yaxis3'] = "#4FA2D6";
                                echo $_POST['color_chart_title_yaxis3'];
                            }
                            ?>">
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $txt["color_chart_gridline_yaxis1"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_chart_gridline_yaxis1"
                                   id="color_chart_gridline_yaxis1" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_gridline_yaxis1']))
                                echo $_POST['color_chart_gridline_yaxis1'];
                            else {
                                $_POST['color_chart_gridline_yaxis1'] = "#151515";
                                echo $_POST['color_chart_gridline_yaxis1'];
                            }
                            ?>">

                            <input class="jscolor" type="text" name="color_chart_gridline_yaxis2"
                                   id="color_chart_gridline_yaxis2" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_gridline_yaxis2']))
                                echo $_POST['color_chart_gridline_yaxis2'];
                            else {
                                $_POST['color_chart_gridline_yaxis2'] = "#4FA2D6";
                                echo $_POST['color_chart_gridline_yaxis2'];
                            }
                            ?>">

                            <input class="jscolor" type="text" name="color_chart_gridline_yaxis3"
                                   id="color_chart_gridline_yaxis3" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_chart_gridline_yaxis3']))
                                echo $_POST['color_chart_gridline_yaxis3'];
                            else {
                                $_POST['color_chart_gridline_yaxis3'] = "#4FA2D6";
                                echo $_POST['color_chart_gridline_yaxis3'];
                            }
                            ?>">
                        </td>
                    </tr>


                    <tr>
                        <td><?php echo $txt["klbalkgrvijfjaar"]; ?>:</td>
                        <td>

                            <?php
                            $color_yearchartt = array("#1F3A93", "#4B77BE", "#2574A9", "#89C4F4", "#5C97BF");
                            for ($i = 0; $i < 5; $i++) {
                                echo '<input class="jscolor" type="text" name="color_yearchart' . $i . '" id ="color_yearchart' . $i . '" size="6" maxlength="6"  value="';
                                if (!empty($_POST['color_yearchart' . $i]))
                                    echo $_POST['color_yearchart' . $i];
                                else {
                                    $_POST['color_yearchart' . $i] = $color_yearchartt[$i];
                                    echo $_POST['color_yearchart' . $i];
                                }
                                echo '">';
                            }
                            ?>
                        </td>
                    </tr>

                    <tr>
                        <td><?php echo $txt["color_text_link"]; ?>:</td>
                        <td>
                            <input class="jscolor" type="text" name="color_text_link1"
                                   id="color_text_link1" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_text_link1']))
                                echo $_POST['color_text_link1'];
                            else {
                                $_POST['color_text_link1'] = "#ffffff";
                                echo $_POST['color_text_link1'];
                            }
                            ?>">
                            <input class="jscolor" type="text" name="color_text_link2"
                                   id="color_text_link2" size="6"
                                   maxlength="6" value="
				<?php
                            if (!empty($_POST['color_text_link2']))
                                echo $_POST['color_text_link2'];
                            else {
                                $_POST['color_text_link2'] = "#c00000";
                                echo $_POST['color_text_link2'];
                            }
                            ?>">
                        </td>
                    </tr>

                    </tbody>
                </table>
                <?php echo $txt["copy"]; ?>
                <button type="button" onclick="resetcolors()"><?php echo $txt["default"]; ?></button>
                <button type="button" onclick="theme1()"><?php echo $txt["colortheme"]; ?>1</button>
                <button type="button" onclick="theme2()"><?php echo $txt["colortheme"]; ?>2</button>
                <!--                <button type="button" onclick="theme3()">-->
                <?php //echo $txt["colortheme"]; ?><!--3</button>-->
                <!--                <button type="button" onclick="theme4()">-->
                <?php //echo $txt["colortheme"]; ?><!--4</button>-->
                <br>

                <?php
                if (empty($_POST['colortheme'])) {
                    $_POST['colortheme'] = "user";
                }
                $theme = $_POST['colortheme'];
                echo $txt["choose"] . " " . $txt["colortheme"] . ":";
                ?>

                <select NAME="colortheme">
                    <option <?php if ($theme == "user") echo "selected "; ?> value="user">User</option>
                    <option <?php if ($theme == "default") echo "selected "; ?> value="default">ZonPHP default</option>
                    <option <?php if ($theme == "theme1") echo "selected "; ?> value="theme1">DarkGreyFire by Michael
                    </option>
                    <option <?php if ($theme == "theme2") echo "selected "; ?> value="theme2">Julia</option>
                    <option <?php if ($theme == "theme3") echo "selected "; ?> value="theme3">fire</option>
                    <option <?php if ($theme == "theme4") echo "selected "; ?> value="theme4">blue</option>
                </select>

                <hr>

                <INPUT name="savecontrole" TYPE="submit" VALUE="<?php echo $txt["save"]; ?>"
                       onClick="return confirmSubmit()">
            </FORM>

        </div>
    </div>
</div>

<script type="text/javascript">

    function resetcolors() {
        document.getElementById("color_background").jscolor.fromString("888888");
        document.getElementById("color_footerbackground").jscolor.fromString("C00000");
        document.getElementById("color_menubackground").jscolor.fromString("ffffff");
        document.getElementById("color_menufont").jscolor.fromString("000000");
        document.getElementById("color_windowfont").jscolor.fromString("ffffff");
        document.getElementById("color_windowcolor").jscolor.fromString("000000");
        document.getElementById("image_windowtitle").value = "inc/image/bg_black.png";
        document.getElementById("color_chartbackground").jscolor.fromString("888888");
        document.getElementById("color_chartbar1").jscolor.fromString("003399");
        document.getElementById("color_chartbar2").jscolor.fromString("3366AA");
        document.getElementById("color_chartbar_piek1").jscolor.fromString("F8F804");
        document.getElementById("color_chartbar_piek2").jscolor.fromString("FF00FF");
        document.getElementById("color_chart_avarage_line").jscolor.fromString("0AF02F");
        document.getElementById("color_chart_reference_line").jscolor.fromString("FF0055");
        document.getElementById("color_chart_cum_line").jscolor.fromString("212121");
        document.getElementById("color_chart_max_line").jscolor.fromString("777777");
        document.getElementById("color_chart_temp_line").jscolor.fromString("777777");
        document.getElementById("color_chart_max_bar").jscolor.fromString("777777");
        document.getElementById("color_chart_expected_bar").jscolor.fromString("C4C4C4");
        document.getElementById("color_chart_text_title").jscolor.fromString("1677B0");
        document.getElementById("color_chart_text_subtitle").jscolor.fromString("1C567D");
        document.getElementById("color_chart_labels_xaxis1").jscolor.fromString("4FA2D6");
        document.getElementById("color_chart_title_yaxis1").jscolor.fromString("1D2599");
        document.getElementById("color_chart_title_yaxis2").jscolor.fromString("0E6C7A");
        document.getElementById("color_chart_title_yaxis3").jscolor.fromString("565699");
        document.getElementById("color_chart_labels_yaxis1").jscolor.fromString("3D3C4F");
        document.getElementById("color_chart_labels_yaxis2").jscolor.fromString("C5D6D1");
        document.getElementById("color_chart_labels_yaxis3").jscolor.fromString("C3D6A9");
        document.getElementById("color_chart_gridline_yaxis1").jscolor.fromString("D1B3B0");
        document.getElementById("color_chart_gridline_yaxis2").jscolor.fromString("C8D1B2");
        document.getElementById("color_chart_gridline_yaxis3").jscolor.fromString("FFEBF2");
        document.getElementById("color_yearchart0").jscolor.fromString("1F3A93");
        document.getElementById("color_yearchart1").jscolor.fromString("4B77BE");
        document.getElementById("color_yearchart2").jscolor.fromString("2574A9");
        document.getElementById("color_yearchart3").jscolor.fromString("89C4F4");
        document.getElementById("color_yearchart4").jscolor.fromString("5C97BF");
        document.getElementById("color_text_link1").jscolor.fromString("ffffff");
        document.getElementById("color_text_link2").jscolor.fromString("c00000");
    }

    function theme1() {
        document.getElementById("color_background").jscolor.fromString("888888");
        document.getElementById("color_footerbackground").jscolor.fromString("C00000");
        document.getElementById("color_menubackground").jscolor.fromString("ffffff");
        document.getElementById("color_menufont").jscolor.fromString("000000");
        document.getElementById("color_windowfont").jscolor.fromString("ffffff");
        document.getElementById("color_windowcolor").jscolor.fromString("000000");
        document.getElementById("image_windowtitle").value = "inc/image/bg_black.png";
        document.getElementById("color_chartbackground").jscolor.fromString("888888");
        document.getElementById("color_chartbar1").jscolor.fromString("FF1C33");
        document.getElementById("color_chartbar2").jscolor.fromString("FFFF0D");
        document.getElementById("color_chartbar_piek1").jscolor.fromString("55FF33");
        document.getElementById("color_chartbar_piek2").jscolor.fromString("127029");
        document.getElementById("color_chart_avarage_line").jscolor.fromString("0AF02F");
        document.getElementById("color_chart_reference_line").jscolor.fromString("FF0055");
        document.getElementById("color_chart_max_line").jscolor.fromString("777777");
        document.getElementById("color_chart_max_bar").jscolor.fromString("777777");
        document.getElementById("color_chart_expected_bar").jscolor.fromString("C4C4C4");
        document.getElementById("color_chart_text_title").jscolor.fromString("1677B0");
        document.getElementById("color_chart_text_subtitle").jscolor.fromString("1C567D");
        document.getElementById("color_chart_labels_xaxis1").jscolor.fromString("4FA2D6");
        document.getElementById("color_chart_title_yaxis1").jscolor.fromString("1D2599");
        document.getElementById("color_chart_title_yaxis2").jscolor.fromString("0E6C7A");
        document.getElementById("color_chart_title_yaxis3").jscolor.fromString("565699");
        document.getElementById("color_chart_labels_yaxis1").jscolor.fromString("3D3C4F");
        document.getElementById("color_chart_labels_yaxis2").jscolor.fromString("C5D6D1");
        document.getElementById("color_chart_labels_yaxis3").jscolor.fromString("C3D6A9");
        document.getElementById("color_chart_gridline_yaxis1").jscolor.fromString("D1B3B0");
        document.getElementById("color_chart_gridline_yaxis2").jscolor.fromString("C8D1B2");
        document.getElementById("color_chart_gridline_yaxis3").jscolor.fromString("FFEBF2");
        document.getElementById("color_yearchart0").jscolor.fromString("FFFF0D");
        document.getElementById("color_yearchart1").jscolor.fromString("3334AD");
        document.getElementById("color_yearchart2").jscolor.fromString("FF1C33");
        document.getElementById("color_yearchart3").jscolor.fromString("55FF33");
        document.getElementById("color_yearchart4").jscolor.fromString("000000");
        document.getElementById("color_text_link1").jscolor.fromString("ffffff");
        document.getElementById("color_text_link2").jscolor.fromString("c00000");
    }

    function theme2() {
        document.getElementById("color_background").jscolor.fromString("381C04");
        document.getElementById("color_footerbackground").jscolor.fromString("E7A75B");
        document.getElementById("color_menubackground").jscolor.fromString("B8895F");
        document.getElementById("color_menufont").jscolor.fromString("000000");
        document.getElementById("color_windowfont").jscolor.fromString("ffffff");
        document.getElementById("color_windowcolor").jscolor.fromString("FFBF87");
        document.getElementById("image_windowtitle").value = "inc/image/bg_brown.png";
        document.getElementById("color_chartbackground").jscolor.fromString("FFDCBC");
        document.getElementById("color_chartbar1").jscolor.fromString("FF1C33");
        document.getElementById("color_chartbar2").jscolor.fromString("700000");
        document.getElementById("color_chartbar_piek1").jscolor.fromString("FFE100");
        document.getElementById("color_chartbar_piek2").jscolor.fromString("FF7E38");
        document.getElementById("color_chart_avarage_line").jscolor.fromString("B88686");
        document.getElementById("color_chart_reference_line").jscolor.fromString("4B3636");
        document.getElementById("color_chart_max_line").jscolor.fromString("805E5E");
        document.getElementById("color_chart_max_bar").jscolor.fromString("B88686");
        document.getElementById("color_chart_expected_bar").jscolor.fromString("C89191");
        document.getElementById("color_chart_text_title").jscolor.fromString("1677B0");
        document.getElementById("color_chart_text_subtitle").jscolor.fromString("1C567D");
        document.getElementById("color_chart_labels_xaxis1").jscolor.fromString("4FA2D6");
        document.getElementById("color_chart_title_yaxis1").jscolor.fromString("1D2599");
        document.getElementById("color_chart_title_yaxis2").jscolor.fromString("0E6C7A");
        document.getElementById("color_chart_title_yaxis3").jscolor.fromString("565699");
        document.getElementById("color_chart_labels_yaxis1").jscolor.fromString("3D3C4F");
        document.getElementById("color_chart_labels_yaxis2").jscolor.fromString("C5D6D1");
        document.getElementById("color_chart_labels_yaxis3").jscolor.fromString("C3D6A9");
        document.getElementById("color_chart_gridline_yaxis1").jscolor.fromString("D1B3B0");
        document.getElementById("color_chart_gridline_yaxis2").jscolor.fromString("C8D1B2");
        document.getElementById("color_chart_gridline_yaxis3").jscolor.fromString("FFEBF2");
        document.getElementById("color_yearchart0").jscolor.fromString("FF00D4");
        document.getElementById("color_yearchart1").jscolor.fromString("000000");
        document.getElementById("color_yearchart2").jscolor.fromString("620993");
        document.getElementById("color_yearchart3").jscolor.fromString("480458");
        document.getElementById("color_yearchart4").jscolor.fromString("9A01BE");
        document.getElementById("color_text_link1").jscolor.fromString("ffffff");
        document.getElementById("color_text_link2").jscolor.fromString("c00000");
    }
    function theme3() {
        //  alert("not jet defined")
    }
    function theme4() {
        //  alert("not jet defined")
    }
</script>

</body>
</html>