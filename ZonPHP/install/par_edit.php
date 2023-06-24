<?php
include_once "../parameters.php";
include_once "../inc/sessionstart.php";

if (!isset($_SESSION['passok']) || $_SESSION['passok'] != "passinorder")
    header('location:par_welcome.php');

include_once "../inc/connect.php";
include_once "par_header.php";

?>
<div id="menus">
    <?php include "par_menu.php"; ?>
</div>
<div id="container">
    <div id="bodytextparm">
        <div class="inside">
            <h2 class="notopgap" align="center">Parameters</h2>
            <?php

            $vlag = 0;

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $watverkeerd = "";
                foreach ($_POST as $key => $waarden) {
                    if (strlen($waarden) == 0 || ($_POST[$key] == getTxt("verkeerd"))) {
                        if ($key == 'plantname' || $key == 'google_tracking' || $key == 'chart_date_format') {
                            // plantname, google_tracking ... could be empty
                        } else {
                            $_POST[$key] = getTxt("verkeerd");
                            $watverkeerd .= "<br/>" . $key;
                            $vlag = 1;
                        }
                    }
                }
                if ($vlag == 1) {
                    echo getTxt("verkeerd") . $watverkeerd;
                }
            }
            if (($_SERVER['REQUEST_METHOD'] == 'POST') && $vlag == 0) {
                $sqldel = "DELETE FROM " . $table_prefix . "_parameters";
                $resultdel = mysqli_query($con, $sqldel);
                if (!$resultdel)
                    echo "<font size='0'color='#ff0000'>" . getTxt("verkeerd") . getTxt("tabelverkeerd") . $table_prefix . getTxt("tabelverkeerd1") . "</font><br/>";
                else {
                    $sqlsave = "INSERT INTO " . $table_prefix . "_parameters(Variable,Waarde)values";
                    foreach ($_POST as $key => $waarden) {
                        $sqlsave .= "('" . $key . "','" . $waarden . "'),";
                    }
                    $sqlsave = substr($sqlsave, 0, -1);
                    mysqli_query($con, $sqlsave) or die("Query failed. sql_save: " . mysqli_error($con));
                    echo "<font size='0'color='#00AA00'>" . getTxt("opgeslagen") . "</font><br/>";
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
                <?php echo getTxt("kiestijd"); ?><br/>
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
                <?php echo getTxt("prefix"); ?><br/>
                <?php echo getTxt("prefix_name") . ": <strong>" . $table_prefix . "</strong>"; ?>
                <br/>
                <hr>
                <label><?php echo getTxt("startdatum"); ?> :</label>
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
                <?php echo getTxt("available_languages"); ?>?<br>
                <input type='checkbox' name='lang_de'
                       size='5' <?php if (isset($_POST['lang_de'])) echo "checked"; ?> >DE <img
                        src="../inc/image/de.svg" border='0' width='16' height='12' alt="de"/>
                <br/>
                <input type='checkbox' name='lang_nl'
                       size='5' <?php if (isset($_POST['lang_nl'])) echo "checked"; ?> >NL <img
                        src="../inc/image/nl.svg" border='0' width='16' height='12' alt="nl"/>
                <br/>
                <input type='checkbox' name='lang_fr'
                       size='5' <?php if (isset($_POST['lang_fr'])) echo "checked"; ?> >FR <img
                        src="../inc/image/fr.svg" border='0' width='16' height='12' alt="fr"/>
                <br/>
                <input type='checkbox' name='lang_en'
                       size='5' <?php if (isset($_POST['lang_en'])) echo "checked"; ?> >EN <img
                        src="../inc/image/en.svg" border='0' width='16' height='12' alt="en"/>
                <br/>
                <hr>
                <?php echo getTxt("autorefresh");
                $autorefresh = 300;
                if (isset($_POST['autorefresh']) && is_numeric($_POST['autorefresh'])) {
                    $autorefresh = intval($_POST['autorefresh']);
                    if ($autorefresh < 0) {
                        $autorefresh = 300;
                    }
                }

                ?> <br/>
                <input type='text' name='autorefresh'
                       value='<?php echo $autorefresh ?>'
                       size='63'>
                <br/>
                <hr>
                <?php echo getTxt("keuzenaam"); ?> (,) bv: SLAPER,SIRA,FJORD<br/>
                <input type='text' name='sNaamSaveDatabasest'
                       value='<?php if (!empty($_POST['sNaamSaveDatabasest'])) echo $_POST['sNaamSaveDatabasest']; ?>'
                       size='63'>
                <br/>
                <hr>
                <?php echo getTxt("watpiek"); ?> (,) bv: 4500,10210,6880<br/>
                <input type='text' name='ieffectief_kwpiekst'
                       value='<?php if (!empty($_POST['ieffectief_kwpiekst'])) echo $_POST['ieffectief_kwpiekst']; else echo "5000" ?>'
                       size='63'>
                <br/>
                <hr>
                <?php echo getTxt("coefficient"); ?>.<br/>
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
                echo getTxt("inlezenbestand");
                ?>:
                <select NAME="sInvullen_gegevens">
                    <option <?php if ($selectedInverter == "none") echo "selected "; ?> >
                        none
                    </option>
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
                    <option <?php if ($selectedInverter == "Invullen_gegevens_sunny_explorer_2WR") echo "selected "; ?>>
                        Invullen_gegevens_sunny_explorer_2WR
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
                    <option <?php if ($selectedInverter == "sunny_explorer_utf16") echo "selected "; ?>>
                        sunny_explorer_utf16
                    </option>
                    <option <?php if ($selectedInverter == "sunny_explorer_thomas") echo "selected "; ?>>
                        sunny_explorer_thomas
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
                <div id="toverdiv"><?php echo getTxt("plantname"); ?>
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
                <?php echo getTxt("gegevensinstallatie"); ?>.<br/>
                <table>
                    <tbody>

                    <tr>
                        <td><?php echo getTxt("naamwebsite"); ?>:</td>
                        <td><input type='text' name='sNaamVoorOpWebsite'
                                   value='<?php if (!empty($_POST['sNaamVoorOpWebsite'])) echo $_POST['sNaamVoorOpWebsite']; else echo "your name" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo getTxt("firmanaam"); ?>:</td>
                        <td><input type='text' name='sNaam_Installateur'
                                   value='<?php if (!empty($_POST['sNaam_Installateur'])) echo $_POST['sNaam_Installateur']; else echo "your installateur" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo getTxt("websitefirma"); ?>:</td>
                        <td><input type='text' name='sWebsite_Installateur'
                                   value='<?php if (!empty($_POST['sWebsite_Installateur'])) echo $_POST['sWebsite_Installateur']; else echo "your website admin" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo getTxt("aantalsoortpaneel"); ?>:</td>
                        <td><input type='text' name='sSoort_pannel_aantal'
                                   value='<?php if (!empty($_POST['sSoort_pannel_aantal'])) echo $_POST['sSoort_pannel_aantal']; else echo "your panels" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo getTxt("omvormer"); ?>:</td>
                        <td><input type='text' name='sOmvormer'
                                   value='<?php if (!empty($_POST['sOmvormer'])) echo $_POST['sOmvormer']; else echo " your converter" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo getTxt("orientatie"); ?>:</td>
                        <td><input type='text' name='sOrientatie'
                                   value='<?php if (!empty($_POST['sOrientatie'])) echo $_POST['sOrientatie']; else echo "your orientation" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo getTxt("datacaptatie"); ?>:</td>
                        <td><input type='text' name='sData_Captatie'
                                   value='<?php if (!empty($_POST['sData_Captatie'])) echo $_POST['sData_Captatie']; else echo "your data capture" ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo getTxt("woonplaats"); ?>:</td>
                        <td><input type='text' name='sPlaats'
                                   value='<?php if (!empty($_POST['sPlaats'])) echo $_POST['sPlaats']; else echo "your location" ?>'
                                   size='63'>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo getTxt("image1"); ?>:</td>
                        <td><input type='text' name='image1'
                                   value='<?php if (!empty($_POST['image1'])) echo $_POST['image1']; else {
                                       $_POST['image1'] = "inc/image/image1.jpg";
                                       echo $_POST['image1'];
                                   } ?>'
                                   size='63'></td>
                    </tr>
                    <tr>
                        <td><?php echo getTxt("image2"); ?>:</td>
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
                <?php echo getTxt("flyout_menu"); ?>?
                <br>
                <?php echo getTxt("hide_menu"); ?>?
                <input type='checkbox' name='hide_menu'
                       size='5' <?php if (isset($_POST['hide_menu'])) echo "checked"; ?> >
                <br/>
                <?php echo getTxt("hide_footer"); ?>?
                <input type='checkbox' name='hide_footer'
                       size='5' <?php if (isset($_POST['hide_footer'])) echo "checked"; ?> >
                <br>
                <hr>
                <?php echo getTxt("chart_date_format"); ?>:
                <input type='text' name='chart_date_format'
                       value='<?php if (!empty($_POST['chart_date_format'])) echo $_POST['chart_date_format']; ?>'
                       size='63'><br/>

                <hr>
                <?php echo getTxt("google_tracking"); ?>:
                <input type='text' name='google_tracking' size='40'
                       value='<?php if (!empty($_POST['google_tracking'])) echo $_POST['google_tracking'] ?>'>
                <br/>
                <hr>
                <?php
                if (empty($_POST['colortheme'])) {
                    $_POST['colortheme'] = "user";
                }
                $theme = $_POST['colortheme'];
                echo getTxt("choose") . " " . getTxt("colortheme") . ":";
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
                <?php echo getTxt("CustomFields"); ?>:<br>
                <?php echo getTxt("EMU_Offset"); ?>: <input type='text' name='EMU_Offset'
                                                            value='<?php if (!empty($_POST['EMU_Offset'])) echo $_POST['EMU_Offset']; else echo "0" ?>'
                                                            size='5'><br/>
                <?php echo getTxt("Path_EMU"); ?>: <input type='text' name='Path_EMU'
                                                          value='<?php if (!empty($_POST['Path_EMU'])) echo $_POST['Path_EMU']; else echo "-" ?>'
                                                          size='60'><br/>
                <?php echo getTxt("Path_Webroot"); ?>: <input type='text' name='Path_Webroot'
                                                              value='<?php if (!empty($_POST['Path_Webroot'])) echo $_POST['Path_Webroot']; else echo "-" ?>'
                                                              size='60'><br/>
                <?php echo getTxt("Path_Zonphp"); ?>: <input type='text' name='Path_Zonphp'
                                                             value='<?php if (!empty($_POST['Path_Zonphp'])) echo $_POST['Path_Zonphp']; else echo "-" ?>'
                                                             size='60'><br/>
                <?php echo getTxt("PVO_API"); ?>: <input type='text' name='PVO_API'
                                                         value='<?php if (!empty($_POST['PVO_API'])) echo $_POST['PVO_API']; else echo "-" ?>'
                                                         size='60'><br/>
                <?php echo getTxt("PVO_SYS_ID"); ?>: <input type='text' name='PVO_SYS_ID'
                                                            value='<?php if (!empty($_POST['PVO_SYS_ID'])) echo $_POST['PVO_SYS_ID']; else echo "-" ?>'
                                                            size='60'><br/>
                <hr>

                <INPUT name="savecontrole" TYPE="submit" VALUE="<?php echo getTxt("save"); ?>"
                       onClick="return confirmSubmit()">
            </FORM>
        </div>
    </div>
</div>
</body>
</html>
