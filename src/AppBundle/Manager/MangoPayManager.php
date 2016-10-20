<?php
namespace AppBundle\Manager;

use AppBundle\Entity\User\AppUserInformation;
use AppBundle\Manager\exception\MissingUserInformationException;
use AppBundle\Utils\enum\LegalStatus;
use ATUserBundle\Entity\AccountUser;
use Doctrine\ORM\EntityManager;
use MangoPay\Libraries\Exception;
use MangoPay\Libraries\ResponseException;
use MangoPay\MangoPayApi;
use MangoPay\UserLegal;
use MangoPay\UserNatural;
use MangoPay\Wallet;
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
        $this->mangoPayApi = new MangoPayApi();
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
     * @return UserLegal|UserNatural
     */
    public function getMangoUserById($mangoUserId)
    {
        return $this->mangoPayApi->Users->Get($mangoUserId);
    }

    /**
     * Verifie si l'on possède les données necessaire pour enregistrer un user mangopay et le crée.
     *
     * @return UserLegal|UserNatural $mangoUser créé
     * @throws MissingUserInformationException contient un array des champs manquants
     */
    public function createMangoUser(AccountUser $user)
    {
        // Si l'utilisateur existe déjà on le retourne.
        if (!(empty($user->getApplicationUser()->getMangoPayId()))) {
            return $this->getMangoUserById($user->getApplicationUser()->getMangoPayId());
        }

        $appUserInformation = $user->getApplicationUser()->getAppUserInformation();
        if ($appUserInformation != null) {
            if ($appUserInformation->getLegalStatus() == null) {
                throw new MissingUserInformationException(array(MissingUserInformationException::USERTYPE));
            }
            if (LegalStatus::INDIVIDUAL == $appUserInformation->getLegalStatus()) {
                // On verifie s'il manque des champs
                try {
                    $this->checkUserNaturalInformation($user->getApplicationUser()->getAppUserInformation());
                } catch (MissingUserInformationException $e) {
                    throw $e;
                }

                // On crée le user mangopay
                $mangoUser = new UserNatural();
                $mangoUser->PersonType = "NATURAL";
                $mangoUser->FirstName = $appUserInformation->getFirstName();
                $mangoUser->LastName = $appUserInformation->getLastName();
                $mangoUser->Birthday = $appUserInformation->getBirthday()->getTimestamp();
                $mangoUser->Nationality = $appUserInformation->getNationality();
                $mangoUser->CountryOfResidence = $appUserInformation->getLivingCountry();
                $mangoUser->Email = $user->getEmail();


                try {
                    $mangoUser = $this->mangoPayApi->Users->Create($mangoUser);
                    // Mise a jour de l'utilisateur avec son AppId
                    $user->getApplicationUser()->setMangoPayId($mangoUser->Id);

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    return $mangoUser;
                } catch (ResponseException $e) {
                    // handle/log the response exception with code $e->GetCode(), message $e->GetMessage() and error(s) $e->GetErrorDetails()
                    throw $e;
                } catch (Exception $e) {
                    // handle/log the exception $e->GetMessage()
                    throw $e;
                }
            } else if (LegalStatus::ORGANISATION == $appUserInformation->getLegalStatus()) {
                // On verifie s'il manque des champs
                try {
                    $this->checkUserLegalInformation($user);
                } catch (MissingUserInformationException $e) {
                    throw $e;
                }

                // On crée le user mangopay
                $mangoUser = new UserLegal();
                $mangoUser->PersonType = "LEGAL";
                $mangoUser->Name = $appUserInformation->getPublicName();
                $mangoUser->LegalRepresentativeFirstName = $appUserInformation->getFirstName();
                $mangoUser->LegalRepresentativeLastName = $appUserInformation->getLastName();
                $mangoUser->LegalRepresentativeBirthday = $appUserInformation->getBirthday()->getTimestamp();
                $mangoUser->LegalRepresentativeNationality = $appUserInformation->getNationality();
                $mangoUser->LegalRepresentativeCountryOfResidence = $appUserInformation->getLivingCountry();
                $mangoUser->LegalRepresentativeEmail = $appUserInformation->getApplicationUser()->getAccountUser()->getEmail();

                try {
                    $mangoUser = $this->mangoPayApi->Users->Create($mangoUser);
                    // Mise a jour de l'utilisateur avec son AppId
                    $user->getApplicationUser()->setMangoPayId($mangoUser->Id);

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    return $mangoUser;
                } catch (ResponseException $e) {
                    // handle/log the response exception with code $e->GetCode(), message $e->GetMessage() and error(s) $e->GetErrorDetails()
                    throw $e;
                } catch (Exception $e) {
                    // handle/log the exception $e->GetMessage()
                    throw $e;
                }
            }
        } else {
            throw new MissingUserInformationException(array(MissingUserInformationException::USERABOUTINFORMATION));
        }
    }

    private function checkUserNaturalInformation(AppUserInformation $appUserInformation)
    {
        $missingInformation = array();
        if ($appUserInformation->getFirstName() == null) {
            array_push($missingInformation, MissingUserInformationException::FIRSTNAME);
        }
        if ($appUserInformation->getLastName() == null) {
            array_push($missingInformation, MissingUserInformationException::LASTNAME);
        }
        if ($appUserInformation->getBirthday() == null) {
            array_push($missingInformation, MissingUserInformationException::BIRTHDAY);
        }
        if ($appUserInformation->getNationality() == null) {
            array_push($missingInformation, MissingUserInformationException::NATIONALITY);
        }
        if ($appUserInformation->getLivingCountry() == null) {
            array_push($missingInformation, MissingUserInformationException::COUNTRYOFRESIDENCE);
        }
        if ($appUserInformation->getApplicationUser()->getAccountUser()->getEmail() == null) {
            array_push($missingInformation, MissingUserInformationException::EMAIL);
        }
        if (!empty ($missingInformation)) {
            throw new MissingUserInformationException($missingInformation);
        }
    }

    private function checkUserLegalInformation(AppUserInformation $appUserInformation)
    {
        $missingInformation = array();
        if ($appUserInformation->getPublicName() == null) {
            array_push($missingInformation, MissingUserInformationException::BUSiNESSNAME);
        }
        if ($appUserInformation->getFirstName() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_FIRSTNAME);
        }
        if ($appUserInformation->getLastName() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_LASTNAME);
        }
        if ($appUserInformation->getBirthday() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_BIRTHDAY);
        }
        if ($appUserInformation->getNationality() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_NATIONALITY);
        }
        if ($appUserInformation->getLivingCountry() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_COUNTRYOFRESIDENCE);
        }
        if ($appUserInformation->getApplicationUser()->getAccountUser()->getEmail() == null) {
            array_push($missingInformation, MissingUserInformationException::LEGALREPRESENTATIVE_EMAIL);
        }
        if (!empty ($missingInformation)) {
            throw new MissingUserInformationException($missingInformation);
        }
    }


    /**
     * @param $mangoWalletId
     * @return Wallet
     * @throws Exception
     * @throws ResponseException
     */
    public function getMangoWalletById($mangoWalletId)
    {
        try {
            $Wallet = $this->mangoPayApi->Wallets->Get($mangoWalletId);
            return $Wallet;

        } catch (ResponseException $e) {
            // handle/log the response exception with code $e->GetCode(), message $e->GetMessage() and error(s) $e->GetErrorDetails()
            throw $e;
        } catch (Exception $e) {
            // handle/log the exception $e->GetMessage()
            throw $e;
        }
    }

    /**
     * @param Wallet $atWallet
     * @param string $currency
     * @return Wallet
     * @throws Exception
     * @throws ResponseException
     */
    public function createMangoWallet(\AppBundle\Entity\Payment\Wallet $atWallet, $currency = "EUR")
    {
        // Si l'utilisateur existe déjà on le retourne.
        if ($atWallet->getMangoPayEWalletId() != null) {
            return $this->getMangoWalletById($atWallet->getMangoPayEWalletId());
        }

        try {
            $Wallet = new Wallet();
            $Wallet->Tag = $atWallet->getId();
            $Wallet->Owners = $atWallet->getEventInvitation()->getApplicationUser()->getMangoPayId();
            // TODO $description = $atWallet->getEventInvitation()->getModule()->getName();
            /*if ($description != null) {
                $Wallet->Description = $description;
            } else {*/
            $Wallet->Description = "No description";
            //}
            $Wallet->Currency = $currency;
            $Wallet = $this->mangoPayApi->Wallets->Create($Wallet);

            $atWallet->setMangoPayEWalletId($Wallet->Id);

            $this->entityManager->persist($atWallet);
            $this->entityManager->flush();

            return $Wallet;

        } catch (ResponseException $e) {
            // handle/log the response exception with code $e->GetCode(), message $e->GetMessage() and error(s) $e->GetErrorDetails()
            throw $e;
        } catch (Exception $e) {
            // handle/log the exception $e->GetMessage()
            throw $e;
        }
    }
}