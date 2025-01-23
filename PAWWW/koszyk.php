<?php
require_once 'cfg.php';
session_start();

// Obsługa usuwania produktów z koszyka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usun'])) {
    $id_produktu = intval($_POST['id_produktu']);
    $ilosc = intval($_POST['ilosc']);

    if (!isset($_SESSION['logged_in_user']) || !isset($_SESSION['logged_in_user']['id'])) {
        echo "<div class='error-message'>Musisz być zalogowany, aby zobaczyć koszyk.</div>";
    } else {
        $id_uzytkownika = intval($_SESSION['logged_in_user']['id']);
        $query_check = "SELECT ilosc FROM koszyk WHERE id_uzytkownika = ? AND id_produktu = ?";
        $stmt_check = $link->prepare($query_check);
        $stmt_check->bind_param("ii", $id_uzytkownika, $id_produktu);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $row = $result_check->fetch_assoc();
            $aktualna_ilosc = $row['ilosc'];

            if ($ilosc >= $aktualna_ilosc) {
                $query_delete = "DELETE FROM koszyk WHERE id_uzytkownika = ? AND id_produktu = ?";
                $stmt_delete = $link->prepare($query_delete);
                $stmt_delete->bind_param("ii", $id_uzytkownika, $id_produktu);
                $stmt_delete->execute();
            } else {
                $nowa_ilosc = $aktualna_ilosc - $ilosc;
                $query_update = "UPDATE koszyk SET ilosc = ? WHERE id_uzytkownika = ? AND id_produktu = ?";
                $stmt_update = $link->prepare($query_update);
                $stmt_update->bind_param("iii", $nowa_ilosc, $id_uzytkownika, $id_produktu);
                $stmt_update->execute();
            }
        }
    }
}

// Obsługa przycisku "Kup"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kup'])) {
    if (!isset($_SESSION['logged_in_user']) || !isset($_SESSION['logged_in_user']['id'])) {
        echo "<div class='error-message'>Musisz być zalogowany, aby zobaczyć koszyk.</div>";
    } else {
        $id_uzytkownika = intval($_SESSION['logged_in_user']['id']);
        $query_delete_all = "DELETE FROM koszyk WHERE id_uzytkownika = ?";
        $stmt_delete_all = $link->prepare($query_delete_all);
        $stmt_delete_all->bind_param("i", $id_uzytkownika);
        $stmt_delete_all->execute();
        echo "<div class='success-message'>Zakupiono produkty!</div>";
    }
}

// Funkcja do wyświetlania koszyka
function PokazKoszyk() {
    global $link;

    if (!isset($_SESSION['logged_in_user']) || !isset($_SESSION['logged_in_user']['id'])) {
        echo "<div class='error-message'>Musisz być zalogowany, aby zobaczyć koszyk.</div>";
        return;
    }

    $id_uzytkownika = intval($_SESSION['logged_in_user']['id']);

    $query = "
        SELECT 
            p.id,
            p.tytul, 
            p.opis, 
            (p.cena_netto + (p.cena_netto * p.podatek_vat / 100)) AS cena_jednostkowa,
            p.zdjecie, 
            k.ilosc
        FROM koszyk k
        JOIN produkty p ON k.id_produktu = p.id
        WHERE k.id_uzytkownika = ?
    ";

    $stmt = $link->prepare($query);
    $stmt->bind_param("i", $id_uzytkownika);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<h1 class="header">Twój Koszyk</h1>';
    
    $total_price = 0;

    if ($result->num_rows > 0) {
        echo '<table class="koszyk-table">';
        echo '<thead>
                <tr>
                    <th>Tytuł</th>
                    <th>Opis</th>
                    <th>Cena brutto</th>
                    <th>Zdjęcie</th>
                    <th>Ilość</th>
                    <th>Usuń</th>
                </tr>
              </thead>
              <tbody>';
        
        while ($row = $result->fetch_assoc()) {
            $cena_calkowita_produktu = $row['cena_jednostkowa'] * $row['ilosc'];
            $total_price += $cena_calkowita_produktu;

            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['tytul']) . '</td>';
            echo '<td>' . htmlspecialchars($row['opis']) . '</td>';
            echo '<td>' . number_format($cena_calkowita_produktu, 2) . ' zł</td>';
            echo '<td><img src="admin/' . htmlspecialchars($row['zdjecie']) . '" alt="Zdjęcie" class="product-image"></td>';
            echo '<td>' . htmlspecialchars($row['ilosc']) . '</td>';
            echo '<td>
                    <form method="post" action="">
                        <input type="hidden" name="id_produktu" value="' . $row['id'] . '">
                        <input type="number" name="ilosc" value="1" min="1" max="' . htmlspecialchars($row['ilosc']) . '" class="quantity-input">
                        <button type="submit" name="usun" class="btn-remove">Usuń</button>
                    </form>
                  </td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';

        echo '<p class="total-price">Całkowita cena: ' . number_format($total_price, 2) . ' zł</p>';

        echo '<form method="post" action="" class="form-buy">
                <button type="submit" name="kup" class="btn-buy">Kup</button>
              </form>';
    } else {
        echo '<div class="alert alert-danger">Twój koszyk jest pusty.</div>';
    }
}
?>
