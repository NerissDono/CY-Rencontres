<?php

//fonction qui stock chaque ligne du fichier 'file' dans une case d'un tableau
function getInfo($file)
{
    return file($file, FILE_IGNORE_NEW_LINES);
}

function loginAccount($email, $password)
{
    $email= strtolower($email);
    $dir = "../../../data/users/" . $email;

    if (file_exists($dir))
    {
        $fileContent = getInfo($dir."/profile.txt");
        if (password_verify(password_hash($password, PASSWORD_DEFAULT),$fileContent[4]))
        {
            echo "<span class='' >Connexion reussite</span>";

            //Connexion au compte
            $_SESSION['login'] = $email;
        }
    }
    else
    {
        echo "<span class='' >Ce compte n'existe pas ! Connectez vous <a href='../../../public/visiteur/register.php'>ici<a>.</span>";

    }
}
?>