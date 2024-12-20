<?php
session_start();
require('kapcs.php');

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

$kapcs = dbkapcs();

// Felhasználók listázása
$sql = "SELECT email, nev, szerep FROM felhasznalo";
$result = mysqli_query($kapcs, $sql);

echo "<h1>Felhasználók kezelése</h1>";
echo "<table border='1'>
        <tr>
            <th>Email</th>
            <th>Név</th>
            <th>Szerep</th>
            <th>Műveletek</th>
        </tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>" . ($row['email']) . "</td>
            <td>" . ($row['nev']) . "</td>
            <td>" . ($row['szerep']) . "</td>
            <td>
                <form action='felhasznalok.php' method='POST' style='display:inline;'>
                    <input type='hidden' name='email' value='" . ($row['email']) . "'>
                    <button type='submit' name='edit'>Módosítás</button>
                </form>
                <form action='felhasznalok.php' method='POST' style='display:inline;'>
                    <input type='hidden' name='email' value='" . ($row['email']) . "'>
                    <button type='submit' name='delete'>Törlés</button>
                </form>
            </td>
          </tr>";
}
echo "</table>";

// Felhasználó törlése email alapján
if (isset($_POST['delete']) && isset($_POST['email'])) {
    $email = $_POST['email'];
    $delete_sql = "DELETE FROM felhasznalo WHERE email = ?";
    $stmt = mysqli_prepare($kapcs, $delete_sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    if (mysqli_stmt_execute($stmt)) {
        if($email==$_SESSION['email']){
            header('Location: logout.php');
        }
        echo "<p>A felhasználó törölve lett.</p>";
        header("Refresh:0"); // Frissíti az oldalt
    } else {
        echo "<p>Hiba történt a törlés során.</p>";
    }
    mysqli_stmt_close($stmt);
}

// Felhasználó módosítása (megjelenítése email alapján)
if (isset($_POST['edit']) && isset($_POST['email'])) {
    $email = $_POST['email'];
    $edit_sql = "SELECT email, nev, szerep FROM felhasznalo WHERE email = ?";
    $stmt = mysqli_prepare($kapcs, $edit_sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $edit_result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($edit_result);

    if ($user) {
        echo "<h2>Felhasználó módosítása</h2>
              <form action='felhasznalok.php' method='POST'>
                <input type='hidden' name='email' value='" . ($user['email']) . "'>
                <label for='nev'>Név:</label>
                <input type='text' id='nev' name='nev' value='" . ($user['nev']) . "' required><br><br>
                <label for='szerep'>Szerep:</label>
                <select id='szerep' name='szerep'>
                    <option value='admin' " . ($user['szerep'] === 2 ? 'selected' : '') . ">Admin</option>
                    <option value='felhasznalo' " . ($user['szerep'] === 1 ? 'selected' : '') . ">Felhasználó</option>
                </select><br><br>
                <button type='submit' name='update'>Frissítés</button>
              </form>";
    }
    mysqli_stmt_close($stmt);
}

// Felhasználó módosításának mentése
if (isset($_POST['update']) && isset($_POST['email'])) {
    $email = $_POST['email'];
    $nev = $_POST['nev'];
    $szerep = $_POST['szerep'];
    if($szerep=='admin' or $szerep==2)
        $szerep=2;
    else
        $szerep=1;
    $update_sql = "UPDATE felhasznalo SET nev = ?, szerep = ? WHERE email = ?";
    $stmt = mysqli_prepare($kapcs, $update_sql);
    mysqli_stmt_bind_param($stmt, "sss", $nev, $szerep, $email);
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>A felhasználó adatai frissítve lettek.</p>";
        header("Refresh:0"); // Frissíti az oldalt
    } else {
        echo "<p>Hiba történt a frissítés során.</p>";
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($kapcs);
?>
