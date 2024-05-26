<?php

// Fonction pour l'enregistrement d'un utilisateur
function createAccount($id, $gender, $name, $lastname, $birthdate, $email, $password)
{
    $email = strtolower($email); // En cas de problème de saisie lors de l'inscription
    $dir = "../../data/users/" . $email;
    $bannedAccountsFile = $_SERVER['DOCUMENT_ROOT'] . '/data/ban/bannedAccount.txt';

    // Vérifie si l'email est dans la liste des comptes bannis
    if (file_exists($bannedAccountsFile)) {
        $bannedAccounts = file($bannedAccountsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($bannedAccounts as $bannedEmail) {
            if (trim($bannedEmail) === $email) {
                echo "<div class='err-message'><p>Compte banni, va voir ailleurs<br></p></div>";
                return;
            }
        }
    }

    // Vérifie si le compte existe déjà
    if (!file_exists($dir)) {
        // Vérifie si le mot de passe est suffisamment sécurisé
        if (strlen($password) >= 6 && 
            preg_match('/[a-z]/', $password) && 
            preg_match('/[A-Z]/', $password) && 
            preg_match('/[0-9]/', $password) && 
            preg_match('/[^a-zA-Z0-9]/', $password)) 
        {
            // Crée le répertoire de l'utilisateur
            if (mkdir($dir)) {
                $file = fopen($dir . '/profile.txt', 'w');
                fwrite($file, $id . "\n");
                fwrite($file, $gender . "\n");
                fwrite($file, $lastname . "\n");
                fwrite($file, $name . "\n");
                fwrite($file, $birthdate . "\n");
                fwrite($file, $email . "\n");
                fwrite($file, password_hash($password, PASSWORD_DEFAULT) . "\n");
                fclose($file);

                // Création du fichier de biographie
                file_put_contents($dir . '/bio.txt', '');

                // Inscription réussie
                echo "<div class='err-message><p>Merci de vous être inscrit, tout s'est déroulé comme prévu !<br> 
                Vous pouvez dès à présent vous connecter à Cupid Quest pour rencontrer des personnes partageant les mêmes intérêts que vous dans la rubrique <a href='./login.php'>Se connecter</a></p></div>";
            } else {
                echo "<span class='err-message>Erreur : échec dans la création du compte</span>";
            }
        } else {
            echo "<span class='err-message>Mot de passe trop faible ! Intégrez au minimum : <br>
            <ul><li>6 caractères</li><li>1 Majuscule</li><li>1 Minuscule</li><li>1 Chiffre</li><li>1 Caractère Spécial</li></ul></span>";
        }
    } else {
        echo "<span class='err-message>Ce compte existe déjà ! Connectez-vous avec votre email et votre mot de passe <a href='../../../public/visiteur/login.php'>ici</a></span>";
    }
}