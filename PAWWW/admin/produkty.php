<?php
require_once '../cfg.php';

function PokazProdukty() {
    global $link;

    echo '<a id="product-list"></a>'; // Kotwica dla listy produktów
    $stmt = $link->prepare("SELECT * FROM produkty LIMIT 100");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<table class="product-table">';
        echo '<thead>';
        echo '<tr><th>ID</th><th>Tytuł</th><th>Opis</th><th>Data utworzenia</th><th>Data modyfikacji</th><th>Data wygaśnięcia</th><th>Cena netto</th><th>Podatek VAT</th><th>Ilość w magazynie</th><th>Status dostępności</th><th>Kategoria</th><th>Zdjęcie</th><th>Akcje</th></tr>';
        echo '</thead>';
        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr id="product-' . $row['id'] . '">'; // Dodanie kotwicy do każdego wiersza
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . htmlspecialchars($row['tytul']) . '</td>';
            echo '<td>' . htmlspecialchars($row['opis']) . '</td>';
            echo '<td>' . htmlspecialchars($row['data_utworzenia']) . '</td>';
            echo '<td>' . ($row['data_modyfikacji'] ?? 'Brak') . '</td>';
            echo '<td>' . ($row['data_wygasniecia'] ?? 'Brak') . '</td>';
            echo '<td>' . $row['cena_netto'] . ' zł</td>';
            echo '<td>' . $row['podatek_vat'] . '%</td>';
            echo '<td>' . $row['ilosc_magazyn'] . '</td>';
            $status = ($row['status_dostepnosci'] && $row['ilosc_magazyn'] > 0 && strtotime($row['data_wygasniecia']) > time()) ? 'Dostępny' : 'Niedostępny';
            echo '<td class="product-status ' . ($status === 'Dostępny' ? 'available' : 'unavailable') . '">' . $status . '</td>';
            echo '<td>' . htmlspecialchars($row['kategoria_id']) . '</td>';
            echo '<td><img src="' . htmlspecialchars($row['zdjecie']) . '" alt="Zdjęcie" class="product-image"></td>';
            echo '<td>
                <form method="post" action="#product-list"> <!-- Przenoszenie na górę tabeli -->
                    <input type="hidden" name="id" value="' . $row['id'] . '">
                    <button type="submit" name="edytuj_produkt" class="btn-edit">Edytuj</button>
                </form>
                <form method="post" action="#product-list"> <!-- Przenoszenie na górę tabeli -->
                    <input type="hidden" name="id" value="' . $row['id'] . '">
                    <button type="submit" name="usun_produkt" class="btn-delete" onclick="return confirm(\'Czy na pewno chcesz usunąć ten produkt?\')">Usuń</button>
                </form>
            </td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p class="no-products">Brak produktów w bazie danych.</p>';
    }
    // Wywołanie funkcji EdytujProdukt pod listą
    if (isset($_POST['edytuj_produkt'])) {
        EdytujProdukt($_POST['id']);
    }
}
    


function DodajProdukt() {
    echo '<div class="add-product-container">';
    echo '<form method="post" action="" enctype="multipart/form-data">
        <label for="tytul">Tytuł:</label><br>
        <input type="text" id="tytul" name="tytul" class="input-field" required><br>

        <label for="opis">Opis:</label><br>
        <textarea id="opis" name="opis" class="textarea-field" required></textarea><br>

        <label for="data_wygasniecia">Data wygaśnięcia:</label><br>
        <input type="date" id="data_wygasniecia" name="data_wygasniecia" class="input-field"><br>

        <label for="cena">Cena netto:</label><br>
        <input type="number" id="cena" name="cena" class="input-field" step="0.01" required><br>

        <label for="vat">VAT (%):</label><br>
        <input type="number" id="vat" name="vat" class="input-field" step="0.01" value="23.00" readonly><br>

        <label for="ilosc">Ilość w magazynie:</label><br>
        <input type="number" id="ilosc" name="ilosc" class="input-field" required><br>

        <label for="status">Status dostępności:</label>
        <select id="status" name="status" class="input-field">
            <option value="1">Dostępny</option>
            <option value="0">Niedostępny</option>
        </select>

        <label for="kategoria">ID Kategorii:</label>
        <input type="number" id="kategoria" name="kategoria" class="input-field" required><br>

        <label for="zdjecie">Zdjęcie:</label><br>
        <input type="file" id="zdjecie" name="zdjecie" class="file-input" accept="image/*" required><br>

        <input type="submit" name="zapisz_produkt" value="Dodaj Produkt" class="submit-button" action="#product-list">
    </form>';
    echo '</div>';
}

function EdytujProdukt($id) {
    global $link;

    $stmt = $link->prepare("SELECT * FROM produkty WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        echo '<div class="edit-product-container">';
        echo '<h1 class="form-title">Edytuj Produkt</h1>';
        echo '<form method="post" action="" class="edit-product-container edit-product-form" enctype="multipart/form-data">';
        echo '<input type="hidden" name="id" value="' . htmlspecialchars($id) . '">';

        echo '<label for="tytul" class="form-label">Tytuł:</label>';
        echo '<input type="text" id="tytul" name="tytul" class="form-input" value="' . htmlspecialchars($product['tytul']) . '" required>';

        echo '<label for="opis" class="form-label">Opis:</label>';
        echo '<textarea id="opis" name="opis" class="form-textarea" required>' . htmlspecialchars($product['opis']) . '</textarea>';

        echo '<label for="data_wygasniecia" class="form-label">Data wygaśnięcia:</label>';
        echo '<input type="date" id="data_wygasniecia" name="data_wygasniecia" class="form-input" value="' . htmlspecialchars($product['data_wygasniecia']) . '">';

        echo '<label for="cena" class="form-label">Cena netto:</label>';
        echo '<input type="number" id="cena" name="cena" class="form-input" step="0.01" value="' . htmlspecialchars($product['cena_netto']) . '" required>';

        echo '<label for="vat" class="form-label">VAT (%):</label>';
        echo '<input type="number" id="vat" name="vat" class="form-input" step="0.01" value="' . htmlspecialchars($product['podatek_vat']) . '" required>';

        echo '<label for="ilosc" class="form-label">Ilość w magazynie:</label>';
        echo '<input type="number" id="ilosc" name="ilosc" class="form-input" value="' . htmlspecialchars($product['ilosc_magazyn']) . '" required>';


        echo '<label for="status_dostepnosci" class="form-label">Status dostępności:</label>';
        echo '<select id="status_dostepnosci" name="status_dostepnosci" class="form-select">
        <option value="1"' . ($product['status_dostepnosci'] == 1 ? ' selected' : '') . '>Dostępny</option>
        <option value="0"' . ($product['status_dostepnosci'] == 0 ? ' selected' : '') . '>Niedostępny</option>
        </select>';

        echo '<label for="kategoria" class="form-label">ID Kategorii:</label>';
        echo '<input type="number" id="kategoria" name="kategoria" class="form-input" value="' . htmlspecialchars($product['kategoria_id']) . '" required>';

        echo '<label for="zdjecie" class="form-label">Zdjęcie:</label>';
        echo '<input type="file" id="zdjecie" name="zdjecie" class="form-input" accept="image/*">';
        echo '<input type="hidden" name="zdjecie_old" value="' . htmlspecialchars($product['zdjecie']) . '">';
        if (!empty($product['zdjecie'])) {
            echo '<p>Aktualne zdjęcie: <img src="' . htmlspecialchars($product['zdjecie']) . '" alt="Zdjęcie produktu" class="product-image"></p>';
        }

        echo '<button type="submit" name="zapisz_edycje_produktu" class="form-button">Zapisz zmiany</button>';
        echo '</form>';
        echo '</div>';
    } else {
        echo '<p class="error-message">Nie znaleziono produktu o podanym ID.</p>';
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['zapisz_produkt'])) {
        global $link;

        $tytul = $_POST['tytul'] ?? '';
        $opis = $_POST['opis'] ?? '';
        $cena = $_POST['cena'] ?? 0;
        $vat = 23.00; // Ustawienie VAT na stałą wartość
        $ilosc = $_POST['ilosc'] ?? 0;
        $data_wygasniecia = $_POST['data_wygasniecia'] ?? null; // Pobranie daty wygaśnięcia
        $status = $_POST['status'] ?? 0;
        $kategoria = $_POST['kategoria'] ?? 0;

        $zdjecie = null;
        if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
            $zdjecie = 'uploads/' . basename($_FILES['zdjecie']['name']);
            move_uploaded_file($_FILES['zdjecie']['tmp_name'], $zdjecie);
        }

        $stmt = $link->prepare("INSERT INTO produkty (tytul, opis, data_utworzenia, data_wygasniecia, cena_netto, podatek_vat, ilosc_magazyn, status_dostepnosci, kategoria_id, zdjecie) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddiiss", $tytul, $opis, $data_wygasniecia, $cena, $vat, $ilosc, $status, $kategoria, $zdjecie);

        if ($stmt->execute()) {
        } else {
            echo '<p>Błąd podczas dodawania produktu: ' . $stmt->error . '</p>';
        }

        $stmt->close();
    }elseif (isset($_POST['zapisz_edycje_produktu'])) {
        $id = $_POST['id'];
        $tytul = $_POST['tytul'] ?? '';
        $opis = $_POST['opis'] ?? '';
        $data_wygasniecia = $_POST['data_wygasniecia'] ?? null;
        $cena = $_POST['cena'] ?? 0.00;
        $vat = $_POST['vat'] ?? 23.00;
        $ilosc = $_POST['ilosc'] ?? 0;
        $status = $_POST['status_dostepnosci'] ?? 0; // Poprawiona nazwa pola
        $kategoria = $_POST['kategoria'] ?? 0;
    
        // Obsługa zdjęcia
        $zdjecie = $_POST['zdjecie_old'] ?? '';
        if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
            $zdjecie = 'uploads/' . basename($_FILES['zdjecie']['name']);
            move_uploaded_file($_FILES['zdjecie']['tmp_name'], $zdjecie);
        }
    
        // Aktualizacja danych w bazie
        $stmt = $link->prepare("UPDATE produkty SET tytul = ?, opis = ?, data_modyfikacji = NOW(), data_wygasniecia = ?, cena_netto = ?, podatek_vat = ?, ilosc_magazyn = ?, status_dostepnosci = ?, kategoria_id = ?, zdjecie = ? WHERE id = ?");
        $stmt->bind_param("sssddiissi", $tytul, $opis, $data_wygasniecia, $cena, $vat, $ilosc, $status, $kategoria, $zdjecie, $id);
    
        if ($stmt->execute()) {

        } else {
            echo '<p>Błąd podczas aktualizacji produktu: ' . $stmt->error . '</p>';
        }
        $stmt->close();
    }elseif (isset($_POST['usun_produkt'])) {
        $id = $_POST['id'] ?? 0;
        if ($id) {
            global $link;
            $stmt = $link->prepare("DELETE FROM produkty WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {

            } else {
                echo '<p>Błąd podczas usuwania produktu: ' . $stmt->error . '</p>';
            }
            $stmt->close();
        }
    }
}
?>