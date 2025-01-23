<?php
function ListaPodstron() {
    global $link;

    // Pobranie danych z dodatkowych kolumn
    $query = "SELECT id, page_title, page_content, status FROM page_list LIMIT 100";
    $result = mysqli_query($link, $query);

    if (!$result || mysqli_num_rows($result) == 0) {
        echo '<p class="no-pages">Brak podstron do wyświetlenia.</p>';
        return;
    }

    echo '<table class="table-podstrony">';
    echo '<tr class="table-header">
            <th class="table-header-textarea">ID</th>
            <th class="table-header-textarea">Tytuł</th>
            <th class="table-header-textarea">Treść</th>
            <th class="table-header-textarea">Status</th>
            <th class="table-header-textarea">Akcje</th>
          </tr>';

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr class="table-row">';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['page_title']) . '</td>';

        // Dodanie pola textarea z możliwością rozciągania
        echo '<td>
        <textarea readonly class="resizable-content">' . htmlspecialchars($row['page_content']) . '</textarea>
            </td>';

        // Wyświetlenie statusu dostępności
        echo '<td>' . ($row['status'] == 1 ? 'Dostępna' : 'Niedostępna') . '</td>';

        // Akcje dla podstrony
        echo '<td>
                <form method="post" action="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">
                    <input type="hidden" name="identyfikator" value="' . $row['id'] . '">
                    <input type="submit" name="usun_podstrone" value="Usuń" class="btn-delete">
                    <input type="submit" name="edytuj_podstrone" value="Edytuj" class="btn-edit">
                </form>
              </td>';

        echo '</tr>';
    }
        
    echo '</table>';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edytuj_podstrone'])) {
        $id = intval($_POST['identyfikator']);
        echo '<section class="admin-section">';
        PokazFormularzEdycjiPodstrony($id);
        echo '</section>';
    }
}

function DodajNowaPodstrone() {
    echo '<div class="add-page-container">';
    echo '<form method="post" action="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '", class="add-page-form">
        <label for="tytul", class="form-label">Tytuł:</label><br>
        <input type="text" id="page-title" name="page_title" class="form-input"><br>

        <label for="tresc" class="form-label">Treść:</label><br>
        <textarea id="tresc" name="page_content" rows="10" cols="50" required class="form-textarea"></textarea><br><br>

        <label for="aktywna">Aktywna:</label>
        <input type="checkbox" id="aktywna" name="status"><br><br>

        <input type="submit" name="zapisz_dodaj_podstrone" class="form-button" value="Stwórz podstronę">
    </form>';
    echo '</div>';
}

function PokazFormularzEdycjiPodstrony($id) {
    global $link;

    $stmt = $link->prepare("SELECT page_title, page_content, status FROM page_list WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        echo '<div class="edit-page-container">
            <h1 class="form-title">Edytuj Podstronę</h1>
            <form method="post" action="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '" class="edit-page-form">
                <input type="hidden" name="identyfikator" value="' . $id . '">
                
                <label for="tytul" class="form-label">Tytuł:</label>
                <input type="text" id="tytul" name="page_title" value="' . htmlspecialchars($row['page_title']) . '" class="form-input" required>
                
                <label for="tresc" class="form-label">Treść:</label>
                <textarea id="tresc" name="page_content" rows="10" class="form-textarea" required>' . htmlspecialchars($row['page_content']) . '</textarea>
                
                <label for="aktywna" class="form-label">Aktywna:</label>
                <input type="checkbox" id="aktywna" name="status" class="form-checkbox"' . ($row['status'] ? ' checked' : '') . '>
                
                <button type="submit" name="zapisz_podstrone" class="form-button">Zapisz zmiany</button>
            </form>
        </div>';
    } else {
        echo '<p class="error-message">Nie znaleziono podstrony do edycji.</p>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['usun_podstrone'])) {
        $id = $_POST['identyfikator'] ?? 0;
        if ($id) {
            global $link;
            $stmt = $link->prepare("DELETE FROM page_list WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                
            } else {
                echo '<p class="message-error">Błąd podczas usuwania podstrony: ' . $stmt->error . '</p>';
            }
        } else {
            echo '<p class="message-error">Nieprawidłowy identyfikator do usunięcia.</p>';
        }
    } elseif (isset($_POST['zapisz_dodaj_podstrone'])) {
        global $link;

        $nowyTytul = $_POST['page_title'] ?? '';
        $nowaTresc = $_POST['page_content'] ?? '';
        $nowaAktywna = isset($_POST['status']) ? 1 : 0;

        $stmt = $link->prepare("INSERT INTO page_list (page_title, page_content, status) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nowyTytul, $nowaTresc, $nowaAktywna);

        if ($stmt->execute()) {
            header('Location: admin_panel.php'); // Tak jak było w oryginale
            exit();
        } else {
            echo '<p class="message-error">Błąd podczas dodawania podstrony: ' . $stmt->error . '</p>';
        }
    }elseif (isset($_POST['zapisz_podstrone'])) {
        $id = $_POST['identyfikator'] ?? 0;
        $nowyTytul = $_POST['page_title'] ?? '';
        $nowaTresc = $_POST['page_content'] ?? '';
        $nowaAktywna = isset($_POST['status']) ? 1 : 0;

        global $link;

        $stmt = $link->prepare("UPDATE page_list SET page_title = ?, page_content = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssii", $nowyTytul, $nowaTresc, $nowaAktywna, $id);

        if ($stmt->execute()) {
            header('Location: admin_panel.php'); // Tak jak było w oryginale
            exit();
        } else {
            echo '<p class="message-error">Błąd podczas aktualizacji podstrony: ' . $stmt->error . '</p>';
        }
    }
}


?>
