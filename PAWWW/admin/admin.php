<?php
require_once '../cfg.php';

echo '<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../Style/style.css">
</head>
<body class="body-admin"><div id="content">';

function FormularzLogowania($error = '') {
    $wynik = '
    <div class="logowanie">
        <h1 class="heading">Panel CMS:</h1>';
    
    if (!empty($error)) {
        $wynik .= '<p class="error-message">' . htmlspecialchars($error) . '</p>';
    }

    $wynik .= '
        <div>
            <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">
                <div class="form-group">
                    <label for="login_email" class="form-label">Email:</label>
                    <input type="text" id="login_email" name="login_email" class="input-field" placeholder="Wpisz email" required>
                </div>
                <div class="form-group">
                    <label for="login_pass" class="form-label">Hasło:</label>
                    <input type="password" id="login_pass" name="login_pass" class="input-field" placeholder="Wpisz hasło" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="x1_submit" class="submit-button">Zaloguj</button>
                </div>
            </form>
        </div>
    </div>';

    echo $wynik;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputLogin = $_POST['login_email'] ?? '';
    $inputPass = $_POST['login_pass'] ?? '';

    if ($inputLogin === $login && $inputPass === $pass) {
        $_SESSION['logged_in'] = true;
        header('Location: admin_panel.php');
        exit();
    } else {
        FormularzLogowania('Nieprawidłowy login lub hasło!');
    }
} elseif (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    FormularzLogowania();
} else {
    header('Location: admin_panel.php');
}

echo '</div></div></body>
</html>';
?>