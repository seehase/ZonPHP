<div id="container1">
    <div id="area"></div>
    <div id="afstand">
        <div class="inside">
            <ul id="nav">
                <?php
                if (isset($_SESSION['passok'])) {
                    echo '
                    <li><a href="installatie_zonphp.php">&raquo;&nbsp;' . $txt["insteltabel"] . '</a></li>
                    <li><a href="clear_tables.php">&raquo;&nbsp;' . $txt["deletevalues"] . '</a></li>
					<li><a href="par_edit.php">&raquo;&nbsp;' . $txt["parameters"] . '</a></li>
					<li><a href="par_referencevalues.php">&raquo;&nbsp;' . $txt["parref"] . '</a></li>
					<li><a href="par_euro.php">&raquo;&nbsp;' . $txt["pareuro"] . '</a></li>
					<li><a href="par_powerusage.php">&raquo;&nbsp;' . $txt["parverbruik"] . '</a></li>
					<li><a href="debug.php">&raquo;&nbsp;Debug</a></li> 
					<li><a href="../inc/destroy.php">&raquo;&nbsp;' . $txt["clearsession"] . '</a></li>
					<li><a href="delete.php">&raquo;&nbsp;' . $txt["pardelete"] . '</a></li>
					<li><a href="update.php">&raquo;&nbsp;' . $txt["parupdate"] . '</a></li>
					
					';
                } else if (isset($con) && $con) {
                    echo '<li><a href="par_welcome.php"><b>&raquo;&nbsp;Inloggen</b></a></li>';
                }
                ?>
                <li><a href="../index.php">&raquo;&nbsp;Index</a></li>

            </ul>

            <?php
            if (isset($con) && $con) {
                echo "    <a href='?taal=nl' TARGET='_self'><img src='../inc/image/nl.png' alt='nl' border='0' width='16'
                                                       height='11'></a>
                <a href='?taal=en' TARGET='_self'><img src='../inc/image/en.png' alt='en' border='0' width='16'
                                                       height='11'></a>
                <a href='?taal=fr' TARGET='_self'><img src='../inc/image/fr.png' alt='fr' border='0' width='16'
                                                       height='11'></a>
                <a href='?taal=de' TARGET='_self'><img src='../inc/image/de.png' alt='de' border='0' width='16'
                                                       height='11'></a> 
				<a href='?taal=at' TARGET='_self'><img src='../inc/image/at.png' alt='at' border='0' width='16'
                                                       height='11'></a> ";

            }
            ?>
            <br/>
            <hr>
            <?php echo $version ?>
            <br/>
            <hr>
            <center>
                <a href="http://validator.w3.org/check?uri=referer" TARGET="_blank"><img
                            style="border:0;width:88px;height:31px" src="http://www.w3.org/Icons/valid-html401"
                            alt="Valid HTML 4.01 Transitional" height="31" width="88"></a><br/>
            </center>
        </div>
    </div>
</div>
<br/>