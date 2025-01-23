<?php

session_start();

$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$baza = 'moja_strona';
$login = 'admin';
$pass = 'admin';

$link = mysqli_connect($dbhost, $dbuser, $dbpass, $baza);
if (!$link) {
    die('Przerwane połączenie: ' . mysqli_connect_error());
}
?>