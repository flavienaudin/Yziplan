<?php
namespace AppBundle\Manager;

use MangoPay;

/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 21/05/2016
 * Time: 16:57
 *
 * Service permettant d'utiliser l'API de MangoPay : https://docs.mangopay.com/api-references/
 */
class MangoPayManager
{
    private $mangoPayApi;

    public function __construct($clientId, $clientPassword, $temporaryFolder, $baseURL)
    {
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $clientId;
        $this->mangoPayApi->Config->ClientPassword = $clientPassword;
        $this->mangoPayApi->Config->TemporaryFolder = $temporaryFolder;

        // Par default mangopay est configuré pour la SandBox, si l'URL de prod est définie, alors on utilise celle de prod.
        if ($baseURL != null && $baseURL != ''){
            $this->mangoPayApi->Config->BaseUrl =$baseURL;
        }
    }

    /**
     * Create Mangopay User
     * @return MangopPayUser $mangoUser
     */
    public function getMangoUser()
    {
        $mangoUser = new MangoPay\UserNatural();
        $mangoUser->PersonType = "NATURAL";
        $mangoUser->FirstName = 'John';
        $mangoUser->LastName = 'Doe';
        $mangoUser->Birthday = 1409735187;
        $mangoUser->Nationality = "FR";
        $mangoUser->CountryOfResidence = "FR";
        $mangoUser->Email = 'john.doe@mail.com';

        //Send the request
        $mangoUser = $this->mangoPayApi->Users->Create($mangoUser);

        return $mangoUser;
    }


    public function testMango()
    {
        $mangoUser1 = new MangoPay\UserNatural();
        $mangoUser1->PersonType = "NATURAL";
        $mangoUser1->FirstName = 'John';
        $mangoUser1->LastName = 'Doe';
        $mangoUser1->Birthday = 1409735187;
        $mangoUser1->Nationality = "FR";
        $mangoUser1->CountryOfResidence = "FR";
        $mangoUser1->Email = 'john.doe@mail.com';

        //Send the request
        $mangoUser1 = $this->mangoPayApi->Users->Create($mangoUser1);

        $mangoUser2 = new MangoPay\UserNatural();
        $mangoUser2->PersonType = "NATURAL";
        $mangoUser2->FirstName = 'John';
        $mangoUser2->LastName = 'Doe';
        $mangoUser2->Birthday = 1409735187;
        $mangoUser2->Nationality = "FR";
        $mangoUser2->CountryOfResidence = "FR";
        $mangoUser2->Email = 'john.doe@mail.com';

        //Send the request
        $mangoUser2 = $this->mangoPayApi->Users->Create($mangoUser2);

        $wallet1 = new MangoPay\Wallet();
        $wallet1->Currency = "EUR";
        $wallet1->Balance = 10;
        $wallet1->Owners = array($mangoUser1);

        return $mangoUser1;
    }
}