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
            $emailLen = strlen($email);
            if ($emailLen >= 9) {
                $value = substr($email, 0, $emailLen - 6) . "***" . substr($email, -3, 3);
            } else {
                $first = substr($email, 0, min(3, max($emailLen - 1, 1)));
                $value = $first . "***";
                if ($emailLen >= 7) {
                    substr($email, 7, $emailLen - 6);
                }
            }
        }
        return $value;
    }
}