<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lovel-Up</title>
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

    $erreur = "";

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $email = $_POST["email"];
        $password = $_POST["password"];

        if ($email != "" && $password != "") {
            $stmt = $bdd->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->execute();

            // Check if a user record was found (using fetch returns an array or false)
            if ($rep = $stmt->fetch()) {
                // User login successful
                //header("Location: ")  //chemin vers la page principale du site
                exit();
            } else {
                $erreur = "Email ou mot de passe incorrect";
            }
        }
    }
    ?>


    <form action="" method="post">
        <label>E-Mail</label> <input type="email" placeholder="Enter email" name="email" required>
        <br>
        <label>Mot de passe:</label> <input type="password" maxlength="50" placeholder="Enter password" name="password"
            required>
        <br>
        <button type="submit" value="register" name="ok">Login</button>
    </form>

    <?php
    if ($erreur) {
        ?>

        <p><?php echo $erreur; ?> </p>
        <?php
    }
    ?>

</body>

</html>