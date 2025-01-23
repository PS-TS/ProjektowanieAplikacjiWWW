<?php
require_once '../cfg.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usun'])) {
    $id_uzytkownika = intval($_POST['id']);

    if ($id_uzytkownika > 0) {
        // Usunięcie użytkownika z bazy danych
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $id_uzytkownika);

        if ($stmt->execute()) {
            echo "<div class='success-message'>Użytkownik został usunięty.</div>";
        } else {
            echo "<div class='error-message'>Błąd podczas usuwania użytkownika: " . $link->error . "</div>";
        }
    } else {
        echo "<div class='error-message'>Nieprawidłowe ID użytkownika.</div>";
    }
}

function WyswietlUzytkownikow() {
    global $link;

    // Ankora do przewijania
    echo '<a id="user-list"></a>';

    // Pobranie wszystkich użytkowników z bazy danych
    $query = "SELECT id, email, created_at FROM users";
    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<table class="user-table">';
        echo '<tr>
                <th>ID</th>
                <th>Email</th>
                <th>Data utworzenia</th>
                <th>Akcje</th>
                <th>Zachęć</th>
              </tr>';
        while ($row = mysqli_fetch_assoc($result)) {
            // Sprawdzenie, czy użytkownik ma produkty w koszyku starsze niż 7 dni
            $id_uzytkownika = $row['id'];
            $query_old_products = "
                SELECT COUNT(*) AS count 
                FROM koszyk 
                WHERE id_uzytkownika = ? 
                AND DATEDIFF(NOW(), data_dodania) > 7";
            $stmt_old_products = $link->prepare($query_old_products);
            $stmt_old_products->bind_param("i", $id_uzytkownika);
            $stmt_old_products->execute();
            $result_old_products = $stmt_old_products->get_result();
            $old_products = $result_old_products->fetch_assoc()['count'];

            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
            echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
            echo '<td>
                    <form method="POST" action="#user-list">
                        <input type="hidden" name="id" value="' . $row['id'] . '">
                        <button type="submit" name="usun" class="btn-delete">Usuń</button>
                    </form>
                  </td>';
            
            // Przyciski "Zachęć"
            if ($old_products > 0) {
                echo '<td>
                        <form method="POST" action="#user-list">
                            <input type="hidden" name="id" value="' . $row['id'] . '">
                            <button type="submit" name="zachec" class="btn-encourage">Zachęć</button>
                        </form>
                      </td>';
            } else {
                echo '<td></td>';
            }

            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>Brak użytkowników w bazie danych.</p>';
    }
}

?>
