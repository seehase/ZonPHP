<?php
switch ($param['sInvullen_gegevens']) {
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


if (!isset($param['next_mail_threshold'])) {
    $sql = "SELECT Waarde FROM " . $table_prefix . "_parameters where variable = \"next_mail_threshold\" limit 1";
    $result = mysqli_query($con, $sql) or die(header('location:opstart_installatie.php?fout=table'));
    if (mysqli_num_rows($result) === 0) {
        $sql = "INSERT INTO " . $table_prefix . "_parameters (variable, waarde) VALUES	('next_mail_threshold', 0)";
        mysqli_query($con, $sql) or die ('next_mail_threshold:' . mysqli_error($con));
        $param['next_mail_threshold'] = 0;
    } else {
        $row = mysqli_fetch_array($result);
        $param['next_mail_threshold'] = $row['Waarde'];
    }
    $_SESSION['lastupdate'] = 0;
};

// sent mail if total kw is reached
$totaal = round(0.5 + $total_sum_for_all_years / $param['mailinterval']) * $param['mailinterval'];
if ($total_sum_for_all_years >= $param['next_mail_threshold']) {
    //define the receiver of the email
    $to = $param['email'];
    //define the subject of the email
    $subject = $param['sNaamVoorOpWebsite'] . $txt["mail1"] . number_format($total_sum_for_all_years, 0, ',', '.') . $txt["mail2"];
    //define the message to be sent. Each line should be separated with \n
    $message = $txt["mail3"] . "\n\n" . $param['sURL_link'] . "\n\n" . date("d/m H:i", time()) . " = " .
        "kWh \n" . $txt["totaal"] . " = " . number_format($total_sum_for_all_years, 1, ',', '.') . "kWh \n";
    //define the headers we want passed. Note that they are separated with \r\n
    $headers = "From: " . $param['email'] . "\r\nReply-To: " . $param['email'];
    //send the email
    $mail_sent = @mail($to, $subject, $message, $headers);
    //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed"
    // echo $mail_sent ? "Mail sent" : "Mail failed";

    if ($mail_sent) {
        error_log('---------> Mail sucessfully sent ' . $message);
    } else {
        error_log('######### Mail faild' . $message);
    }

    $param['next_mail_threshold'] = $totaal + $param['mailinterval'];
    $sql = "update " . $table_prefix . "_parameters set waarde = " . $param['next_mail_threshold'] . " where variable = 'next_mail_threshold'";
    mysqli_query($con, $sql) or die ('next_mail_threshold:' . mysqli_error($con));
}


?>
