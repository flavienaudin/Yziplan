<?php
namespace AppBundle\Manager;

use AppBundle\Entity\AppUser;
use AppBundle\Manager\exception\MissingUserInformationException;
use ATUserBundle\Entity\User;
use ATUserBundle\Entity\UserAbout;
use Doctrine\ORM\EntityManager;
use MangoPay;

/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 21/05/2016
 * Time: 16:57
 *
 * Service permettant d'utiliser l'API de MangoPay : https://docs.mangopay.com/api-references/
 * 
 * /!\ l'enregistrement d'un moyen de paiement doit se faire par un appel direct à MangoPay en JS et ne pas passer par nos serveurs
 * 
 */
class MangoPayManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    private $mangoPayApi;

    public function __construct($clientId, $clientPassword, $temporaryFolder, $baseURL, EntityManager $doctrine, AuthorizationCheckerInterface $authorizationChecker)
    {

        // Initialisation MangoPay
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $clientId;
        $this->mangoPayApi->Config->ClientPassword = $clientPassword;
        $this->mangoPayApi->Config->TemporaryFolder = $temporaryFolder;

        // Par default mangopay est configuré pour la SandBox, si l'URL de prod est définie, alors on utilise celle de prod.
        if ($baseURL != null && $baseURL != ''){
            $this->mangoPayApi->Config->BaseUrl =$baseURL;
        }
    }

    /******************************************************************************************************************************
     *                                           Mango User
     ******************************************************************************************************************************/

    /**
     * @param $mangoUserId
     * @return MangoPay\UserLegal|MangoPay\UserNatural
     */
    public function getMangoUserById($mangoUserId){
        $mangoUser = $this->mangoPayApi->Users->Get($mangoUserId);
        return $mangoUser;
    }

    /**
     * Verifie si l'on possède les données necessaire pour enregistrer un user mangopay et le crée.
     *
     * @return MangopPayUser $mangoUser créé
     * @throws MissingUserInformationException contient un array des champs manquants
     */
    public function CreateMangoUserNatural(User $user)
    {

        $userAbout = $user->getUserAbout();
        if ($userAbout != null){
            if($userAbout->getUserType() == null){
                throw new MissingUserInformationException(array(MissingUserInformationException::USERTYPE));
            }
            if (UserAbout::NATURAL == $userAbout->getUserType()){
                // On verifie s'il manque des champs
                try{
                    $this->CheckUserNaturalInformation($user);
                }catch (MissingUserInformationException $e){
                    throw $e;
                }

                // On crée le user mangopay
                $mangoUser = new MangoPay\UserNatural();
                $mangoUser->PersonType = "NATURAL";
                $mangoUser->FirstName = $userAbout->getFirstName();
                $mangoUser->LastName = $userAbout->getLastName();
                $mangoUser->Birthday = $userAbout->getBirthday()->getTimestamp();
                $mangoUser->Nationality = $userAbout->getNationality();
                $mangoUser->CountryOfResidence = $userAbout->getCountryOfResidence();
                $mangoUser->Email = $user->getEmail();

                $mangoUser = $this->mangoPayApi->Users->Create($mangoUser);
                return $mangoUser;
            }
            else if (UserAbout::LEGAL == $userAbout->getUserType()){
                // On verifie s'il manque des champs
                try{
                    $this->CheckUserLegalInformation($user);
                }catch (MissingUserInformationException $e){
                    throw $e;
                }

                // On crée le user mangopay
                $mangoUser = new MangoPay\UserLegal();
                $mangoUser->PersonType = "LEGAL";
                $mangoUser->Name = $userAbout->getBusinessName();
                $mangoUser->LegalRepresentativeFirstName = $userAbout->getFirstName();
                $mangoUser->LegalRepresentativeLastName = $userAbout->getLastName();
                $mangoUser->LegalRepresentativeBirthday = $userAbout->getBirthday()->getTimestamp();
                $mangoUser->LegalRepresentativeNationality = $userAbout->getNationality();
                $mangoUser->LegalRepresentativeCountryOfResidence = $userAbout->getCountryOfResidence();
                $mangoUser->LegalRepresentativeEmail = $userAbout->getGenericBusinessEmail();

                $mangoUser = $this->mangoPayApi->Users->Create($mangoUser);
                return $mangoUser;
            }
        }
        else{
            throw new MissingUserInformationException(array(MissingUserInformationException::USERABOUTINFORMATION));
        }
    }

    private function CheckUserNaturalInformation(User $user){
        $userAbout = $user->getUserAbout();
        $missingInformation = array();
        if($userAbout->getFirstName() == null){
            array_push($missingInformation, MissingUserInformationException::FIRSTNAME);
        }
        if($userAbout->getLastName() == null){
            array_push($missingInformation, MissingUserInformationException::LASTNAME);
        }
        if($userAbout->getBirthday() == null){
            array_push($missingInformation, MissingUserInformationException::BIRTHDAY);
        }
        if($userAbout->getNationality() == null){
            array_push($missingInformation, MissingUserInformationException::NATIONALITY);
        }
        if($userAbout->getCountryOfResidence() == null){
            array_push($missingInformation, MissingUserInformationException::COUNTRYOFRESIDENCE);
        }
        if($user->getEmail() == null){
            array_push($missingInformation, MissingUserInformationException::EMAIL);
        }
        if (!empty ($missingInformation)){
            throw new MissingUserInformationException($missingInformation);
        }
    }

    private function CheckUserLegalInformation(User $user){
        $userAbout = $user->getUserAbout();
        $missingInformation = array();
        if($userAbout->getBusinessName() == null){
            array_push($missingInformation, MissingUserInformationException::BUSiNESSNAME);
        }
        if($userAbout->getFirstName() == null){
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_FIRSTNAME);
        }
        if($userAbout->getLastName() == null){
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_LASTNAME);
        }
        if($userAbout->getBirthday() == null){
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_BIRTHDAY);
        }
        if($userAbout->getNationality() == null){
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_NATIONALITY);
        }
        if($userAbout->getCountryOfResidence() == null){
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_COUNTRYOFRESIDENCE);
        }
        if($user->getEmail() == null){
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_EMAIL);
        }
        if (!empty ($missingInformation)){
            throw new MissingUserInformationException($missingInformation);
        }
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