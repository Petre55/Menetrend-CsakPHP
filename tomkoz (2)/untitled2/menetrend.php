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
    echo '<form action="admin.php" method="POST" style="display:inline;">
            <button type="submit">Admin</button>
          </form>';
    echo '<form action="logout.php" method="POST" style="display:inline;">
            <button type="submit">Kijelentkezés</button>
          </form>';
} else {
    echo '<form action="login.php" method="POST" style="display:inline;">
            <button type="submit">Bejelentkezés/Regisztráció</button>
          </form>';
}

if (isset($_POST['add_jarat'])) {
    $jaratszam = $_POST['jaratszam'];
    $kezdom = $_POST['kezdom'];
    $vegm = $_POST['vegm'];
    $kezdo_idopont = $_POST['kezdo_idopont'];
    $veg_idopont = $_POST['veg_idopont'];

    // Ellenőrizzük, hogy a járatszám már létezik-e
    $query = "SELECT jaratszam FROM jarat WHERE jaratszam = ?";
    $stmt = mysqli_prepare($kapcs, $query);
    mysqli_stmt_bind_param($stmt, 's', $jaratszam);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo "<p>Hiba: Már létezik ilyen járatszám.</p>";
    }
    elseif (strtotime($veg_idopont) <= strtotime($kezdo_idopont)) {
        echo "<p>Hiba: A vég időpontnak nagyobbnak kell lennie a kezdő időpontnál.</p>";
    } else {
        // Járat beszúrása
        $query = "INSERT INTO jarat (jaratszam, kezdom, vegm) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($kapcs, $query);
        mysqli_stmt_bind_param($stmt, 'sss', $jaratszam, $kezdom, $vegm);
        mysqli_stmt_execute($stmt);

        // Most hozzáadjuk a menetrendet is
        // Lekérjük a legmagasabb sorszamot, hogy ne ütközzön
        $query_sorszam = "SELECT MAX(sorszam) AS max_sorszam FROM menetrend WHERE jaratszam = ?";
        $stmt_sorszam = mysqli_prepare($kapcs, $query_sorszam);
        mysqli_stmt_bind_param($stmt_sorszam, 's', $jaratszam);
        mysqli_stmt_execute($stmt_sorszam);
        $result_sorszam = mysqli_stmt_get_result($stmt_sorszam);
        $row_sorszam = mysqli_fetch_assoc($result_sorszam);
        $next_sorszam = $row_sorszam['max_sorszam'] + 1;

        // Menetrendi adatok beszúrása
        $insert_menetrend_sql = "INSERT INTO menetrend (jaratszam, megallonev, idopont, sorszam) VALUES (?, ?, ?, ?)";

        // Kezdő megálló beszúrása
        $stmt = mysqli_prepare($kapcs, $insert_menetrend_sql);
        mysqli_stmt_bind_param($stmt, 'sssi', $jaratszam, $kezdom, $kezdo_idopont, $next_sorszam);
        mysqli_stmt_execute($stmt);

        // Növeljük a sorszámot
        $next_sorszam++;

        // Végállomás beszúrása
        $stmt = mysqli_prepare($kapcs, $insert_menetrend_sql);
        mysqli_stmt_bind_param($stmt, 'sssi', $jaratszam, $vegm, $veg_idopont, $next_sorszam);
        mysqli_stmt_execute($stmt);

        header("Location: menetrend.php");
    }
    mysqli_stmt_close($stmt);
}
if (isset($_POST['delete_jarat'])) {
    $jaratszam = $_POST['jaratszam'];

    // Töröljük a járatot a jarat táblából
    $query = "DELETE FROM jarat WHERE jaratszam = ?";
    $stmt = mysqli_prepare($kapcs, $query);
    mysqli_stmt_bind_param($stmt, 's', $jaratszam);
    mysqli_stmt_execute($stmt);

    header("Location: menetrend.php");
}
// Járatszámok lekérdezése
$jaratszam_sql = "SELECT jaratszam FROM jarat ORDER BY jaratszam ASC";
$jaratszam_result = mysqli_query($kapcs, $jaratszam_sql);

// Menetrend listázása
$sql = "SELECT * FROM menetrend ORDER BY jaratszam, sorszam";
$menetrendek = mysqli_query($kapcs, $sql);
$query = "SELECT nev FROM megallo";
$megallok_result = mysqli_query($kapcs, $query);
$query = "SELECT jaratszam, kezdom, vegm FROM jarat";
$result = mysqli_query($kapcs, $query);
?>
<h2>Járatok</h2>
    <table border="1">
        <tr>
            <th>Járatszám</th>
            <th>Kezdő megálló</th>
            <th>Végállomás</th>
            <th>Műveletek</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <form method="POST">
                    <td><?php echo ($row['jaratszam']); ?></td>
                    <td>
                        <?php echo ($row['kezdom'])?>
                    </td>
                    <td>
                        <?php echo ($row['vegm'])?>

                    </td>
                    <td>
                        <input type="hidden" name="jaratszam" value="<?php echo $row['jaratszam']; ?>">
                        <button type="submit" name="delete_jarat">Törlés</button>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3>Új járat hozzáadása</h3>
    <form method="POST">
        <input type="text" name="jaratszam" placeholder="Járatszám" required>

        <!-- Kezdő megálló legördülő lista -->
        <select name="kezdom" required>
            <option value="">Kezdő megálló választása</option>
<?php
// Megállók listájának kiírása a kezdő megállóhoz
mysqli_data_seek($megallok_result, 0); // Megállók újraindítása
while ($megallo = mysqli_fetch_assoc($megallok_result)) {
    echo "<option value='" . ($megallo['nev']) . "'>"
        . ($megallo['nev']) . "</option>";
}

?>
        </select>
        <!-- Végállomás legördülő lista -->
        <select name="vegm" required>
            <option value="">Végállomás választása</option>
            <?php
            // Megállók listájának kiírása a végállomáshoz
            mysqli_data_seek($megallok_result, 0); // Megállók újraindítása
            while ($megallo = mysqli_fetch_assoc($megallok_result)) {
                echo "<option value='" . ($megallo['nev']) . "'>" . ($megallo['nev']) . "</option>";
            }
            ?>
        </select>


        <!-- Kezdő időpont -->
        <input type="time" name="kezdo_idopont" required>

        <!-- Vég időpont -->
        <input type="time" name="veg_idopont" required>

        <button type="submit" name="add_jarat">Hozzáadás</button>
    </form>
    <?php
echo "<h1>Menetrend kezelése</h1>";

    // Menetrend módosítása
    if (isset($_POST['edit']) && isset($_POST['id'])) {
        $id = $_POST['id'];
        $edit_sql = "SELECT * FROM menetrend WHERE id = ?";
        $stmt = mysqli_prepare($kapcs, $edit_sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $edit_result = mysqli_stmt_get_result($stmt);
        $menetrend = mysqli_fetch_assoc($edit_result);

        if ($menetrend) {
            $megallo_sql = "SELECT nev FROM megallo";
            $megallo_result = mysqli_query($kapcs, $megallo_sql);
            $megallo_options = "";
            while ($megallo_row = mysqli_fetch_assoc($megallo_result)) {
                $selected = ($megallo_row['nev'] == $menetrend['megallonev']) ? "selected" : "";
                $megallo_options .= "<option value='" . ($megallo_row['nev']) . "' $selected>" . ($megallo_row['nev']) . "</option>";
            }

            $jaratszam_options = "";
            mysqli_data_seek($jaratszam_result, 0);
            while ($jaratszam_row = mysqli_fetch_assoc($jaratszam_result)) {
                $selected = ($jaratszam_row['jaratszam'] == $menetrend['jaratszam']) ? "selected" : "";
                $jaratszam_options .= "<option value='" . ($jaratszam_row['jaratszam']) . "' $selected>" . ($jaratszam_row['jaratszam']) . "</option>";
            }

            echo "<h2>Menetrend módosítása</h2>
              <form action='menetrend.php' method='POST'>
                <input type='hidden' name='id' value='" . $menetrend['id'] . "'>
                <label for='jaratszam'>Járatszám:</label>
                <select id='jaratszam' name='jaratszam' required>
                    $jaratszam_options
                </select><br><br>
                <label for='megallonev'>Megálló neve:</label>
                <select id='megallonev' name='megallonev' required>
                    $megallo_options
                </select><br><br>
                <label for='idopont'>Időpont:</label>
                <input type='time' id='idopont' name='idopont' value='" . ($menetrend['idopont']) . "' required><br><br>
                <button type='submit' name='update'>Frissítés</button>
              </form>";
        }
        mysqli_stmt_close($stmt);
    }

    // Menetrend módosítás mentése
    if (isset($_POST['update']) && isset($_POST['id'])) {
        $id = $_POST['id'];
        $jaratszam = $_POST['jaratszam'];
        $megallonev = $_POST['megallonev'];
        $idopont = $_POST['idopont'];

        // Ellenőrzés, hogy létezik-e már ilyen megálló annál a járatnál
        $check_sql = "SELECT COUNT(*) FROM menetrend WHERE jaratszam = ? AND megallonev = ? AND id != ?";
        $stmt = mysqli_prepare($kapcs, $check_sql);
        mysqli_stmt_bind_param($stmt, "ssi", $jaratszam, $megallonev, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($count > 0) {
            // Ha már létezik ilyen megálló, hibaüzenet
            echo "<p>Hiba: Ez a megálló már szerepel az adott járatszámban.</p>";
        } else {
            // Frissítés
            $update_sql = "UPDATE menetrend SET jaratszam = ?, megallonev = ?, idopont = ? WHERE id = ?";
            $stmt = mysqli_prepare($kapcs, $update_sql);
            mysqli_stmt_bind_param($stmt, "sssi", $jaratszam, $megallonev, $idopont, $id);
            if (mysqli_stmt_execute($stmt)) {
                frissit_sorszamok($kapcs, $jaratszam);
                ?>
                <script>window.location.href = window.location.href;</script>
                <?php
            } else {
                echo "<p>Hiba történt a frissítés során.</p>";
            }
            mysqli_stmt_close($stmt);
        }
    }


    // Megállók betöltése a lenyílóhoz
    $megallo_sql = "SELECT nev FROM megallo";
    $megallo_result = mysqli_query($kapcs, $megallo_sql);
    $megallo_options = "";
    while ($megallo_row = mysqli_fetch_assoc($megallo_result)) {
        $megallo_options .= "<option value='" . ($megallo_row['nev']) . "'>" . ($megallo_row['nev']) . "</option>";
    }

// Járatszámok lekérdezése
$jaratszam_sql = "SELECT DISTINCT jaratszam FROM menetrend ORDER BY jaratszam ASC";
$jaratszam_result = mysqli_query($kapcs, $jaratszam_sql);

// Minden járatszámhoz új táblázat
while ($jarat_row = mysqli_fetch_assoc($jaratszam_result)) {
    $current_jaratszam = $jarat_row['jaratszam'];
    echo "<h2>Járatszám: " . $current_jaratszam . "</h2>";
    echo "<table border='1'>
            <tr>
                <th>Járatszám</th>
                <th>Megálló neve</th>
                <th>Időpont</th>
                <th>Műveletek</th>
            </tr>";

    // Menetrend listázása az aktuális járatszámhoz
    $sql = "SELECT id, jaratszam, megallonev, idopont, sorszam FROM menetrend WHERE jaratszam = ? ORDER BY sorszam ASC";
    $stmt = mysqli_prepare($kapcs, $sql);
    mysqli_stmt_bind_param($stmt, "s", $current_jaratszam);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>" . $row['jaratszam'] . "</td>
                <td>" . $row['megallonev'] . "</td>
                <td>" . $row['idopont'] . "</td>
                <td>";
        echo "<form action='menetrend.php' method='POST' style='display:inline;'>
                <input type='hidden' name='id' value='" . $row['id'] . "'>
                <button type='submit' name='edit'>Módosítás</button>
              </form>";
        echo "<form action='menetrend.php' method='POST' style='display:inline;'>
                <input type='hidden' name='id' value='" . $row['id'] . "'>
                <button type='submit' name='delete'>Törlés</button>
              </form>";
        echo "</td></tr>";
    }
    echo "</table>";
}






// Sorszámok frissítése funkció
function frissit_sorszamok($kapcs, $jaratszam)
{
    // Kezdjük a tranzakciót
    mysqli_begin_transaction($kapcs);

    try {
        // Ideiglenes változó a ranghoz
        $set_rank_sql = "SET @rank = 0";
        if (!mysqli_query($kapcs, $set_rank_sql)) {
            throw new Exception("Hiba történt a rang változó beállítása során.");
        }

        // Frissítsük a sorszámokat
        $update_sql = "UPDATE menetrend 
                       SET sorszam = (@rank := @rank + 1) 
                       WHERE jaratszam = ? 
                       ORDER BY idopont";
        $stmt = mysqli_prepare($kapcs, $update_sql);
        mysqli_stmt_bind_param($stmt, "s", $jaratszam);
        mysqli_stmt_execute($stmt); // Kihagyott végrehajtás
        mysqli_stmt_close($stmt);

        // Lekérjük a legkisebb és legnagyobb sorszámokhoz tartozó megállóneveket
        $min_max_sql = "SELECT (SELECT megallonev FROM menetrend WHERE jaratszam = ? ORDER BY sorszam ASC LIMIT 1) AS kezdo_megallo, 
                               (SELECT megallonev FROM menetrend WHERE jaratszam = ? ORDER BY sorszam DESC LIMIT 1) AS veg_megallo";
        $stmt = mysqli_prepare($kapcs, $min_max_sql);
        mysqli_stmt_bind_param($stmt, "ss", $jaratszam, $jaratszam);
        mysqli_stmt_execute($stmt); // Kihagyott végrehajtás
        mysqli_stmt_bind_result($stmt, $kezdo_megallo, $veg_megallo);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Frissítjük a járat táblát a lekért megállónevekkel
        $update_jarat_sql = "UPDATE jarat SET kezdom = ?, vegm = ? WHERE jaratszam = ?";
        $stmt = mysqli_prepare($kapcs, $update_jarat_sql);
        mysqli_stmt_bind_param($stmt, "sss", $kezdo_megallo, $veg_megallo, $jaratszam);
        mysqli_stmt_execute($stmt); // Kihagyott végrehajtás
        mysqli_stmt_close($stmt);

        // Elkötelezzük a tranzakciót
        mysqli_commit($kapcs);
    } catch (Exception $e) {
        // Ha hiba történik, visszagörgetjük a tranzakciót
        mysqli_rollback($kapcs);
    }
}




// Menetrend törlése
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = $_POST['id'];

    // Járatszám lekérése a törlés előtt
    $jarat_sql = "SELECT jaratszam FROM menetrend WHERE id = ?";
    $stmt = mysqli_prepare($kapcs, $jarat_sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $jaratszam);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $delete_sql = "DELETE FROM menetrend WHERE id = ?";
    $stmt = mysqli_prepare($kapcs, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        // Ellenőrzés, hogy hány megálló maradt
        $count_sql = "SELECT COUNT(*) FROM menetrend WHERE jaratszam = ?";
        $stmt = mysqli_prepare($kapcs, $count_sql);
        mysqli_stmt_bind_param($stmt, "s", $jaratszam);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($count <= 1) { // Ha 0 vagy 1 megálló marad, töröljük a menetrend összes sorát és a járatot is
            $delete_all_sql = "DELETE FROM menetrend WHERE jaratszam = ?";
            $stmt = mysqli_prepare($kapcs, $delete_all_sql);
            mysqli_stmt_bind_param($stmt, "s", $jaratszam);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $delete_jarat_sql = "DELETE FROM jarat WHERE jaratszam = ?";
            $stmt = mysqli_prepare($kapcs, $delete_jarat_sql);
            mysqli_stmt_bind_param($stmt, "s", $jaratszam);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            echo "<p>A járat és az összes kapcsolódó menetrend törölve lett.</p>";
        } else {
            frissit_sorszamok($kapcs, $jaratszam);
        }

        echo "<p>A menetrend törölve lett.</p>";?>
        <script>window.location.href = window.location.href;</script>
    <?php } else {
        echo "<p>Hiba történt a törlés során.</p>";
    }
}




// Járatszámok legördülő listája
$jaratszam_options = "";
mysqli_data_seek($jaratszam_result, 0); // A járatszámok újra beolvassuk
while ($jaratszam_row = mysqli_fetch_assoc($jaratszam_result)) {
    $jaratszam_options .= "<option value='" . ($jaratszam_row['jaratszam']) . "'>" . ($jaratszam_row['jaratszam']) . "</option>";
}
// Menetrend hozzáadása
if (isset($_POST['add'])) {
    $jaratszam = $_POST['jaratszam'];
    $megallonev = $_POST['megallonev'];
    $idopont = $_POST['idopont'];

    // Ellenőrzés, hogy az adott járatszámhoz van-e már a megadott megálló
    $check_sql = "SELECT COUNT(*) FROM menetrend WHERE jaratszam = ? AND megallonev = ?";
    $stmt = mysqli_prepare($kapcs, $check_sql);
    mysqli_stmt_bind_param($stmt, "ss", $jaratszam, $megallonev);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($count > 0) {
        // Ha már van ilyen megálló a járatszám alatt, hibaüzenet
        echo "<p>Hiba: Ez a megálló már szerepel ebben a járatszámban.</p>";
    } else {
        // Ha nincs, hozzáadjuk az új menetrendet
        $insert_sql = "INSERT INTO menetrend (jaratszam, megallonev, idopont) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($kapcs, $insert_sql);
        mysqli_stmt_bind_param($stmt, "sss", $jaratszam, $megallonev, $idopont);
        if (mysqli_stmt_execute($stmt)) {
            echo "<p>A menetrend hozzáadva lett.</p>";
            frissit_sorszamok($kapcs, $jaratszam);
            mysqli_stmt_close($stmt);?>
            <script>window.location.href = window.location.href;</script>
            <?php exit;
        } else {
            echo "<p>Hiba történt a hozzáadás során.</p>";
        }

        mysqli_stmt_close($stmt);
    }
}
// Járatszámok legördülő listája
$jaratszam_options = "";
mysqli_data_seek($jaratszam_result, 0); // A járatszámok újra beolvassuk
while ($jaratszam_row = mysqli_fetch_assoc($jaratszam_result)) {
    $jaratszam_options .= "<option value='" . ($jaratszam_row['jaratszam']) . "'>" . ($jaratszam_row['jaratszam']) . "</option>";
}
echo "<h2>Új megálló hozzáadása egy menetrendhez</h2>
      <form action='menetrend.php' method='POST'>
        <label for='jaratszam'>Járatszám:</label>
        <select id='jaratszam' name='jaratszam' required>
            $jaratszam_options
        </select><br><br>
        <label for='megallonev'>Megálló neve:</label>
        <select id='megallonev' name='megallonev' required>
            $megallo_options
        </select><br><br>
        <label for='idopont'>Időpont:</label>
        <input type='time' id='idopont' name='idopont' required><br><br>
        <button type='submit' name='add'>Hozzáadás</button>
      </form>";

?>

<?php
mysqli_close($kapcs);

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Menetrend</title>
</head>



