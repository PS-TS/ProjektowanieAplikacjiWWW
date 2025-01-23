<?php
require_once 'cfg.php';
require_once 'koszyk.php'; // Dodanie funkcji addToCart

// Obsługa dodawania produktu do koszyka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj'])) {
    $id_produktu = intval($_POST['id_produktu']);
    $ilosc = intval($_POST['ilosc']);

    // Sprawdzanie, czy użytkownik jest zalogowany
    if (!isset($_SESSION['logged_in_user']) || !isset($_SESSION['logged_in_user']['id'])) {
        echo "<p class='message error'>Musisz być zalogowany, aby dodać produkt do koszyka.</p>";
    } else {
        $id_uzytkownika = intval($_SESSION['logged_in_user']['id']);
        $data_dodania = date('Y-m-d H:i:s'); // Aktualna data

        if ($id_produktu > 0 && $ilosc > 0) {
            // Sprawdzanie, czy produkt już istnieje w koszyku
            $query_check = "SELECT ilosc FROM koszyk WHERE id_uzytkownika = ? AND id_produktu = ?";
            $stmt_check = $link->prepare($query_check);
            $stmt_check->bind_param("ii", $id_uzytkownika, $id_produktu);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Produkt istnieje - aktualizacja ilości i daty
                $row = $result_check->fetch_assoc();
                $nowa_ilosc = $row['ilosc'] + $ilosc;

                $query_update = "UPDATE koszyk SET ilosc = ?, data_dodania = ? WHERE id_uzytkownika = ? AND id_produktu = ?";
                $stmt_update = $link->prepare($query_update);
                $stmt_update->bind_param("isii", $nowa_ilosc, $data_dodania, $id_uzytkownika, $id_produktu);

                if ($stmt_update->execute()) {
                    echo "<p class='message success'>Zaktualizowano ilość produktu w koszyku!</p>";
                } else {
                    echo "<p class='message error'>Błąd podczas aktualizacji koszyka: " . $link->error . "</p>";
                }
            } else {
                // Produkt nie istnieje - dodanie nowego rekordu
                $query_insert = "INSERT INTO koszyk (id_uzytkownika, id_produktu, ilosc, data_dodania) VALUES (?, ?, ?, ?)";
                $stmt_insert = $link->prepare($query_insert);
                $stmt_insert->bind_param("iiis", $id_uzytkownika, $id_produktu, $ilosc, $data_dodania);

                if ($stmt_insert->execute()) {
                    echo "<p class='message success'>Produkt został dodany do koszyka!</p>";
                } else {
                    echo "<p class='message error'>Błąd podczas dodawania do koszyka: " . $link->error . "</p>";
                }
            }
        } else {
            echo "<p class='message error'>Niepoprawne dane przesłane z formularza.</p>";
        }
    }
}

// Funkcja do wyświetlania produktów
function PokazProdukty() {
    global $link;
    $selected_category = isset($_GET['kategoria']) ? $_GET['kategoria'] : null;

    $query = "SELECT p.id, p.tytul, p.opis, 
                     (p.cena_netto + (p.cena_netto * p.podatek_vat / 100)) AS cena_brutto,
                     p.zdjecie, k.nazwa AS kategoria
              FROM produkty p
              JOIN kategorie k ON p.kategoria_id = k.id
              WHERE p.status_dostepnosci = 1";

    if ($selected_category) {
        $query .= " AND k.nazwa = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $selected_category);
    } else {
        $stmt = $link->prepare($query);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Sprawdzenie, czy żądanie pochodzi z AJAX-a
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if (!$isAjax) {
        // Renderowanie formularza tylko dla pełnych żądań
        echo '<div class="container">';
        echo '<h1 class="header">Lista Produktów</h1>';
        echo '<form method="GET" action="index.php?idp=produkty" id="filterForm">
                <label for="kategoria">Wybierz kategorię:</label>
                <select name="kategoria" id="kategoria" class="form-control">
                    <option value="">Wszystkie</option>'; // Opcja "Wszystkie"

        $category_query = "SELECT nazwa FROM kategorie WHERE nazwa NOT IN ('Kategorie', 'Filmy', 'Seriale')";
        $category_result = mysqli_query($link, $category_query);

        while ($category = mysqli_fetch_assoc($category_result)) {
            $selected = ($category['nazwa'] === $selected_category) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($category['nazwa']) . '" ' . $selected . '>' . htmlspecialchars($category['nazwa']) . '</option>';
        }

        echo '</select>
                <button type="submit" class="btn">Filtruj</button>
              </form>';
    }

    // Wyświetlanie tabeli produktów
    if (!$isAjax) {
        echo '<div id="productsTable">';
    }
    if ($result->num_rows > 0) {
        echo '<table class="film-table">';
        echo '<tr><th>Tytuł</th><th>Opis</th><th>Cena brutto</th><th>Zdjęcie</th><th>Akcje</th></tr>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['tytul']) . '</td>';
            echo '<td>' . htmlspecialchars($row['opis']) . '</td>';
            echo '<td>' . number_format($row['cena_brutto'], 2) . ' zł</td>';

            if (!empty($row['zdjecie'])) {
                echo '<td><img src="admin/' . htmlspecialchars($row['zdjecie']) . '" alt="Zdjęcie"></td>';
            } else {
                echo '<td>Brak zdjęcia</td>';
            }

            echo '<td>
                    <form method="post" action="">
                        <input type="hidden" name="id_produktu" value="' . $row['id'] . '">
                        <label for="ilosc_' . $row['id'] . '">Ilość:</label>
                        <input type="number" id="ilosc_' . $row['id'] . '" name="ilosc" value="1" min="1" class="form-control">
                        <button type="submit" name="dodaj" class="btn">Dodaj do koszyka</button>
                    </form>
                  </td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p class="message">Brak produktów spełniających kryteria.</p>';
    }
    if (!$isAjax) {
        echo '</div>';
    }
}
?>
