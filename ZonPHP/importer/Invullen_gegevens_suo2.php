<?php
//
//include "Parameters.php";
//include "sessionstart.php";
//include "startup.php";
?>
<?php

$sql = "SELECT *
	FROM " . $table_prefix . "_dag
	ORDER BY Datum_Dag DESC LIMIT 1";
$result = mysql_queryi($con, $sql) or die("invullen gegevens solar ERROR: " . mysqli_error($con));

if (mysqli_num_rows($result) == 0)
    $dateTime = $dstartdatum;
else {
    while ($row = mysqli_fetch_array($result)) {
        $dateTime = $row['Datum_Dag'];
    }
}
$dateTime = date("Y-m-d H:i:s", strtotime($dateTime));
$dateTime1 = $dateTime;

$directory = "" . $_SESSION['Wie'] . "/";
$aday = array();

for ($tel = 0; $tel <= 60; $tel++) {
    //Eintragen ab 12.03.2008
    $num = (date("ymd", strtotime("+" . $tel . " day", strtotime($dateTime))));  //echo $num."<br />";echo $directory . $num.'.suo'."<br />";
    // Eintragen bis 12.03.2008
    //$num=(date("dmy",strtotime("+".$tel." day", strtotime($dateTime))));  //echo $num."<br />";echo $directory . $num.'.suo'."<br />";

    $HVL_suo = 0;
    $stHVL_suo = "0" . $HVL_suo;
    while (file_exists($directory . $num . $stHVL_suo . '.suo')) {
        $adag[] = $directory . $num . $stHVL_suo . '.suo';
        $HVL_suo++;
        if ($HVL_suo < 10)
            $stHVL_suo = "0" . $HVL_suo;
        else
            $stHVL_suo = $HVL_suo;
    }
}
?>

<?php
if (!empty($adag)) {
    foreach ($adag as $v) {
        $teller = 1;
        $string11 = "";
        $string12 = "";
        $string13 = "";
        $string14 = "";
        $string15 = "";
        $start1 = 0;
        $start2 = 0;
        $start3 = 0;
        $start4 = 0;
        $start5 = 0;
        $startend = 0;
        $startend2 = 0;
        $startend3 = 0;
        $startend4 = 0;
        $startend5 = 0;
        $houdeeindwaarde = 0;
        $houdeeindwaarde2 = 0;
        $houdeeindwaarde3 = 0;
        $houdeeindwaarde4 = 0;
        $houdeeindwaarde5 = 0;
        $stringend = "";
        $stringend2 = "";
        $stringend3 = "";
        $stringend4 = "";
        $stringend5 = "";
        $voegnulltoe = 0;
        $voegnulltoe2 = 0;
        $voegnulltoe3 = 0;
        $voegnulltoe4 = 0;
        $voegnulltoe5 = 0;
        $string1 = "insert into " . $table_prefix . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
        $string2 = "insert into " . $table_prefix . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
        $string3 = "insert into " . $table_prefix . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
        $string4 = "insert into " . $table_prefix . "_dag(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values";
        $string5 = "insert into " . $table_prefix . "_dag_sum(IndexDag,Datum_Dag,Geg_Dag,kWh_Dag,Naam)values"; //Summe

        $file = fopen($v, "r") or die ("Kan " . $v . " niet openen");
        while (!feof($file)) {
            $geg_suo = fgets($file, 1024);
            $geg_suo = trim($geg_suo);
            if ($teller > 5) {
                if (!empty($geg_suo)) {
                    //echo "<pre>".print_r($geg_suo,true)."/<pre>";

                    /*/ import ab 06.10.2002 Trenner== Tab
                    $p=explode("\t",$geg_suo);
                    list ($TimeStamp,$UpvIst2,$UpvSoll2,$IacIst2,$Uac2,$Fac2,$MeteringDykWh2,$Zac2,$RErdStart2,$Ipv2,$dI2,$GridMsTotW2,$hTotal2,$NetzEin2,$Seriennummer2,$Status2,$Fehler2,$UpvIst3,$UpvSoll3,$IacIst3,$Uac3,$Fac3,$MeteringDykWh3,$Zac3,$RErdStart3,$Ipv3,$dI3,$GridMsTotW3,$hTotal3,$NetzEin3,$Seriennummer3,$Status3,$Fehler3,$PV1,$PV2,$PV3,$PV4,$PV5,$PV6,$PV7)=explode("\t",$geg_suo);
                    $MeteringDykWh = 0;
                    $GridMsTotW = 0;
                    */

                    /*/ import ab 13.07.2003 ab 01 Trenner== Tab
                    $p=explode("\t",$geg_suo);
                    list ($TimeStamp,$UpvIst2,$UpvSoll2,$IacIst2,$Uac2,$Fac2,$MeteringDykWh2,$Zac2,$RErdStart2,$Ipv2,$dI2,$GridMsTotW2,$hTotal2,$NetzEin2,$Seriennummer2,$Status2,$Fehler2,$UpvIst3,$UpvSoll3,$IacIst3,$Uac3,$Fac3,$MeteringDykWh3,$Zac3,$RErdStart3,$Ipv3,$dI3,$GridMsTotW3,$hTotal3,$NetzEin3,$Seriennummer3,$Status3,$Fehler3,$UpvIst,$UpvSoll,$IacIst,$Uac,$Fac,$MeteringDykWh,$Zac,$Riso,$Ipv,$GridMsTotW,$hTotal,$NetzEin,$Seriennummer,$Status,$Fehler,$PV1,$PV2,$PV3,$PV4,$PV5,$PV6,$PV7)=explode("\t",$geg_suo);
                    */

                    /*/ import ab 12.03.2008	Trenner== ;
                    $p=explode(";",$geg_suo);
                    list ($TimeStamp,$UpvIst,$UpvSoll,$IacIst,$IacSoll,$Uac,$Fac,$MeteringDykWh,$Zac,$dZac,$Riso,$UacSrr,$FacSrr,$ZacSrr,$Izac,$Tkk,$Ipv,$Tkkmax,$Upvmax,$Reserve,$Replaced,$GridMsTotW,$hTotal,$NetzEin,$FehlerCnt,$Seriennummer,$Status,$Fehler,$UpvIst2,$UpvSoll2,$IacIst2,$IacSoll2,$Uac2,$Fac2,$MeteringDykWh2,$Zac2,$dZac2,$RErdStart2,$UacSrr2,$FacSrr2,$ZacSrr2,$Izac2,$Tkk2,$Ipv2,$Uzwk2,$dI2,$dISrr2,$GridMsTotW2,$hTotal2,$NetzEin2,$FehlerCnt2,$Seriennummer2,$Status2,$Fehler2,$UpvIst3,$UpvSoll3,$IacIst3,$IacSoll3,$Uac3,$Fac3,$MeteringDykWh3,$Zac3,$dZac3,$RErdStart3,$UacSrr3,$FacSrr3,$ZacSrr3,$Izac3,$Tkk3,$Ipv3,$Uzwk3,$dI3,$dISrr3,$GridMsTotW3,$hTotal3,$NetzEin3,$FehlerCnt3,$Seriennummer3,$Status3,$Fehler3)=explode(";",$geg_suo);
                    */

                    // import ab 8.06.2014	Trenner==  4 Wechselrichter;
                    $p = explode(";", $geg_suo);
                    list ($TimeStamp, $UpvIst, $UpvSoll, $IacIst, $IacSoll, $Uac, $Fac, $MeteringDykWh, $Zac, $dZac, $Riso, $UacSrr, $FacSrr, $ZacSrr, $Izac, $Tkk, $Ipv, $Tkkmax, $Upvmax, $Reserve, $Replaced, $GridMsTotW, $hTotal, $NetzEin, $FehlerCnt, $Seriennummer, $Status, $Fehler, $UpvIst2, $UpvSoll2, $IacIst2, $IacSoll2, $Uac2, $Fac2, $MeteringDykWh2, $Zac2, $dZac2, $RErdStart2, $UacSrr2, $FacSrr2, $ZacSrr2, $Izac2, $Tkk2, $Ipv2, $Uzwk2, $dI2, $dISrr2, $GridMsTotW2, $hTotal2, $NetzEin2, $FehlerCnt2, $Seriennummer2, $Status2, $Fehler2, $UpvIst3, $UpvSoll3, $IacIst3, $IacSoll3, $Uac3, $Fac3, $MeteringDykWh3, $Zac3, $dZac3, $RErdStart3, $UacSrr3, $FacSrr3, $ZacSrr3, $Izac3, $Tkk3, $Ipv3, $Uzwk3, $dI3, $dISrr3, $GridMsTotW3, $hTotal3, $NetzEin3, $FehlerCnt3, $Seriennummer3, $Status3, $Fehler3, $UpvIst4, $UpvSoll4, $IacIst4, $IacSoll4, $Uac4, $Fac4, $MeteringDykWh4, $RErdStart4, $UacSrr4, $FacSrr4, $Tkk4, $Ipv4, $Tkkmax4, $Upvmax4, $Uzwk4, $dI4, $dISrr4, $GridMsTotW4, $hTotal4, $hOn4, $NetzEin4, $EventCnt4, $Seriennummer4, $Status4, $Fehler4) = explode(";", $geg_suo);

                    //list(TimeStamp,$GridMsTotW,$MeteringDykWh,$GridMsTotW2,$MeteringDykWh2,$GridMsTotW3,$MeteringDykWh3)=explode(";",$geg_suo);	//,$rest
                    //echo '<pre>'.print_r($p,true).'<pre>';
                    //$oTimeStamp=omzetdatum($TimeStamp);

                    $oTimeStamp = date("Y-m-d H:i:s", strtotime($TimeStamp));
                    //$oTimeStamp1 = date('Y-m-d H:i:s',strtotime($dateTime1)); //Datenbank letzer eintrag plus 5 Minuten
                    $oTimeStamp1 = date('Y-m-d H:i:s', strtotime($dateTime1) + ('231')); //Datenbank letzer eintrag plus 5 Minuten

                    /*echo $dateTime.'z1 <br />';
                    echo $oTimeStamp.'z2 <br />';
                    echo $oTimeStamp1.'z3 <br />';
                    echo $GridMsTotW.'a<br />';
                    echo $MeteringDykWh.'a<br />';
                    echo $GridMsTotW2.'b<br />';
                    echo $MeteringDykWh2.'b<br />';
                    echo $GridMsTotW3.'c<br />';
                    echo $MeteringDykWh3.'c<br />';
                    */
                    if (((is_numeric($GridMsTotW)) or (is_string($GridMsTotW))) and ($GridMsTotW > 0) and $start1 == 0) {
                        $GridMsTotW = str_replace(array(','), '.', $GridMsTotW);
                        $startkw = $GridMsTotW;//echo $startkw.'<br />';
                        $startkw1s = $GridMsTotW;//summe
                        $start1 = 1;
                    }
                    if (((is_numeric($GridMsTotW2)) or (is_string($GridMsTotW2))) and ($GridMsTotW2 > 0) and $start2 == 0) {
                        $GridMsTotW2 = str_replace(array(','), '.', $GridMsTotW2);
                        $startkw2 = $GridMsTotW2;//echo $startkw2.'<br />';
                        $startkw2s = $GridMsTotW2;//summe
                        $start2 = 1;
                    }
                    if (((is_numeric($GridMsTotW3)) or (is_string($GridMsTotW3))) and ($GridMsTotW3 > 0) and $start3 == 0) {
                        $GridMsTotW3 = str_replace(array(','), '.', $GridMsTotW3);
                        $startkw3 = $GridMsTotW3;//echo $startkw3.'<br />';
                        $startkw3s = $GridMsTotW3;//summe
                        $start3 = 1;
                    }
                    if (((is_numeric($GridMsTotW4)) or (is_string($GridMsTotW4))) and ($GridMsTotW4 > 0) and $start4 == 0) {
                        $GridMsTotW4 = str_replace(array(','), '.', $GridMsTotW4);
                        $startkw4 = $GridMsTotW4;//echo $startkw4.'<br />';
                        $startkw4s = $GridMsTotW4;//summe
                        $start4 = 1;
                    }

                    $W1sum = 0;
                    $DykWh1sum = 0;
                    $W2sum = 0;
                    $DykWh2sum = 0;
                    $W3sum = 0;
                    $DykWh3sum = 0;
                    $W4sum = 0;
                    $DykWh4sum = 0;

                    if ((strtotime($oTimeStamp) > strtotime($dateTime)) and ($oTimeStamp != "geen datumtijd")) {

                        $tempwie = $_SESSION['Wie'];
                        //WR1
                        if (((is_numeric($GridMsTotW)) or (is_string($GridMsTotW))) and ($GridMsTotW > 0)) {
                            $MeteringDykWh = str_replace(array(','), '.', $MeteringDykWh);
                            $GridMsTotW = str_replace(array(','), '.', $GridMsTotW);

                            if ($oTimeStamp > $oTimeStamp1) {

                                $_SESSION['Wie'] = "WR1";
                                if ($MeteringDykWh != 0 && $start1 == 1) {
                                    if ($MeteringDykWh == 0) {
                                        //echo "<br />" . "Konstruktion String11 in Durchlauf Nr " . $teller . "<br />";
                                        $startend = 1;
                                        $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                        $stringend .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh . "," . number_format($GridMsTotW - $startkw, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                        $string11 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotW - $startkw) * $param['coefficient'], 3, '.', '') . ",'" . $_SESSION['Wie'] . "')";
                                    } else {
                                        $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                        //$GridMsTotW=$GridMsTotW*$param['coefficient'];
                                        if ($startend == 0) {
                                            //echo "<br />" . "Konstruktion String1 startend==0 in Durchlauf Nr " . $teller . "<br />";
                                            $string1 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh . "," . number_format($GridMsTotW - $startkw, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                        } else {
                                            //echo "<br />" . "Konstruktion String1 startend!=0 in Durchlauf Nr " . $teller . "<br />";
                                            $string1 .= $stringend . "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh . "," . number_format($GridMsTotW - $startkw, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                        }
                                        $string11 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotW - $startkw) * $param['coefficient'], 3, '.', '') . ",'" . $_SESSION['Wie'] . "')";
                                        $stringend = "";
                                        $startend = 0;
                                        $houdeeindwaarde = 1;
                                        $voegnulltoe = 0;
                                    }
                                } else {
                                    if ($houdeeindwaarde != 0 && $voegnulltoe == 0) {
                                        $string1 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "',0," . number_format($GridMsTotW - $startkw, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                        $voegnulltoe = 1;
                                    }
                                }
                                $dateTime1 = $oTimeStamp;
                                $W1sum = ($GridMsTotW - $startkw); //Summe
                                $DykWh1sum = $MeteringDykWh;    //Summe
                                $start5 = 1;
                            }
                        }
                        //WR2
                        if (((is_numeric($GridMsTotW2)) or (is_string($GridMsTotW2))) and ($GridMsTotW2 > 0)) {
                            $MeteringDykWh2 = str_replace(array(','), '.', $MeteringDykWh2);
                            $GridMsTotW2 = str_replace(array(','), '.', $GridMsTotW2);

                            if ($oTimeStamp > $oTimeStamp1) {

                                $_SESSION['Wie'] = "WR2";
                                if ($MeteringDykWh2 != 0 && $start2 == 1) {
                                    if ($MeteringDykWh2 == 0) {
                                        $startend2 = 1;
                                        $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                        $stringend2 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh2 . "," . number_format($GridMsTotW2 - $startkw2, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                        $string12 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotW2 - $startkw2) * $param['coefficient'], 3, '.', '') . ",'" . $_SESSION['Wie'] . "')";
                                    } else {
                                        $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                        //$GridMsTotW2=$GridMsTotW2*$param['coefficient'];
                                        if ($startend2 == 0) {
                                            $string2 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh2 . "," . number_format($GridMsTotW2 - $startkw2, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                        } else
                                            $string2 .= $stringend2 . "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh2 . "," . number_format($GridMsTotW2 - $startkw2, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";

                                        $string12 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotW2 - $startkw2) * $param['coefficient'], 3, '.', '') . ",'" . $_SESSION['Wie'] . "')";
                                        $stringend2 = "";
                                        $startend2 = 0;
                                        $houdeeindwaarde2 = 1;
                                        $voegnulltoe2 = 0;
                                    }
                                } else {
                                    if ($houdeeindwaarde2 != 0 && $voegnulltoe2 == 0) {
                                        $string2 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "',0," . number_format($GridMsTotW2 - $startkw2, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                        $voegnulltoe2 = 1;
                                    }
                                }
                                $dateTime1 = $oTimeStamp;
                                $W2sum = ($GridMsTotW2 - $startkw2);  //Summe
                                $DykWh2sum = $MeteringDykWh2;        //Summe
                                $start5 = 1;
                            }
                        }
                        //WR3
                        if (((is_numeric($GridMsTotW3)) or (is_string($GridMsTotW3))) and ($GridMsTotW3 > 0)) {
                            $MeteringDykWh3 = str_replace(array(','), '.', $MeteringDykWh3);
                            $GridMsTotW3 = str_replace(array(','), '.', $GridMsTotW3);

                            if ($oTimeStamp > $oTimeStamp1) {
                                $_SESSION['Wie'] = "WR3";
                                if ($MeteringDykWh3 != 0 && $start3 == 1) {
                                    if ($MeteringDykWh3 == 0) {
                                        $startend3 = 1;
                                        $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                        $stringend3 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh3 . "," . number_format($GridMsTotW3 - $startkw3, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                        $string13 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotW3 - $startkw3) * $param['coefficient'], 3, '.', '') . ",'" . $_SESSION['Wie'] . "')";
                                    } else {
                                        $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                        //$GridMsTotW3=$GridMsTotW3*$param['coefficient'];
                                        if ($startend3 == 0)
                                            $string3 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh3 . "," . number_format($GridMsTotW3 - $startkw3, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";

                                        else
                                            $string3 .= $stringend3 . "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh3 . "," . number_format($GridMsTotW3 - $startkw3, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";

                                        $string13 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotW3 - $startkw3) * $param['coefficient'], 3, '.', '') . ",'" . $_SESSION['Wie'] . "')";
                                        $stringend3 = "";
                                        $startend3 = 0;
                                        $houdeeindwaarde3 = 1;
                                        $voegnulltoe3 = 0;
                                    }
                                } else {
                                    if ($houdeeindwaarde3 != 0 && $voegnulltoe3 == 0) {
                                        $string3 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "',0," . number_format($GridMsTotW3 - $startkw3, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                        $voegnulltoe3 = 1;
                                    }
                                }
                                $dateTime1 = $oTimeStamp;
                                $W3sum = ($GridMsTotW3 - $startkw3);
                                $DykWh3sum = $MeteringDykWh3;
                                $start5 = 1;
                            }
                        }
//WR4
                        if (((is_numeric($GridMsTotW4)) or (is_string($GridMsTotW4))) and ($GridMsTotW4 > 0)) {
                            $MeteringDykWh4 = str_replace(array(','), '.', $MeteringDykWh4);
                            $GridMsTotW4 = str_replace(array(','), '.', $GridMsTotW4);

                            if ($oTimeStamp > $oTimeStamp1) {
                                $_SESSION['Wie'] = "WR4";
                                if ($MeteringDykWh4 != 0 && $start4 == 1) {
                                    if ($MeteringDykWh4 == 0) {
                                        $startend4 = 1;
                                        $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                        $stringend4 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh4 . "," . number_format($GridMsTotW4 - $startkw4, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                        $string14 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotW4 - $startkw4) * $param['coefficient'], 3, '.', '') . ",'" . $_SESSION['Wie'] . "')";
                                    } else {
                                        $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                        //$GridMsTotW3=$GridMsTotW3*$param['coefficient'];
                                        if ($startend4 == 0)
                                            $string4 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh4 . "," . number_format($GridMsTotW4 - $startkw4, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";

                                        else
                                            $string4 .= $stringend4 . "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWh3 . "," . number_format($GridMsTotW3 - $startkw4, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";

                                        $string14 = "insert into " . $table_prefix . "_maand (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotW4 - $startkw4) * $param['coefficient'], 3, '.', '') . ",'" . $_SESSION['Wie'] . "')";
                                        $stringend4 = "";
                                        $startend4 = 0;
                                        $houdeeindwaarde4 = 1;
                                        $voegnulltoe4 = 0;
                                    }
                                } else {
                                    if ($houdeeindwaarde4 != 0 && $voegnulltoe4 == 0) {
                                        $string4 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "',0," . number_format($GridMsTotW3 - $startkw4, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                        $voegnulltoe4 = 1;
                                    }
                                }
                                $dateTime1 = $oTimeStamp;
                                $W4sum = ($GridMsTotW4 - $startkw4);
                                $DykWh4sum = $MeteringDykWh4;
                                $start5 = 1;
                            }
                        }
// Summe anfang
                        //WRSum
                        $MeteringDykWhSum = $DykWh1sum + $DykWh2sum + $DykWh3sum + $DykWh4sum;
                        $GridMsTotWSum = $W1sum + $W2sum + $W3sum + $W4sum;
                        $startkwSum = 0;

                        if ($oTimeStamp > $oTimeStamp1) {
                            $_SESSION['Wie'] = "WRSum";

                            if ($MeteringDykWhSum != 0 && $start5 == 1) {
                                if ($MeteringDykWhSum == 0) {
                                    //echo "<br />" . "Konstruktion String15 /1 in Durchlauf Nr " . $teller . "<br />";
                                    $startend5 = 1;
                                    $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                    $stringend5 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWhSum . "," . number_format($GridMsTotWSum - $startkwSum, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                    $string15 = "insert into " . $table_prefix . "_maand_sum (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotWSum - $startkwSum) * $param['coefficient'], 3, '.', '') . ",'" . $_SESSION['Wie'] . "')";
                                } else {
                                    //echo "<br />" . "Konstruktion String15 /2 in Durchlauf Nr  " . $teller . "<br />";
                                    $odatum = explode(" ", $oTimeStamp);//echo '<pre>'.print_r($odatum,true).'<pre>';
                                    //$GridMsTotW5=$GridMsTotW5*$param['coefficient'];

                                    if ($startend5 == 0)
                                        $string5 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWhSum . "," . number_format($GridMsTotWSum - $startkwSum, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";

                                    else
                                        $string5 .= $stringend5 . "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "'," . $MeteringDykWhSum . "," . number_format($GridMsTotWSum - $startkwSum, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";

                                    $string15 = "insert into " . $table_prefix . "_maand_sum (IndexMaand,Datum_Maand,Geg_Maand,Naam)values('" . $odatum[0] . $_SESSION['Wie'] . "','" . $odatum[0] . "'," . number_format(($GridMsTotWSum - $startkwSum) * $param['coefficient'], 3, '.', '') . ",'" . $_SESSION['Wie'] . "')";
                                    $stringend5 = "";
                                    $startend5 = 0;
                                    $houdeeindwaarde5 = 1;
                                    $voegnulltoe5 = 0;
                                }
                            } else {
                                //echo "<br />" . "Konstruktion String15 /3 in Durchlauf Nr " . $teller . "<br />";
                                if ($houdeeindwaarde5 != 0 && $voegnulltoe5 == 0) {
                                    $string5 .= "('" . $oTimeStamp . $_SESSION['Wie'] . "','" . $oTimeStamp . "',0," . number_format($GridMsTotWSum - $startkwSum, 3, '.', '') . ",'" . $_SESSION['Wie'] . "'),";
                                    $voegnulltoe5 = 1;
                                }
                            }
                            $dateTime1 = $oTimeStamp;
                        }
                        // Summe ende
                        $_SESSION['Wie'] = $tempwie;
                    }
                }
            }
            $teller++;
        }
        fclose($file);

        $tempwie = $_SESSION['Wie'];

        $_SESSION['Wie'] = "WR1";
        if ($string11 != "") {
            $string1 = substr($string1, 0, -1);
            //echo "WR1 String1: " . $string1 . "<br />";
            //echo "WR1 String11: " . $string11 . "<br /><br />";
            mysqli_query($con, "DELETE FROM " . $table_prefix . "_maand WHERE Naam ='" . $_SESSION['Wie'] . "' AND Datum_Maand='" . $odatum[0] . "'") or die("Query failed1. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string1) or die("Query failed2. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string11) or die("Query failed3. ERROR: " . mysqli_error($con));
            //echo $oDatum;echo "<br />";
        }

        $_SESSION['Wie'] = "WR2";
        if ($string12 != "") {
            $string2 = substr($string2, 0, -1);
            //echo "WR2 String2: " . $string2 . "<br />";
            //echo "WR2 String12: " . $string12 . "<br /><br />";
            mysqli_query($con, "DELETE FROM " . $table_prefix . "_maand WHERE Naam ='" . $_SESSION['Wie'] . "' AND Datum_Maand='" . $odatum[0] . "'") or die("Query failed4. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string2) or die("Query failed5. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string12) or die("Query failed6. ERROR: " . mysqli_error($con));
            //echo $oDatum;echo "<br />";
        }

        $_SESSION['Wie'] = "WR3";
        if ($string13 != "") {
            $string3 = substr($string3, 0, -1);
            //echo "WR3 String3: " . $string3 . "<br />";
            //echo "WR3 String13: " . $string13 . "<br /><br />";
            mysqli_query($con, "DELETE FROM " . $table_prefix . "_maand WHERE Naam ='" . $_SESSION['Wie'] . "' AND Datum_Maand='" . $odatum[0] . "'") or die("Query failed7. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string3) or die("Query failed8. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string13) or die("Query failed9. ERROR: " . mysqli_error($con));
            //echo $oDatum;echo "<br />";
        }

        $_SESSION['Wie'] = "WR4";
        if ($string14 != "") {
            $string4 = substr($string4, 0, -1);
            //echo "WR4 String4: " . $string4 . "<br />";
            //echo "WR4 String14: " . $string14 . "<br /><br />";
            mysqli_query($con, "DELETE FROM " . $table_prefix . "_maand WHERE Naam ='" . $_SESSION['Wie'] . "' AND Datum_Maand='" . $odatum[0] . "'") or die("Query failed10. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string4) or die("Query failed11. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string14) or die("Query failed12. ERROR: " . mysqli_error($con));
            //echo $oDatum;echo "<br />";
        }
//Summe anfang	
        $_SESSION['Wie'] = "WRSum";
        if ($string15 != "") {
            $string5 = substr($string5, 0, -1);
            //echo "WRSum String4: " . $string4 . "<br />";
            //echo "WRSum String14: " . $string14 . "<br /><br />";
            mysqli_query($con, "DELETE FROM " . $table_prefix . "_maand_sum WHERE Naam ='" . $_SESSION['Wie'] . "' AND Datum_Maand='" . $odatum[0] . "'") or die("Query failed13. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string5) or die("Query failed14. ERROR: " . mysqli_error($con));
            mysqli_query($con, $string15) or die("Query failed15. ERROR: " . mysqli_error($con));
            //echo $oDatum;echo "<br />";
        }
//Summe ende	
        $_SESSION['Wie'] = $tempwie;
    }
}
?>
<?php
/******************************************************************************
 * maak months.js bestand aan
 * ****************************************************************************
 */
$sql = "SELECT MAX(Datum_Maand) AS maxi,SUM(Geg_Maand) AS som
	FROM " . $table_prefix . "_maand

	GROUP BY DATE_FORMAT(Datum_Maand,'%y-%m')
	ORDER BY 1 DESC";

$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $dateTime = "Leeg";
} else {
    $fp = fopen($directory . 'months.js', "w+");
    while ($row = mysqli_fetch_array($result)) {
        $datfile = date("d.m.y", strtotime($row['maxi']));
        $dateTime = 'mo[mx++]="' . $datfile . "|" . floor($row['som'] * 1000) . '"';//echo $dateTime; //datum omzetten

        //$fp = fopen('months1.js',"r+");
        fwrite($fp, $dateTime . "\r\n");
    }
    fclose($fp);
}

/******************************************************************************
 * maak days_hist.js bestand aan
 * ****************************************************************************
 */
$sql = "SELECT *
	FROM " . $table_prefix . "_maand

	ORDER BY Datum_Maand DESC";
$result = mysqli_query($con, $sql) or die("Query failed. ERROR: " . mysqli_error($con));
if (mysqli_num_rows($result) == 0) {
    $dateTime = "Leeg";
} else {
    $fp = fopen($directory . 'days_hist.js', "w+");
    while ($row = mysqli_fetch_array($result)) {
        $datfile = date("d.m.y", strtotime($row['Datum_Maand']));
        //echo $datfile."<br />";
        $dateTime = 'da[dx++]="' . $datfile . "|" . floor($row['Geg_Maand'] * 1000) . ';1000"';//echo $dateTime; //datum omzetten
        //$fp = fopen('months1.js',"r+");
        fwrite($fp, $dateTime . "\r\n");
    }
    fclose($fp);
}
?>
<?php
function omzetdatum($date)
{
    //echo $date.'<br />';
    $date = str_replace(array('.', '/', '-', ' ', ':'), '/', $date);

    $d_m_j_t = explode("/", $date);
    if (!isset($d_m_j_t[0])) return "geen datumtijd";
    if (!isset($d_m_j_t[1])) return "geen datumtijd";
    if (!isset($d_m_j_t[2])) return "geen datumtijd";
    if (!isset($d_m_j_t[3])) return "geen datumtijd";
    if (!isset($d_m_j_t[4])) return "geen datumtijd";
    if (!isset($d_m_j_t[5])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[0])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[1])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[2])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[3])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[4])) return "geen datumtijd";
    if (!is_numeric($d_m_j_t[5])) return "geen datumtijd";

    if (controledatum($d_m_j_t[0], $d_m_j_t[1], $d_m_j_t[2]))
        if (checktime($d_m_j_t[3], $d_m_j_t[4], $d_m_j_t[5]))
            return $d_m_j_t[2] . "-" . $d_m_j_t[1] . "-" . $d_m_j_t[0] . " " . $d_m_j_t[3] . ":" . $d_m_j_t[4] . ":" . $d_m_j_t[5];
        else
            return "geen datumtijd";
    else
        return "geen datumtijd";
}

?>
<?php
function controledatum($idag, $imaand, $ijaar)
{
    //echo $idag.$imaand.$ijaar;
    If (!checkdate($imaand, $idag, $ijaar)) {
        return false;
    } else {
        return true;
    }
}

?>
<?php
function checktime($hour, $minute, $second)
{
    if ($hour > -1 && $hour < 24 && $minute > -1 && $minute < 60 && $second > -1 && $second < 60) {
        return true;
    }
}

?>