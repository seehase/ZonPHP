<?php

include_once  "Parameters.php";
include_once "inc/sessionstart.php";
include_once "inc/load_cache.php";

?>
<?php include_once "inc/header.php";	?>
<div id="menus"><?php include "menu.php";?></div>
<div id="container" >	
	<div id="bodytext">
		<div class="inside">	
			<h2 class="notopgap" align="center"> <?php echo $txt["dagboekmenu"] ?> </h2>
			<?php include "tagebuch.txt" ?>
		<p class="nobottomgap"></p>
		</div>
	</div>				
</div>

</div><!-- closing ".page-content" -->

</div><!-- closing ".container" -->

<?php include_once "inc/footer.php"; ?>

</body>
</html>
