<?php
session_start();
$showError = false;


echo '<form action="index.php" method="POST" style="display:inline;">
        <button type="submit">Index</button>
      </form>';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (isset($_SESSION['szerep']) && $_SESSION['szerep'] === 'admin') {
        echo '<form action="admin.php" method="POST" style="display:inline;">
                <button type="submit">Admin</button>
              </form>';
    }
}

if (isset($_POST['gomb'])) {
    require('kapcs.php');
    $kapcs = dbkapcs();

    if (!isset($_POST['email'], $_POST['jelszo'])) {
        $showError = "Üres az egyik mező";
    } elseif (empty($_POST['email']) || empty($_POST['jelszo'])) {
        $showError = "Üres az egyik mező";
    } else {
        $query = "SELECT email, jelszo, nev, szerep FROM felhasznalo WHERE email = ?";
        $stmt = mysqli_prepare($kapcs, $query);
        mysqli_stmt_bind_param($stmt, "s", $_POST['email']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user) {
            // Jelszó ellenőrzése
            if (password_verify($_POST['jelszo'], $user['jelszo'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['nev'];
                $_SESSION['email'] = $user['email'];
                if($user['szerep']==2){$_SESSION['szerep'] = 'admin';}

                header('Location: index.php');
                exit;
            } else {
                $showError = "Helytelen jelszó!";
            }
        } else {
            $showError = "Hibás felhasználónév!";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
</head>
<body>
<?php
if ($showError) {
    echo '<div style="color:red;">  
            <strong>Error!</strong> '. $showError .' 
          </div>';
}
?>
<div class="login">
    <form action="login.php" method="POST">
        <table>
            <tr>
                <td><label for="email">Email</label><br>
                    <input id="email" type="text" name="email" required>
                </td>
            </tr>
            <tr>
                <td><label for="jelszo">Jelszó</label><br>
                    <input id="jelszo" type="password" name="jelszo" required>
                </td>
            </tr>
            <tr>
                <td><input type="submit" value="Bejelentkezés" name="gomb"></td>
            </tr>
            <tr>
                <td>Nincs még profilod? <a href="regisztracio.php">Regisztráció</a></td>
            </tr>
        </table>
    </form>
</div>
</body>
</html>
