<?php
include_once "../Parameters.php";
include_once "../inc/sessionstart.php";

unset($_SESSION['passok']);
unset($_SESSION['lastupdate']);

session_unset();
session_destroy();

header('location:../index.php')

?>