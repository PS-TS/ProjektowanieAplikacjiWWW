<?php
require_once '../cfg.php';

// Funkcja generująca drzewo kategorii
function generujDrzewoKategorii($matka = 0, $poziom = 0, $maksymalnaGlebokosc = 10) {
    global $link;

    if ($poziom >= $maksymalnaGlebokosc) {
        return;
    }

    $stmt = mysqli_prepare($link, "SELECT id, nazwa FROM kategorie WHERE matka = ? LIMIT 100");
    mysqli_stmt_bind_param($stmt, 'i', $matka);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result->num_rows > 0) {
        echo '<ul class="category-list">';
        echo '<a id="category-list"></a>';
        while ($row = $result->fetch_assoc()) {
            echo '<li class="category-item">' .
                 '<span class="category-name">' . htmlspecialchars($row['nazwa']) . '</span> ' .
                 '<span class="category-id">ID: ' . htmlspecialchars($row['id']) . '</span>' .
                 ' <form class="category-form" style="display:inline;" method="post" action="#category-list">' . // Adjusted action to target the category-list anchor
                 '     <input type="hidden" name="identyfikator" value="' . $row['id'] . '">' .
                 '     <button type="submit" name="edytuj_kategorie" class="btn-edit">Edytuj</button>' .
                 '     <button type="submit" name="usun_kategorie" class="btn-delete" onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\')">Usuń</button>' .
                 ' </form>';

            generujDrzewoKategorii($row['id'], $poziom + 1);
            echo '</li>';
        }
        echo '</ul>';
    }
}

// Funkcja dodawania nowej kategorii
function DodajNowaKategorie() {
    echo '<div class="add-category-container">
        <form method="post" action="" class="add-category-form">
            <label for="nazwa" class="form-label">Nazwa kategorii:</label><br>
            <input type="text" id="nazwa" name="nazwa" class="form-input" required><br>
            
            <label for="matka" class="form-label">Kategoria nadrzędna (ID):</label><br>
            <input type="number" id="matka" name="matka" class="form-input" value="0">
            
            <button type="submit" action="#category-list" name="zapisz_dodaj_kategorie" class="form-button">Zapisz nową kategorię</button>
        </form>
    </div>';
}

// Funkcja wyświetlania formularza edycji kategorii
function PokazFormularzEdycjiKategorii($id) {
    global $link;

    $stmt = $link->prepare("SELECT nazwa, matka FROM kategorie WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($nazwa, $matka);
    $stmt->fetch();
    $stmt->close();

    echo '<div class="edit-category-container">';
    echo '<h1 class="form-title">Edytuj Kategorię</h1>
    <form method="post" action="" class="edit-category-form">
        <input type="hidden" name="identyfikator" value="' . $id . '">
        
        <label for="nazwa" class="form-label">Nazwa kategorii:</label>
        <input type="text" id="nazwa" name="nazwa" value="' . htmlspecialchars($nazwa) . '" class="form-input" required>
        
        <label for="matka" class="form-label">Kategoria nadrzędna (ID):</label>
        <input type="number" id="matka" name="matka" value="' . htmlspecialchars($matka) . '" class="form-input">
        
        <button type="submit" action="#category-list" name="zapisz_edycje_kategorii" class="form-button">Zapisz zmiany</button>
    </form>';
    echo '</div>';
}

// Funkcja usuwania kategorii
function UsunKategorie($id) {
    global $link;

    $stmtCheck = $link->prepare("SELECT COUNT(*) FROM kategorie WHERE id = ?");
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $stmtCheck->bind_result($exists);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($exists === 0) {
        echo "<p style='color: red;'>Błąd: Kategoria o ID $id nie istnieje.</p>";
        return;
    }

    $stmt = $link->prepare("DELETE FROM kategorie WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo '<p>Kategoria została usunięta.</p>';
    } else {
        echo "<p>Błąd podczas usuwania kategorii: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Obsługa żądań POST i wyświetlanie kategorii w odpowiednim miejscu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['zapisz_dodaj_kategorie'])) {
        global $link;

        $nazwa = $_POST['nazwa'] ?? '';
        $matka = intval($_POST['matka'] ?? 0);

        $stmt = $link->prepare("INSERT INTO kategorie (nazwa, matka) VALUES (?, ?)");
        $stmt->bind_param("si", $nazwa, $matka);

        if ($stmt->execute()) {
            echo '<p>Nowa kategoria została dodana.</p>';
        } else {
            echo '<p>Błąd podczas dodawania kategorii: ' . $stmt->error . '</p>';
        }
    } elseif (isset($_POST['zapisz_edycje_kategorii'])) {
        global $link;

        $id = intval($_POST['identyfikator']);
        $nazwa = $_POST['nazwa'] ?? '';
        $matka = intval($_POST['matka'] ?? 0);

        $stmt = $link->prepare("UPDATE kategorie SET nazwa = ?, matka = ? WHERE id = ?");
        $stmt->bind_param("sii", $nazwa, $matka, $id);

        if ($stmt->execute()) {
            echo '<p>Kategoria została zaktualizowana.</p>';
        } else {
            echo '<p>Błąd podczas aktualizacji kategorii: ' . $stmt->error . '</p>';
        }
    } elseif (isset($_POST['usun_kategorie'])) {
        $id = intval($_POST['identyfikator']);
        UsunKategorie($id);
    } 
}
?>
