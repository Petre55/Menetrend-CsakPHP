<?php
session_start();
require('kapcs.php');
$kapcs = dbkapcs();
if (!isset($_SESSION['szerep']) || $_SESSION['szerep'] !== 'admin') {
    header("Location: index.php");
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
// Megállók listázása
$query = "SELECT * FROM megallo";
$result = mysqli_query($kapcs, $query);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Megállók kezelése</title>
</head>
<body>
<h1>Megállók kezelése</h1>
<table border="1">
    <thead>
    <tr>
        <th>Megálló neve</th>
        <th>Szélesség</th>
        <th>Hosszúság</th>
        <th>Akciók</th>
    </tr>
    </thead>
    <tbody>
    <?php
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . ($row['nev']) . "</td>";
        echo "<td>" . ($row['x']) . "</td>";
        echo "<td>" . ($row['y']) . "</td>";
        echo "<td>
                        <a href='megallo.php?edit=" . urlencode($row['nev']) . "'>Módosítás</a> | 
                        <a href='megallo.php?delete=" . urlencode($row['nev']) . "'>Törlés</a>
                      </td>";
        echo "</tr>";
    }
    ?>
    </tbody>
</table>

<!-- Megálló hozzáadása -->
<h2>Új megálló hozzáadása</h2>
<form action="megallo.php" method="POST">
    <label for="nev">Megálló neve:</label>
    <input type="text" name="nev" id="nev" required><br><br>

    <label for="x">Szélességi fok (pl 46.1452):</label>
    <input type="text" name="x" id="x" required><br><br>

    <label for="y">Hosszúsági fok (pl 20.0849):</label>
    <input type="text" name="y" id="y" required><br><br>

    <button type="submit" name="add">Hozzáadás</button>
</form>

<?php
// Hozzáadás funkció
if (isset($_POST['add'])) {
    $nev = mysqli_real_escape_string($kapcs, $_POST['nev']);
    $x = mysqli_real_escape_string($kapcs, $_POST['x']);
    $y = mysqli_real_escape_string($kapcs, $_POST['y']);

    // Koordináták validálása
    if (is_numeric($x) && is_numeric($y) && $x >= -90 && $x <= 90 && $y >= -180 && $y <= 180) {
        // Ellenőrizzük, hogy már létezik-e a megálló neve
        $query = "SELECT COUNT(*) FROM megallo WHERE nev = ?";
        $stmt = mysqli_prepare($kapcs, $query);
        mysqli_stmt_bind_param($stmt, "s", $nev);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($count > 0) {
            echo "<p>Ez a megálló már létezik!</p>";
        } else {
            // Ha nem létezik, hozzáadjuk az új megállót
            $query = "INSERT INTO megallo (nev, x, y) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($kapcs, $query);
            mysqli_stmt_bind_param($stmt, "sss", $nev, $x, $y);

            if (mysqli_stmt_execute($stmt)) {
                ?>
                <script>window.location.href = window.location.href;</script>

                <?php exit;
            } else {
                echo "<p>Hiba történt a hozzáadáskor!</p>";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        echo "<p>Érvénytelen koordináták!</p>";
    }

}


// Megálló törlés funkció
if (isset($_GET['delete'])) {
    $nev = mysqli_real_escape_string($kapcs, $_GET['delete']);



    // Törlés a megallo táblából
    $query = "DELETE FROM megallo WHERE nev = ?";
    $stmt = mysqli_prepare($kapcs, $query);
    mysqli_stmt_bind_param($stmt, "s", $nev);

    if (mysqli_stmt_execute($stmt)) {
        echo "<p>Megálló törölve!</p>";
    } else {
        echo "<p>Hiba történt a megálló törlésekor!</p>";
    }
    mysqli_stmt_close($stmt);

    // Visszairányítás az oldal frissítése érdekében
    header("Location: megallo.php");
    exit;
}


// Megálló módosítása
if (isset($_GET['edit'])) {
    $nev = mysqli_real_escape_string($kapcs, $_GET['edit']);
    $query = "SELECT * FROM megallo WHERE nev = ?";
    $stmt = mysqli_prepare($kapcs, $query);
    mysqli_stmt_bind_param($stmt, "s", $nev);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        ?>
        <h2>Megálló módosítása</h2>
        <form action="megallo.php" method="POST">
            <input type="hidden" name="old_name" value="<?php echo ($row['nev']); ?>">
            <label for="new_name">Új megálló neve:</label>
            <input type="text" name="new_name" id="new_name" value="<?php echo ($row['nev']); ?>" required><br><br>

            <label for="x">Új Szélességi fok (X):</label>
            <input type="text" name="new_x" id="x" value="<?php echo ($row['x']); ?>" required><br><br>

            <label for="y">Új Hosszúsági fok (Y):</label>
            <input type="text" name="new_y" id="y" value="<?php echo ($row['y']); ?>" required><br><br>

            <button type="submit" name="update">Módosítás</button>
        </form>
        <?php
    }
    mysqli_stmt_close($stmt);

}

// Megálló módosítása (ha a felhasználó rákattintott a módosítás gombra)
if (isset($_POST['update'])) {
    $old_name = mysqli_real_escape_string($kapcs, $_POST['old_name']);
    $new_name = mysqli_real_escape_string($kapcs, $_POST['new_name']);
    $new_x = mysqli_real_escape_string($kapcs, $_POST['new_x']);
    $new_y = mysqli_real_escape_string($kapcs, $_POST['new_y']);

    // Koordináták validálása
    if (is_numeric($new_x) && is_numeric($new_y) && $new_x >= -90 && $new_x <= 90 && $new_y >= -180 && $new_y <= 180) {
        $query = "UPDATE megallo SET nev = ?, x = ?, y = ? WHERE nev = ?";
        $stmt = mysqli_prepare($kapcs, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $new_name, $new_x, $new_y, $old_name);

        if (mysqli_stmt_execute($stmt)) {
            echo "<p>Megálló módosítva!</p>";
        } else {
            echo "<p>Hiba történt a módosításkor!</p>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<p>Érvénytelen koordináták!</p>";
    }
    header("Location:megallo.php");

}

mysqli_close($kapcs);

?>
</body>
</html>
