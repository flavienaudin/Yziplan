<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 06/01/2016
 * Time: 19:47
 */

namespace AppBundle\Manager;


class GenerateursToken
{
    const MOTDEPASSE_LONGUEUR = 10;
    const TOKEN_LONGUEUR = 9;

    function random($car)
    {
        $string = "";
        $chaine = "abcdefghijklmnpqrstuvwxyABCDEFGHIJKLMNPQRSTUVWXY123456789";
        srand((double)microtime() * 1000000);
        for ($i = 0; $i < $car; $i++) {
            $string .= $chaine[rand() % strlen($chaine)];
        }
        return $string;
    }
}