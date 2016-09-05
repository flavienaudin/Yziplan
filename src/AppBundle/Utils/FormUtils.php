<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 05/09/2016
 * Time: 17:30
 */

namespace AppBundle\Utils;


use Symfony\Component\Form\FormError;

class FormUtils
{
    /**
     * Give the full name of a form including parents' names (as it is displayed in attributes "name" of input field)
     *
     * @param FormError $formError The "form" in error
     * @return mixed|string
     */
    public static function getFullFormErrorFieldName(FormError $formError)
    {
        $origin = $formError->getOrigin();
        $names = array();
        $names[] = $origin->getName();
        while (($origin = $origin->getParent()) != null) {
            $names[] = $origin->getName();
        }
        $name = $names[count($names) - 1];
        for ($i = count($names) - 2; $i >= 0; $i--) {
            $name .= "[$names[$i]]";
        }
        return $name;
    }
}