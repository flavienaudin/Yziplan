<?php
namespace AppBundle\Manager;

use AppBundle\Entity\Payment\Wallet;
use AppBundle\Manager\exception\MissingUserInformationException;
use ATUserBundle\Entity\AccountUser;
use ATUserBundle\Entity\UserAbout;
use Doctrine\ORM\EntityManager;
use MangoPay;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
        if ($baseURL != null && $baseURL != '') {
            $this->mangoPayApi->Config->BaseUrl = $baseURL;
        }
    }

    /******************************************************************************************************************************
     *                                           Mango User
     ******************************************************************************************************************************/

    /**
     * @param $mangoUserId
     * @return MangoPay\UserLegal|MangoPay\UserNatural
     */
    public function getMangoUserById($mangoUserId)
    {
        $mangoUser = $this->mangoPayApi->Users->Get($mangoUserId);
        return $mangoUser;
    }

    /**
     * Verifie si l'on possède les données necessaire pour enregistrer un user mangopay et le crée.
     *
     * @return MangopPayUser $mangoUser créé
     * @throws MissingUserInformationException contient un array des champs manquants
     */
    public function createMangoUser(AccountUser $user)
    {
        // Si l'utilisateur existe déjà on le retourne.
        if ($user->getApplicationUser()->getMangoPayUserId() != null) {
            return $this->getMangoUserById($user->getApplicationUser()->getMangoPayUserId());
        }

        $userAbout = $user->getUserAbout();
        if ($userAbout != null) {
            if ($userAbout->getUserType() == null) {
                throw new MissingUserInformationException(array(MissingUserInformationException::USERTYPE));
            }
            if (UserAbout::NATURAL == $userAbout->getUserType()) {
                // On verifie s'il manque des champs
                try {
                    $this->checkUserNaturalInformation($user);
                } catch (MissingUserInformationException $e) {
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


                try {
                    $mangoUser = $this->mangoPayApi->Users->Create($mangoUser);
                    // Mise a jour de l'utilisateur avec son AppId
                    $user->getApplicationUser()->setMangoPayUserId($mangoUser->Id);

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    return $mangoUser;
                } catch (MangoPay\Libraries\ResponseException $e) {
                    // handle/log the response exception with code $e->GetCode(), message $e->GetMessage() and error(s) $e->GetErrorDetails()
                    throw $e;
                } catch (MangoPay\Libraries\Exception $e) {
                    // handle/log the exception $e->GetMessage()
                    throw $e;
                }
            } else if (UserAbout::LEGAL == $userAbout->getUserType()) {
                // On verifie s'il manque des champs
                try {
                    $this->checkUserLegalInformation($user);
                } catch (MissingUserInformationException $e) {
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

                try {
                    $mangoUser = $this->mangoPayApi->Users->Create($mangoUser);
                    // Mise a jour de l'utilisateur avec son AppId
                    $user->getApplicationUser()->setMangoPayUserId($mangoUser->Id);

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    return $mangoUser;
                } catch (MangoPay\Libraries\ResponseException $e) {
                    // handle/log the response exception with code $e->GetCode(), message $e->GetMessage() and error(s) $e->GetErrorDetails()
                    throw $e;
                } catch (MangoPay\Libraries\Exception $e) {
                    // handle/log the exception $e->GetMessage()
                    throw $e;
                }
            }
        } else {
            throw new MissingUserInformationException(array(MissingUserInformationException::USERABOUTINFORMATION));
        }
    }

    private function checkUserNaturalInformation(AccountUser $user)
    {
        $userAbout = $user->getUserAbout();
        $missingInformation = array();
        if ($userAbout->getFirstName() == null) {
            array_push($missingInformation, MissingUserInformationException::FIRSTNAME);
        }
        if ($userAbout->getLastName() == null) {
            array_push($missingInformation, MissingUserInformationException::LASTNAME);
        }
        if ($userAbout->getBirthday() == null) {
            array_push($missingInformation, MissingUserInformationException::BIRTHDAY);
        }
        if ($userAbout->getNationality() == null) {
            array_push($missingInformation, MissingUserInformationException::NATIONALITY);
        }
        if ($userAbout->getCountryOfResidence() == null) {
            array_push($missingInformation, MissingUserInformationException::COUNTRYOFRESIDENCE);
        }
        if ($user->getEmail() == null) {
            array_push($missingInformation, MissingUserInformationException::EMAIL);
        }
        if (!empty ($missingInformation)) {
            throw new MissingUserInformationException($missingInformation);
        }
    }

    private function checkUserLegalInformation(AccountUser $user)
    {
        $userAbout = $user->getUserAbout();
        $missingInformation = array();
        if ($userAbout->getBusinessName() == null) {
            array_push($missingInformation, MissingUserInformationException::BUSiNESSNAME);
        }
        if ($userAbout->getFirstName() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_FIRSTNAME);
        }
        if ($userAbout->getLastName() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_LASTNAME);
        }
        if ($userAbout->getBirthday() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_BIRTHDAY);
        }
        if ($userAbout->getNationality() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_NATIONALITY);
        }
        if ($userAbout->getCountryOfResidence() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_COUNTRYOFRESIDENCE);
        }
        if ($user->getEmail() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_EMAIL);
        }
        if (!empty ($missingInformation)) {
            throw new MissingUserInformationException($missingInformation);
        }
    }


    /**
     * @param $mangoWalletId
     * @return MangoPay\Wallet
     * @throws MangoPay\Libraries\Exception
     * @throws MangoPay\Libraries\ResponseException
     */
    public function getMangoWalletById($mangoWalletId){
        try {
            $Wallet = $this->mangoPayApi->Wallets->Get($mangoWalletId);
            return $Wallet;

        } catch(MangoPay\Libraries\ResponseException $e) {
            // handle/log the response exception with code $e->GetCode(), message $e->GetMessage() and error(s) $e->GetErrorDetails()
            throw $e;
        } catch(MangoPay\Libraries\Exception $e) {
            // handle/log the exception $e->GetMessage()
            throw $e;
        }
    }

    /**
     * @param Wallet $atWallet
     * @param string $currency
     * @return MangoPay\Wallet
     * @throws MangoPay\Libraries\Exception
     * @throws MangoPay\Libraries\ResponseException
     */
    public function createMangoWallet(Wallet $atWallet, $currency = "EUR")
    {
        // Si l'utilisateur existe déjà on le retourne.
        if ($atWallet->getMangoPayEWalletId() != null) {
            return $this->getMangoWalletById($atWallet->getMangoPayEWalletId());
        }
        
        try {
            $Wallet = new \MangoPay\Wallet();
            $Wallet->Tag = $atWallet->getId();
            $Wallet->Owners = $atWallet->getEventInvitation()->getEventInvitation()->getApplicationUser()->getMangoPayUserId();
            $description = $atWallet->getEventInvitation()->getModule()->getName();
            if($description!= null){
                $Wallet->Description = $description;
            }else{
                $Wallet->Description = "No description";
            }
            $Wallet->Currency = $currency;
            $Wallet = $this->mangoPayApi->Wallets->Create($Wallet);

            $atWallet->setMangoPayEWalletId($Wallet->Id);

            $this->entityManager->persist($atWallet);
            $this->entityManager->flush();

            return $Wallet;

        } catch(MangoPay\Libraries\ResponseException $e) {
            // handle/log the response exception with code $e->GetCode(), message $e->GetMessage() and error(s) $e->GetErrorDetails()
            throw $e;
        } catch(MangoPay\Libraries\Exception $e) {
            // handle/log the exception $e->GetMessage()
            throw $e;
        }
    }

    
}