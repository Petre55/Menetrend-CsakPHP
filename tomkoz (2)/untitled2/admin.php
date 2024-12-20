<?php

session_start();
// Csak admin jogosultsággal engedélyezzük az oldalt
if (!isset($_SESSION['loggedin']) || $_SESSION['szerep'] != 'admin') {
    header("Location: login.php");
    exit;
}
echo '<form action="index.php" method="POST" style="display:inline;">
        <button type="submit">Index</button>
      </form>';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (isset($_SESSION['szerep']) && $_SESSION['szerep'] === 'admin') {
        echo '<form action="admin.php" method="POST" style="display:inline;">
                <button type="submit">Admin</button>
              </form>';
    }
    echo '<form action="logout.php" method="POST" style="display:inline;">
            <button type="submit">Kijelentkezés</button>
          </form>';
} else {
    echo '<form action="login.php" method="POST" style="display:inline;">
            <button type="submit">Bejelentkezés/Regisztráció</button>
          </form>';
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Adminisztráció</title>
</head>
<body>
<h1>Adminisztrációs felület</h1>
<ul>
    <li><a href="felhasznalok.php">Felhasználók kezelése</a></li>
    <li><a href="menetrend.php">Menetrend kezelése</a></li>
    <li><a href="megallo.php">Megállók kezelése</a></li>
</ul>
<form action="logout.php" method="POST">
    <button type="submit">Kijelentkezés</button>
</form>
</body>
</html>
