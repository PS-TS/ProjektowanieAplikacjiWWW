<?php
require_once 'cfg.php'; // Połączenie z bazą danych

function PokazFormularze() {
    global $link;

    // Domyślny formularz
    $form_type = 'login';

    // Sprawdzenie, jaki formularz wybrał użytkownik
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
        $form_type = $_POST['form_type'];
    }

    // Obsługa rejestracji użytkownika
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
        $email = mysqli_real_escape_string($link, $_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Walidacja hasła
        if ($password !== $confirm_password) {
            echo "<div class='message error'>Hasła nie są zgodne. Spróbuj ponownie.</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Szyfrowanie hasła

            $query = "INSERT INTO users (email, password) VALUES ('$email', '$hashed_password')";
            if (mysqli_query($link, $query)) {
                echo "<div class='message success'>Rejestracja zakończona sukcesem! Możesz się teraz zalogować.</div>";
            } else {
                echo "<div class='message error'>Błąd podczas dodawania użytkownika: " . mysqli_error($link) . "</div>";
            }
        }
    }

    // Obsługa logowania użytkownika
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $email = mysqli_real_escape_string($link, $_POST['email']);
        $password = $_POST['password'];
    
        // Check if the email is in a valid format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<div class='error-message'>Złe dane! Wprowadź poprawny adres e-mail.</div>";
        } else {
            $query = "SELECT * FROM users WHERE email = '$email'";
            $result = mysqli_query($link, $query);
    
            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                if (password_verify($password, $user['password'])) {
                    $_SESSION['logged_in_user'] = [
                        'email' => $user['email'],
                        'id' => $user['id'],
                        'created_at' => $user['created_at']
                    ];
                    echo "<div class='message-container'>Logowanie zakończone sukcesem!</div>";
                } else {
                    echo "<div class='error-message'>Nieprawidłowe dane! Spróbuj jeszcze raz.</div>";
                }
            } else {
                echo "<div class='error-message'>Nieprawidłowe dane! Spróbuj jeszcze raz.</div>";
            }
        }
    }

    // Obsługa wylogowania
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
        unset($_SESSION['logged_in_user']);
        echo "<div class='message success'>Wylogowano pomyślnie.</div>";
    }

    // Obsługa wyświetlania ID użytkownika
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['show_user_id'])) {
        if (isset($_SESSION['logged_in_user']) && isset($_SESSION['logged_in_user']['id'])) {
            echo "<div class='message success'>ID zalogowanego użytkownika: " . $_SESSION['logged_in_user']['id'] . "</div>";
        } else {
            echo "<div class='message error'>Brak zalogowanego użytkownika. Zaloguj się, aby zobaczyć ID.</div>";
        }
    }

    // Formularze
    echo '<div class="container"><br><br><br><br><hr><br>
            <form method="POST" action="" class="form-selector">
                <input type="hidden" name="form_type" value="register">
                <button type="submit" class="btn">Zarejestruj Się</button>
            </form>';

    if ($form_type === 'register') {
        // Formularz rejestracji
        echo '<br><hr><form method="POST" action="" class="form-container">
                <h2 class="form-title">Rejestracja</h2>
                <label for="email">Adres e-mail</label>
                <input type="email" name="email" placeholder="Adres e-mail" required>
                <label for="password">Hasło</label>
                <input type="password" name="password" placeholder="Hasło" required>
                <label for="confirm_password">Potwierdź hasło</label>
                <input type="password" name="confirm_password" placeholder="Potwierdź hasło" required>
                <button type="submit" name="register" class="btn">Zarejestruj się</button>
              </form>';
    } elseif ($form_type === 'login') {
        // Formularz logowania
        echo '<br><hr><form method="POST" action="" class="form-container">
                <br><h2 class="form-title">Logowanie</h2>
                <label for="email">Adres e-mail</label><br>
                <input type="email" name="email" placeholder="Adres e-mail" required>
                <br><label for="password">Hasło</label><br>
                <input type="password" name="password" placeholder="Hasło" required>
                <button type="submit" name="login" class="btn">Zaloguj się</button>
              </form>';
    }

    echo '</div>';
}

function Wyloguj() {
    // Usuwanie danych użytkownika z sesji
    if (isset($_SESSION['logged_in_user'])) {
        unset($_SESSION['logged_in_user']); // Usuwa dane zalogowanego użytkownika
        echo "<p>Wylogowano pomyślnie.</p>";
    } else {
        echo "<p>Brak użytkownika do wylogowania.</p>";
    }
}

?>
