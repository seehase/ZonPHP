<?php
include_once "../inc/init.php";
include_once "../inc/sessionstart.php";

$_SESSION = array();
session_destroy();
header('location:../index.php');
