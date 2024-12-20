<?php


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
            <button type="submit">Bejelentkezés</button>
          </form>';
}
$error_message = ""; // Hibaüzenet tárolása

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Bemeneti adatok validálása
    $nev = trim($_POST['nev']);
    $email = trim($_POST['email']);
    $jelszo = trim($_POST['jelszo']);

    // Adatbázis kapcsolat létrehozása
    require('kapcs.php');
    $kapcs = dbkapcs();

    // Ellenőrizzük az e-mail cím formátumát
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Érvénytelen e-mail cím formátum!";
    }

    // Ha az e-mail cím formátuma helyes, folytatjuk az e-mail ellenőrzésével
    if (empty($error_message)) {
        // Ellenőrizzük, hogy az e-mail már létezik-e
        $sql_check_email = "SELECT email FROM felhasznalo WHERE email = ?";
        $stmt_check = mysqli_prepare($kapcs, $sql_check_email);

        if ($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, "s", $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $error_message = "Ez az email cím már foglalt!";
            }

            mysqli_stmt_close($stmt_check);
        } else {
            $error_message = "Hiba az e-mail ellenőrzés során: " . mysqli_error($kapcs);
        }
    }

    // Ha az email cím egyedi, folytatjuk a jelszó érvényességi ellenőrzésével
    if (empty($error_message)) {
        // Jelszó érvényességi ellenőrzés
        if (!preg_match('/[A-Z]/', $jelszo) || !preg_match('/[0-9]/', $jelszo) || strlen($jelszo) < 8) {
            $error_message = "A jelszónak legalább 8 karakter hosszúnak kell lennie, és tartalmaznia kell nagybetűt és számot!";
        }
    }

    // Ha nincs hiba a jelszóval, akkor titkosítjuk és beszúrjuk az adatokat
    if (empty($error_message)) {
        // Jelszó titkosítása
        $hashed_jelszo = password_hash($jelszo, PASSWORD_DEFAULT);
        $szerep = 1; // Alapértelmezett szerep értéke

        // Felhasználó adatainak beszúrása az adatbázisba
        $sql = 'INSERT INTO felhasznalo (nev, email, jelszo, szerep) VALUES (?, ?, ?, ?)';
        $stmt = mysqli_prepare($kapcs, $sql);

        if ($stmt) {
            // Itt négy paramétert kell kötni a lekérdezéshez
            mysqli_stmt_bind_param($stmt, "sssi", $nev, $email, $hashed_jelszo, $szerep);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: login.php");
            } else {
                $error_message = "Hiba történt: " . mysqli_error($kapcs);
            }

            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Hiba az előkészítés során: " . mysqli_error($kapcs);
        }
    }


    mysqli_close($kapcs);
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
</head>
<body>
<h2>Regisztráció</h2>

<?php if (!empty($error_message)): ?>
    <div style="color: red;"><?php echo $error_message; ?></div>
<?php endif; ?>

<form action="regisztracio.php" method="POST">
    <label for="nev">Név:</label>
    <input type="text" id="nev" name="nev" required><br><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>

    <label for="jelszo">Jelszó:</label>
    <input type="password" id="jelszo" name="jelszo" required><br><br>

    <button type="submit" name="register">Regisztráció</button>
</form>
</body>
</html>
