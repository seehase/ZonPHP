<div id="container1">
    <div id="area"></div>
    <div id="afstand">
        <div class="inside">
            <ul id="nav">
                <br/>
                <li><a href="../">&raquo;&nbsp;Index</a></li>
                <?php
                if (isset($_SESSION['passok']) && $_SESSION['passok'] == "passinorder") {
                    echo '
                    
                    <li><a href="./installatie_zonphp.php">&raquo;&nbsp;' . $txt["insteltabel"] . '</a></li>
                    <li><a href="clear_tables.php">&raquo;&nbsp;' . $txt["deletevalues"] . '</a></li>
					<li><a href="par_edit.php">&raquo;&nbsp;' . $txt["parameters"] . '</a></li>
					<li><a href="par_referencevalues.php">&raquo;&nbsp;' . $txt["parref"] . '</a></li>
					<li><a href="par_euro.php">&raquo;&nbsp;' . $txt["pareuro"] . '</a></li>
					<li><a href="debug.php">&raquo;&nbsp;Debug</a></li> 
					<li><a href="delete.php">&raquo;&nbsp;' . $txt["pardelete"] . '</a></li>
					<li><a href="update.php">&raquo;&nbsp;' . $txt["parupdate"] . '</a></li>
					<hr>
					<li><a href="../inc/destroy.php">&raquo;&nbsp;' . $txt["clearsession"] . '</a></li>
					<li><a href="par_logout.php">&raquo;&nbsp;Logout</a></li>
					';
                } else if (isset($con) && $con) {
                    echo '<li><a href="par_welcome.php"><b>&raquo;&nbsp;Inloggen</b></a></li>';
                }
                ?>
            </ul>

            <?php
            if (isset($con) && $con) {
                echo " <hr>   <a href='?taal=nl' TARGET='_self'><img src='../inc/image/nl.svg' alt='nl' border='0' width='16'
                                                       height='11'></a>
                <a href='?taal=en' TARGET='_self'><img src='../inc/image/en.svg' alt='en' border='0' width='16'
                                                       height='11'></a>
                <a href='?taal=fr' TARGET='_self'><img src='../inc/image/fr.svg' alt='fr' border='0' width='16'
                                                       height='11'></a>
                <a href='?taal=de' TARGET='_self'><img src='../inc/image/de.svg' alt='de' border='0' width='16'
                                                       height='11'></a> 
			  ";
            }
            ?>
            <hr>
            <?php echo $version ?>
            <br/>
            <hr>
        </div>
    </div>
</div>
<br/>
