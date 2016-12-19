<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 11/10/2016
 * Time: 11:02
 */

namespace ATUserBundle;


class ATUserEvents
{

    /**
     * The OAUTH_REGISTRATION_SUCCESS event occurs when the load of User through OAuth connexion need to create a new account AND the email used is attached (AppUserAccount) to an existing account
     * without being the main email of the AccountUser.
     *
     * This event allows you to update the user after its successfully registration by Oauth
     *
     * @Event("AppBundle\Event\AppUserEmailEvent")
     */
    const OAUTH_REGISTRATION_SUCCESS = 'at_user.oauth_registration.success';
}