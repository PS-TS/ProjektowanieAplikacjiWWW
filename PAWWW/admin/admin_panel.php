<?php
require_once '../cfg.php';
require_once 'podstrona.php';
require_once 'kategorie.php';
require_once 'produkty.php';
require_once 'users.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: admin.php');
    exit();
}

echo '<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny</title>
    <link rel="stylesheet" href="../Style/style.css">
</head>
<body class="body-admin"><div id="content">
    <div class="admin-panel">
        <header class="admin-header">
            <h1>Panel Administracyjny</h1>
            <a href="logout.php" class="btn-logout">Wyloguj</a>
        </header>

        <main class="admin-content">
            <section class="admin-section">
                <h2 class="section-title">Lista Podstron</h2>';
ListaPodstron();
echo '      </section>

            <section class="admin-section">
                <h2 class="section-title">Dodaj Nową Podstronę</h2>';
DodajNowaPodstrone();
echo '      </section>

<section class="admin-section">
    <h2 class="section-title">Lista Kategorii</h2>';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['zapisz_dodaj_podstrone'])) {
            generujDrzewoKategorii(); // Drzewo kategorii generowane po dodaniu podstrony
        } else if (isset($_POST['edytuj_kategorie'])) {
            $id = intval($_POST['identyfikator']);
            generujDrzewoKategorii(); // Wyświetlenie drzewa kategorii
            PokazFormularzEdycjiKategorii($id);
        } else {
            generujDrzewoKategorii(); // Domyślne wyświetlenie drzewa kategorii
        }
    } else {
        generujDrzewoKategorii(); // Domyślne wyświetlenie drzewa kategorii
    }
                

echo '      </section>

            <section class="admin-section">
                <h2 class="section-title">Dodaj Nową Kategorię</h2>';
DodajNowaKategorie();
echo '      </section>

            <section class="admin-section">
                <h2 class="section-title">Lista Produktów</h2>';
PokazProdukty();
echo '      </section>

            <section class="admin-section">
                <h2 class="section-title">Dodaj Produkt</h2>';
DodajProdukt();
echo '      </section>

            <section class="admin-section">
                <h2 class="section-title">Lista użytkowników</h2>';
WyswietlUzytkownikow();
echo '      </section>

        </main>
    </div>
</div></body>
</html>';
?>