<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 11/10/2016
 * Time: 11:14
 */

namespace AppBundle\Envent;

use AppBundle\Entity\User\AppUserEmail;
use Symfony\Component\EventDispatcher\Event;

class AppUserEmailEvent extends Event
{
    /** @var AppUserEmail $appUserEmail */
    private $appUserEmail;

    /**
     * ATUserEmailEvent constructor.
     * @param AppUserEmail $appUserEmail
     */
    public function __construct(AppUserEmail $appUserEmail)
    {
        $this->appUserEmail = $appUserEmail;
    }

    /**
     * @return AppUserEmail
     */
    public function getAppUserEmail()
    {
        return $this->appUserEmail;
    }
}