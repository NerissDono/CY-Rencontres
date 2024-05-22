<?php 

//Fonction qui permet de modifier le profil public (visible par tout le monde) d'un utilisateur

function editPublicProfile($nickname=" ", $gender=" ", $birthdate=" ", $height=" ", $bio=" ", $Be)
{
    $dir = "../../../data/users/" . $_SESSION('email');
    $fileContent = getInfo($dir . "/publicprofile.txt");
}