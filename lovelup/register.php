<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lovel-Up</title>
    <link rel="stylesheet" href="/register.css"> <!-- peut etre à retirer-->
</head>

<body>
    <?php

    $servername = "localhost";
    $username = "root";
    $mdp = "";

    try {
        $bdd = new PDO("mysql:host=$servername; dbname=lovelup", $username, $mdp);
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //echo "Connexion Réussie";
    } catch (PDOException $e) {
        echo "" . $e->getMessage() . "";
    }
    if (isset($_POST["ok"])) {
        $name = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $date = date("Y-m-d H:i:s");
        $requete = $bdd->prepare("INSERT INTO users VALUES (0, :username, :email, :password, :date)");
        $requete->execute(
            array(
                "username" => $name,
                "email" => $email,
                "password" => $password,
                "date" => $date
            )
        );
        header("Location: registersuccess.html");
        exit();
    }
    ?>

    <!-- le css n'etait pas appliqué au rechargement de la page après l'injection du php donc on ajoute le css ici au lieu du fichier externe-->
    <style>
        p {
            font-family: "Google Sans", sans-serif;
            font-weight: bold;
            color: rgb(73, 73, 73);
        }
    </style>

    <form action="" method="post">
        <label>Pseudonyme</label> <input maxlength="50" placeholder="Enter username" name="username" required>
        <br>
        <label>E-Mail</label> <input type="email" placeholder="Enter email" name="email" required>
        <br>
        <label>Mot de passe:</label> <input type="password" maxlength="50" placeholder="Enter password" name="password"
            required>
        <br>
        <button type="submit" value="register" name="ok">Login</button>
    </form>



</body>

</html>