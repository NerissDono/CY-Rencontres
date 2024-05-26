<?php

//Fonction qui vérifie qu'une checkbox est bien sauvgardée et retourne "saved" si c'est le cas

function saved($val, $array)
{
    if (in_array($val, $array))
    {
        return "saved";
    }
    return "";
}