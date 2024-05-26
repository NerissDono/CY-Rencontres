<?php


function loginAdmin($email, $password)
{
    $email = strtolower($email);
    $dir = "../../data/users/" . $email;

    if (file_exists($dir))
    {
        if ($password == "password123")
        {
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