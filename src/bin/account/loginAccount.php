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
        if (password_verify($password, $fileContent[6]))
        {
            // affecte les variables de session
            $_SESSION["id"] = $fileContent[0];
            $_SESSION["gender"] = $fileContent[1];
            $_SESSION["email"] = $email;
            $_SESSION["password"] = $password;
            $_SESSION["birthdate"] = $fileContent[4];
            $_SESSION["lastname"] = $fileContent[2];
            $_SESSION["name"] = $fileContent[3];

            return true;
        }
        else
        {
            echo "<span class='err-message'>Mot de passe incorrect</span>";
        }
    }
    else
    {
        echo "<span class='err-message'>Ce compte n'existe pas ! Inscrivez-vous <a href='../../../public/visiteur/register.php'> ici</a>.</span>";
    }

    return false;
}