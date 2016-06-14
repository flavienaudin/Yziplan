<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 13/06/2016
 * Time: 10:52
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\AppUser;
use AppBundle\Entity\Event;
use AppBundle\Entity\Module;
use AppBundle\Entity\module\ExpenseModule;
use AppBundle\Entity\module\ExpenseProposal;
use ATUserBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEventData implements FixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Get our userManager, you must implement `ContainerAwareInterface`
        $userManager = $this->container->get('fos_user.user_manager');

        /** @var $userprincipal User */
        $userprincipal = $userManager->createUser();
        $userprincipal->setUsername('test');
        $userprincipal->setEmail('test@test.com');
        $userprincipal->setPlainPassword('test');
        $userprincipal->setEnabled(true);
        // Update the user
        $userManager->updateUser($userprincipal, true);
        
        $appUserPrincipal = new AppUser();
        $appUserPrincipal->setUser($userprincipal);
        $userprincipal->setAppUser($appUserPrincipal);

        /** @var $userInvite User */
        $userInvite = $userManager->createUser();
        $userInvite->setUsername('test');
        $userInvite->setEmail('test@test.com');
        $userInvite->setPlainPassword('test');
        $userInvite->setEnabled(true);
        // Update the user
        $userManager->updateUser($userInvite, true);

        $appUserInvite = new AppUser();
        $appUserInvite->setUser($userInvite);
        $userInvite->setAppUser($appUserInvite);
        
        //Event
        $event = new Event();
        $event->setName("Test d'evenement");
        
        //Module
        $module = new Module();
        $module->setName("Test Expense Module");
        $module->setDescription("Ceci est une super description.
        
        Avec un saut à la ligne!");
        $module->setEvent($event);
        $event->addModule($module);
        
        //Module specifique pour les depenses
        $expenseModule = new ExpenseModule();
        $expenseModule->setModule();
        $expenseModule->setModule($module);
        $module->addExpenseModule($expenseModule);
        
        $expenseProposal = new ExpenseProposal();
        $expenseProposal->setName("Restaurant");
        $expenseProposal->setAmount(12);
        $expenseProposal->setExpenseDate(new \DateTime());
        $expenseProposal->setCreator($appUserPrincipal);
        $expenseProposal->setPayer($appUserPrincipal);
            
    }
}