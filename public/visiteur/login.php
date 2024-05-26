<?php session_start(); ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupid-Quest_login</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="../../src/element/header.css">
</head>

<body>
    <?php 
        include('../../src/element/header.html');
    ?>
    <div class="container">
    <form action="" method="post" style="border: 1px solid red">
        <div class="mail"><label>E-Mail</label> <input type="email" placeholder="Enter email" name="email" required></div>
        <br>
        <div class="mdp"><label>Mot de passe:</label> <input type="password" maxlength="50" placeholder="Enter password" name="password" required></div>
        <br>
        <div class="logbtn"><button type="submit" value="register" name="ok">Login</button></div>
    </form>
    </div>

    <?php
include('../../src/bin/account/loginAccount.php');

// Vérifie l'envoi du formulaire avant la redirection
if (isset($_POST["ok"]))
{
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Si les identifiants sont ceux spécifiés, redirige vers la page spécifiée
    if ($email === "admin@example.com" && $password === "password123") {
        echo "<script>window.location.replace('../admin/admin.php');</script>";
    } else {
        // Sinon, vérifie l'authentification normalement
        if (loginAccount($email, $password))
        {
            echo "<script>window.location.replace('../utilisateur/user.php');</script>";
        }
    }
}
?>


</body>

</html>