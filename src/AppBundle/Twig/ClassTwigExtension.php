<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 23/03/2017
 * Time: 18:44
 */

namespace AppBundle\Twig;


class ClassTwigExtension extends \Twig_Extension
{
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('instanceof', array($this, 'isInstanceof'))
        ];
    }

    /**
     * @param $var
     * @param $instance
     * @return bool
     */
    public function isInstanceof($var, $instance)
    {
        return $var instanceof $instance;
    }

    public function getName()
    {
        return 'class_twig_extension';
    }
}