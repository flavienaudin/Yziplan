<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 12/10/2016
 * Time: 16:30
 */

namespace AppBundle;


class AppEvents
{
    /**
     * The APPUSEREMAIL_ADD_SUCCESS event occurs when the creation of a new addAppUserEmail form is submitted successfully.
     *
     * This event allows you to set the response instead of using the default one.
     *
     * @Event("FOS\UserBundle\Event\FormEvent")
     */
    const APPUSEREMAIL_ADD_SUCCESS = 'app.appuseremail_add.success';
}