<?php

//Fonction pour l'enregistrement d'un utilisateur
function createAccount($id, $gender, $name, $lastname, $birthdate, $email, $password)
{
    $email = strtolower($email); //en cas de problèmz de saisie lors de l'inscription
    $dir = "../../data/users/" . $email;
    //Vérifie si le compte existe déjà et la sécurité mot de passe
    if (!file_exists($dir)){
        if (strlen($password) >= 6 && preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password) && preg_match('/[0-9]/', $password) && preg_match('/[^a-zA-Z0-9]/', $password))
        {
            if(mkdir($dir))
            {
                $file= fopen($dir .'/profile.txt','w');
                fwrite($file, $id . "\n");
                fwrite($file, $gender . "\n");
                fwrite($file, $lastname . "\n");
                fwrite($file, $name ."\n");
                fwrite($file, $birthdate ."\n");
                fwrite($file, $email ."\n");
                fwrite($file, password_hash($password, PASSWORD_DEFAULT). "\n");
                fclose($file);

                //inscription réussie
                echo "<div><p>Merci de vous être inscrit, tout s'est déroulé comme prévu !<br> 
                Vous pouvez dès à présent vous connecter à Lovel-Up pour rencontrer des personnes partageant les mêmes intérêts que vous dans la rubrique <a href='./login.php'>Se connecter<a> </p></div>";
    
            }
            else
            {
                echo "<span class='' > Erreur : échec dans la création du compte </span>";
            }
        }
        else
        {
            echo "<span class ='' >Mot de passe trop faible ! Intégrez au minimum : <br>
            <ul><li>6 caracteres</li><li>1 Majuscule</li><li>1 Minuscule</li><li>1 Chiffre</li><li>1 Caractere Special</li></ul></span>";
        }
    }
    else
    {
        echo "<span class = ''>Ce compte existe déjà ! Connectez vous avec votre email et votre mot de passe <a href='../../../public/visiteur/login.php' > ici </a> </span>";
    }
}