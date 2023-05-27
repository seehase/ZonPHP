<?php

include_once "parameters.php";
include_once "inc/sessionstart.php";
include_once "startup.php";

?>
<?php
include_once "inc/header.php";
?>

    <div id="menus">
        <?php include_once "inc/menu.php"; ?>
    </div>
    <div id="container">
        <div id="bodytext">
            <div class="inside">
                <font size="-1">Dit script leest het bestand Invullen_aangepaste_gegevens.txt en past waar nodig de
                    aanpassingen toe aan te database.<br/><br/>
                    In dat bestand kan je manuele gegevens invullen, handig wanneer er ontbrekende of onvolledige
                    databestanden zijn.
                    Deze gegevens hebben voorrang op de gegevens gelezen uit andere bestanden!<br/><br/>
                    Let op de datumnotatie en gebruik een punt als decimaalteken.<br/>
                    bv :<br/>
                    &nbsp;&nbsp;&nbsp;2009-09-04;10.5;SLAPER<br/>
                    &nbsp;&nbsp;&nbsp;2009-06-21;27.19;FJORD<br/>
                    Enkel de datums die moeten gewijzigd worden dienen aanwezig te zijn.</font><br/>
                <hr>
                <font size="-1">Aangepaste data:</font><br/>


                <div id="tabelgegevensbanner">
                    <?php
                    if ($param['sInvullen_gegevens'] == "Invullen_gegevens_xls")
                        $xls_file = " 23:59:59";
                    else
                        $xls_file = "";

                    $sbestand = "Invullen_manuele_gegevens.txt";

                    $file = fopen($sbestand, "r") or die ("ERROR READ");
                    $teller = 1;
                    while (!feof($file)) {
                        $iverkeerd = 0;
                        $geg = fgets($file, 1024);
                        //echo $geg;echo "<br />";
                        $geg = trim($geg);
                        if ($teller > 2) {
                            if (!empty($geg)) {
                                $alist = explode(";", $geg);
                                if (!isset($alist[0])) $iverkeerd = 1; else if (controledatum($alist[0])) $TimeStamp = $alist[0]; else $iverkeerd = 1;
                                if (!isset($alist[1])) $iverkeerd = 1; else $kWh = $alist[1];
                                if (!isset($alist[2])) $iverkeerd = 1; else $Naam = $alist[2];
                                if ($iverkeerd == 0) {
                                    $Naam = trim($Naam);
                                    $string = "INSERT INTO " . $table_prefix . "_maand(IndexMaand,Datum_Maand,Geg_Maand,Naam) VALUES ('" . $TimeStamp . $xls_file . $Naam . "','" . $TimeStamp . $xls_file . "'," . $kWh . ",'" . $Naam . "') ON DUPLICATE KEY UPDATE Geg_Maand=" . $kWh . "";
                                    //echo $string;echo "<br />";
                                    mysqli_query($con, $string) or die ('SQL Error string:' . mysqli_error($con));

                                    echo $TimeStamp;
                                    echo "=";
                                    echo $kWh;
                                    echo "=";
                                    echo "$Naam";
                                    echo "<br />";

                                } else
                                    echo "<b>Verkeerde data in " . $sbestand . " op lijn " . $teller . "</b><br />";
                            }
                        }
                        $teller++;
                    }
                    fclose($file);
                    mysqli_close($con);
                    ?>
                    <?php
                    function controledatum($date)
                    {
                        $j_m_d = explode("-", $date);
                        if (!isset($j_m_d[0])) return false;
                        if (!isset($j_m_d[1])) return false;
                        if (!isset($j_m_d[2])) return false;
                        if (!checkdate($j_m_d[1], $j_m_d[2], $j_m_d[0])) {
                            return false;
                        } else {
                            return true;
                        }
                    }

                    ?>
                </div>
                <p class="nobottomgap"></p>
            </div>
        </div>
    </div>

<?php include_once "footer.php"; ?>