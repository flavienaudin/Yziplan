<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 30/01/2017
 * Time: 11:28
 */

namespace AppBundle\Utils;


class StringUtils
{


    /**
     * Renvoi l'email obfusqué à partir de la partie avant le @
     * @param $email string email to obfuscate
     * @return string|null
     */
    public static function getObfuscatedEmail($email)
    {
        $value = null;
        if (!empty($email) && is_string($email)) {
            if (($pos = strpos($email, '@')) !== false) {
                $email = substr($email, 0, $pos);
            }
            $value = "";
            $emailLength = strlen($email);
            $chaine = "abcdefghijklmnpqrstuvwxy123456789";
            $chaineLength = strlen($chaine);
            srand((double)microtime() * 1000000);
            for ($i = 0; $i < 3; $i++) {
                if ($i < $emailLength) {
                    $value .= $email[$i];
                } else {
                    $value .= $chaine[rand() % $chaineLength];
                }
            }
            $value .= "***";
        }
        return $value;
    }
}