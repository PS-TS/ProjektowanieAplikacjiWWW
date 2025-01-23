<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

include('cfg.php');
include('showpage.php'); //PokazPodstrone()
include('user.php'); // Wyloguj()

$idp = isset($_GET['idp']) ? $_GET['idp'] : 'glowna';

//Wylogowanie
if (isset($_GET['action']) && $_GET['action'] === 'wyloguj') {
    Wyloguj();
    header("Location: index.php");
    exit;
}

if ($idp == 'glowna') {
    $id = 1;
} elseif ($idp == 'kontakt') {
    $id = 3;
} elseif ($idp == 'sklep') {
    $id = 8;
} elseif ($idp == 'koszyk') {
    $id = 9;
} elseif ($idp == 'zaloguj') {
    $id = 11;
} elseif ($idp == 'chromatyczne') {
    $id = 21;
} elseif ($idp == 'filmy') {
    $id = 22;
} elseif ($idp == 'zywiolow') {
    $id = 23;
} elseif ($idp == 'klejnotow') {
    $id = 24;
} elseif ($idp == 'metaliczne') {
    $id = 25;
} elseif ($idp == 'reszta') {
    $id = 26;
} elseif ($idp == 'plac') {
    $id = 27;
} else {
    $id = 1;
}
$page_content = PokazPodstrone($id);

if (isset($_GET['id'])) {
    $fragment = htmlspecialchars($_GET['id']); // Sanityzacja
    header("Location: #" . $fragment); // Przekierowanie
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="Content-Language" content="pl" />
        <meta name="Author" content="Piotr S" />
        <link rel="stylesheet" href="Style/style.css">
        <script src="JS/skryptTlo.js"></script>
        <script src="JS/skryptZegar.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <title>(Moje hobby to )Smoki</title>
    </head>
    <body onload="startclock()">
        <div id="content"> 
            <div id="uppermenu">
                <div id="menubutton">
                    <a href="index.php?idp=glowna">Główna</a>
                </div>
                <div id="menubutton">
                    <a href="index.php?idp=filmy">Filmy</a>
                </div>
                <div id="menubutton">
                    <a href="index.php?idp=sklep">Sklep</a>
                </div>
                <div id="menubutton">
                    <a href="index.php?idp=kontakt">Kontakt</a>
                </div>
                <div id="menubutton">
                    <a href="index.php?idp=plac">Reszta</a>
                </div>
                <div id="menubutton">
                    <a href="index.php?idp=koszyk">Koszyk</a>
                </div>
                <div id="menubutton">
                    <?php
                    // Sprawdź czy zalogowany
                    $isLoggedIn = isset($_SESSION['logged_in_user']) && isset($_SESSION['logged_in_user']['id']);
                    if ($isLoggedIn) {
                        echo '<a href="index.php?action=wyloguj" class="zaloguj">Wyloguj</a>';
                    } else {
                        echo '<a href="index.php?idp=zaloguj" class="zaloguj">Zaloguj</a>';
                    }
                    ?>
                </div>
            </div>
            <?php echo $page_content; ?>
        </div>
    </body>
</html>