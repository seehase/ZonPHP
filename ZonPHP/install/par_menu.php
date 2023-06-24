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
					<li><a href="par_edit.php">&raquo;&nbsp;' . getTxt("parameters") . '</a></li>
					<li><a href="par_referencevalues.php">&raquo;&nbsp;' . getTxt("parref") . '</a></li>					
					<li><a href="installatie_zonphp.php">&raquo;&nbsp;' . getTxt("insteltabel") . '</a></li>
					<li><a href="clear_tables.php">&raquo;&nbsp;' . getTxt("deletevalues") . '</a></li>
					<li><a href="delete.php">&raquo;&nbsp;' . getTxt("pardelete") . '</a></li>
					<li><a href="update.php">&raquo;&nbsp;' . getTxt("parupdate") . '</a></li>
					<hr>
					<li><a href="../inc/destroy.php">&raquo;&nbsp;' . getTxt("clearsession") . '</a></li>
					<li><a href="par_logout.php">&raquo;&nbsp;Logout</a></li>
					';
                } else if (isset($con) && $con) {
                    echo '<li><a href="par_welcome.php"><b>&raquo;&nbsp;Inloggen</b></a></li>';
                }
                ?>
            </ul>
            <?php
            //if (isset($con) && $con) {
            if (isset($_SESSION['passok']) && $_SESSION['passok'] == "passinorder") {
                echo " 
                <hr>   
                <a href='?taal=nl' TARGET='_self'><img src='../inc/image/nl.svg' alt='nl' border='0' width='16' height='12'></a>
                <a href='?taal=en' TARGET='_self'><img src='../inc/image/en.svg' alt='en' border='0' width='16' height='12'></a>
                <a href='?taal=fr' TARGET='_self'><img src='../inc/image/fr.svg' alt='fr' border='0' width='16' height='12'></a>
                <a href='?taal=de' TARGET='_self'><img src='../inc/image/de.svg' alt='de' border='0' width='16' height='12'></a> 
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
