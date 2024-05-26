<?php session_start(); ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupid Quest</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="../../src/element/header.css">
</head>

<body>

    <?php 
        include('../../src/element/headerlogin.html');
    ?>
    <div class="container">
        <form action="" method="post">
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
            // vérifie l'authentification 
            if (loginAccount($email, $password))
            {
            echo "<script>window.location.replace('../utilisateur/user.php');</script>";
            }
        }
    ?>

</body>
</html>