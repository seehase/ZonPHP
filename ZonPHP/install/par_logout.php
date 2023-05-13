<?php
include_once "../Parameters.php";
include_once "../inc/sessionstart.php";

unset($_SESSION['passok']);

header('location:../install/par_welcome.php')

?>