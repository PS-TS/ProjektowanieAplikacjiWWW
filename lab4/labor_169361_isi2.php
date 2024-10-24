<?php
$nr_indeksu = '1234567';
$nrGrupy = 'X';
echo ("Jan Kowalski ".$nr_indeksu." grupa ".$nrGrupy." <br /><br />");
echo ("Zastosowanie metody include() <br />");
echo ("include require_once - kopiuje istniejący gdzieś w innym pliku fragment kodu (zmienną), require once robi to tylko za pierwszym uruchomieniem<br />");
echo ("if else elseif switch - instrukcje do obsługi warunków programu<br />");
echo ("while for - pętle do wielkrotnego robienia tych samych czynności<br />");
echo ("GET POST SESSION - zmienne które strona może odebrać aby ustawić odpowiednią konfigurację");

/*
include('plik.php');
require_once('plik2.php');

if ($x > 10) {
    echo "X > 10";
} elseif ($x == 10) {
    echo "X = 10";
} else {
    echo "X < 10";
}

switch ($kolor) {
    case 'c':
        echo "Czerwony";
        break;
    case 'n':
        echo "Niebieski";
        break;
    default:
        echo "Inny";

$i = 0;
while ($i < 5) {
    echo $i;
    $i++;
}

for ($i = 0; $i < 5; $i++) {
    echo $i;
}

$name = $_GET['name'];
echo $name;

$password = $_POST['password'];
echo $password;

session_start();
$_SESSION['username'] = 'Nick';
echo "Session username: " . $_SESSION['username'];
session_destroy();
*/
?>
