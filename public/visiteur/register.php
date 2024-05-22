<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lovel-Up</title>
    <link rel="stylesheet" href="register.css"> <!-- peut etre à retirer-->
</head>

<body>
    <!-- le css n'etait pas appliqué au rechargement de la page après l'injection du php donc on ajoute le css ici au lieu du fichier externe-->
    <style>
        p {
            font-family: "Google Sans", sans-serif;
            font-weight: bold;
            color: rgb(73, 73, 73);
        }
    </style>

    <?php
        include('../../src/bin/account/createAccount.php');
    ?>

    <form action="" method="post">
        <label>Pseudonyme</label> <input maxlength="50" placeholder="Votre pseudonyme" name="id" required>
        <br>
        <label>Nom</label> <input maxlength="50" placeholder="Votre nom" name="lastname" required>
        <br>
        <label>Prénom</label> <input maxlength="50" placeholder="Votre prénom" name="name" required>
        <br>
        <label>Genre</label> <select name="gender">
            <option value="Homme">Homme</option>
            <option value="Femme">Femme</option>
            <option value="Autre / Non spécifié">Non spécifié</option>
        </select>
        <label>Date de naissance</label> <input type="date" placeholder="mm/dd/yyyy" name="birthdate" required>
        <br>
        <label>E-Mail</label> <input type="email" placeholder="Enter email" name="email" required>
        <br>
        <label>Mot de passe</label> <input type="password" maxlength="50" placeholder="Enter password" name="password"
            required>
        <br>
        <button type="submit" value="register" name="ok">S'inscrire</button>
    </form>

<?php
    if (isset($_POST["ok"]))
    {
        createAccount($_POST['id'], $_POST['gender'], $_POST['name'], $_POST['lastname'], $_POST['birthdate'], $_POST['email'], $_POST['password']);
    }
?>

</body>

</html>