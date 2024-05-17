<?php session_start(); ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lovel-Up</title>
</head>

<?php
    include ('../../src/bin/account/loginAccount.php');
?>

<body>

    <form action="" method="post">
        <label>E-Mail</label> <input type="email" placeholder="Enter email" name="email" required>
        <br>
        <label>Mot de passe:</label> <input type="password" maxlength="50" placeholder="Enter password" name="password"
            required>
        <br>
        <button type="submit" value="register" name="ok">Login</button>
    </form>

<?php

    //connexion

    if(isset($_POST["ok"]))
    {
        loginAccount($_POST["email"], $_POST["password"]);
        if (isset($_SESSION["userprofile"]))
        {
            echo "<script>window.location.replace('../utilisateur/user.php');</script>";
        }
    }
?>

</body>

</html>