<?php
session_start();
require('kapcs.php'); // Adatbázis kapcsolat

// Kapcsolódás az adatbázishoz
$kapcs = dbkapcs();

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

// Bejelentés mentése, csak bejelentkezett felhasználók számára
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {

        if (isset($_POST['bejelentes_jaratszam']) && isset($_POST['megallo'])) {
            $bejelentes_jaratszam = $_POST['bejelentes_jaratszam'];
            $megallo = $_POST['megallo'];
            $felhasznalo_email = $_SESSION['email'];

            $query = "INSERT INTO bejelentes (jaratszam, megallonev, fel_email, datum) VALUES (?, ?, ?, NOW())";
            $stmt = mysqli_prepare($kapcs, $query);
            if ($stmt === false) {
                die('Statement preparation failed: ' . mysqli_error($kapcs));
            }
            mysqli_stmt_bind_param($stmt, "sss", $bejelentes_jaratszam, $megallo, $felhasznalo_email);
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_errno($stmt)) {
                die('Statement execution failed: ' . mysqli_stmt_error($stmt));
            }
            header('Location: index.php');
        }


}


// Aznapi bejelentések lekérdezése
$mai_bejelentesek = [];
$query = "SELECT b.jaratszam, b.megallonev, f.nev,b.datum 
          FROM bejelentes b
          JOIN felhasznalo f ON b.fel_email = f.email
          WHERE DATE(b.datum) = CURDATE()";
$stmt = mysqli_prepare($kapcs, $query);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $mai_bejelentesek[] = $row;
}



// 1. lépés: Lekérdezzük az összes egyedi járatszámot
$query = "SELECT DISTINCT jaratszam FROM menetrend";
$result = mysqli_query($kapcs, $query);
$jaratszamok = [];

// Ellenőrizzük a lekérdezés sikerességét
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $jaratszamok[] = $row['jaratszam']; // Hozzáadjuk az egyedi járatszámokat egy tömbhöz
    }
}

// 2. lépés: Ha a felhasználó kiválasztott egy járatszámot, lekérdezzük az összes adatot
$sorok = [];
if (isset($_POST['jaratszam'])) {
    $selected_jaratszam = $_POST['jaratszam'];
    // ORDER BY sorszam ASC - sorszam szerint növekvő sorrend
    $query = "SELECT sorszam, idopont, megallonev, g.x, g.y 
              FROM menetrend m
              JOIN megallo g ON m.megallonev = g.nev
              WHERE m.jaratszam = ?
              ORDER BY m.sorszam ASC";
    $stmt = mysqli_prepare($kapcs, $query);
    mysqli_stmt_bind_param($stmt, "s", $selected_jaratszam);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $previousX = $previousY = null;
    $totalDistance = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $sorok[] = $row; // Hozzáadjuk a sorokat a megjelenítendő tömbhöz

        // Számítsuk ki az utazási távolságot
        if ($previousX !== null && $previousY !== null) {
            $distance = calculateDistance($previousX, $previousY, $row['x'], $row['y']);
            $totalDistance += $distance;
        }
        $previousX = $row['x'];
        $previousY = $row['y'];
    }
}

$megallok = [];
if (isset($_POST['bejelentes_jaratszam'])) {
    $selected_jaratszam = $_POST['bejelentes_jaratszam'];
    // ORDER BY sorszam ASC - sorszam szerint növekvő sorrend
    $query = "SELECT megallonev
              FROM menetrend m
              JOIN megallo g ON m.megallonev = g.nev
              WHERE m.jaratszam = ?
              ORDER BY m.sorszam ASC";
    $stmt = mysqli_prepare($kapcs, $query);
    mysqli_stmt_bind_param($stmt, "s", $selected_jaratszam);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $megallok[] = $row['megallonev'];
    }
}
$query = "SELECT megallonev, COUNT(*) AS megallok_szama
    FROM menetrend
    WHERE megallonev IN (SELECT kezdom FROM jarat) OR megallonev IN (SELECT vegm FROM jarat)
    GROUP BY megallonev
";
$result = $kapcs->query($query);

mysqli_close($kapcs);

// Távolság számítása a Haversine képlet alapján
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Föld sugara kilométerben

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = $earthRadius * $c;

    return round($distance,2);
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Menetrend</title>
</head>
<body>

    <h1>Menetrend</h1>




<!-- Lenyílós választó a járatszámokhoz -->
<form action="" method="POST">
    <label for="jaratszam">Válassz járatszámot:</label>
    <select id="jaratszam" name="jaratszam" required>
        <option value="">-- Válassz --</option>
        <?php foreach ($jaratszamok as $jaratszam): ?>
            <option value="<?php echo ($jaratszam); ?>"><?php echo ($jaratszam); ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Megjelenít</button>
</form>

<!-- Tábla az eredmények megjelenítésére -->
<?php if (!empty($sorok)): ?>
    <h2>Járatszám: <?php echo ($selected_jaratszam); ?></h2>
    <p>Összes távolság: <?php echo ($totalDistance); ?> km</p>
    <table border="1">
        <thead>
        <tr>
            <th>Sorszám</th>
            <th>Időpont</th>
            <th>Megálló név</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($sorok as $sor): ?>
            <tr>
                <td><?php echo ($sor['sorszam']); ?></td>
                <td><?php echo ($sor['idopont']); ?></td>
                <td><?php echo ($sor['megallonev']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<!-- Bejelentés hozzáadása, csak bejelentkezett felhasználók számára -->
<?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
    <h2>Bejelentés</h2>
    <form action="" method="POST">
        <label for="bejelentes_jaratszam">Válassz járatszámot:</label>
        <select id="bejelentes_jaratszam" name="bejelentes_jaratszam" onchange="this.form.submit()" required>
            <option value="">-- Válassz --</option>
            <?php foreach ($jaratszamok as $jaratszam): ?>
                <option value="<?php echo ($jaratszam); ?>" <?php echo isset($selected_jaratszam) && $selected_jaratszam == $jaratszam ? 'selected' : ''; ?>><?php echo ($jaratszam); ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (!empty($megallok)): ?>
        <form action="" method="POST">
            <input type="hidden" name="bejelentes_jaratszam" value="<?php echo ($selected_jaratszam); ?>">
            <label for="megallo">Válassz megállót:</label>
            <select id="megallo" name="megallo" required>
                <option value="">-- Válassz --</option>
                <?php foreach ($megallok as $megallo): ?>
                    <option value="<?php echo ($megallo); ?>"><?php echo ($megallo); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Bejelentés</button>
        </form>
    <?php endif; ?>
<?php endif; ?>




<!-- Aznapi bejelentések felsorolása -->
<?php if (!empty($mai_bejelentesek)): ?>
    <h2>Mai bejelentések</h2>
    <ul>
        <?php foreach ($mai_bejelentesek as $bejelentes): ?>
            <li><?php echo ($bejelentes['jaratszam']) . " járatszámú busz vagy késik, vagy kimaradt a " . ($bejelentes['megallonev']) . " megállónál. Ekkor: ".($bejelentes['datum'])." Bejelentő: " . ($bejelentes['nev']); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
    <?php
    if ($result->num_rows > 0) {
        echo '<h2>Kezdő megállón, vagy végállomáson megálló járatok száma</h2>';
        echo "<table border='1'>";
        echo "<tr><th>Megálló név</th><th>Megállók száma</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . ($row["megallonev"]) . "</td><td>" . ($row["megallok_szama"]) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Nincs találat.";
    }
    ?>

</body>
</html>

