<?php

// Fonction qui stock les lignes du fichier 'file' dans un tableau
function getInfo($file)
{
    return file($file, FILE_IGNORE_NEW_LINES);
}

function loginAccount($email, $password)
{
    $email = strtolower($email);
    $dir = "../../data/users/" . $email;

    if (file_exists($dir))
    {
        $fileContent = getInfo($dir . "/profile.txt");
        if (password_verify($password, $fileContent[4]))
        {
            // lance la session et affecte les variables de session
            session_start();
            $_SESSION["userprofile"] = [
                'email' => $email,
                'password' => $password,
                'birthdate' => $fileContent[2],
                'lastname' => $fileContent[0],
                'name' => $fileContent[1]
            ];
            return true;
        }
        else
        {
            echo "<span class='' >Mot de passe incorrect</span>";
        }
    }
    else
    {
        echo "<span class='' >Ce compte n'existe pas ! Inscrivez-vous <a href='../../../public/visiteur/register.php'>ici</a>.</span>";
    }

    return false;
}