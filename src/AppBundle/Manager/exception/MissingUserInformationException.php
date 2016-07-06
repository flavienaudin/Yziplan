<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 05/07/2016
 * Time: 16:36
 */

namespace AppBundle\Manager\exception;


use Symfony\Component\Config\Definition\Exception\Exception;

class MissingUserInformationException extends Exception
{

    const USERABOUTINFORMATION = "UserAboutInformation";
    const USERTYPE = "UserType";
    
    const FIRSTNAME = "FirstName";
    const LASTNAME = "LastName";
    const BIRTHDAY = "Birthday";
    const NATIONALITY = "Nationality";
    const COUNTRYOFRESIDENCE = "CountryOfResidence";
    const EMAIL = "Email";
    
    const BUSiNESSNAME = "BusinessName";
    const LEGALREPRESENTATIVE_FIRSTNAME = "LegalRepresentativeFirstName";
    const LEGALREPRESENTATIVE_LASTNAME = "LegalRepresentativeLastName";
    const LEGALREPRESENTATIVE_BIRTHDAY = "LegalRepresentativeBirthday";
    const LEGALREPRESENTATIVE_NATIONALITY = "LegalRepresentativeNationality";
    const LEGALREPRESENTATIVE_COUNTRYOFRESIDENCE = "LegalRepresentativeCountryOfResidence";
    const LEGALREPRESENTATIVE_EMAIL = "GenericBusinessEmail";
    
    /**
     * @var array
     */
    private
    $missingInformations;

    /**
     * MissingUserAboutInformationException constructor.
     * @param array $missingInformations
     */
    public
    function __construct(array $missingInformations)
    {
        $this->missingInformations = $missingInformations;
    }


    /**
     * @return array
     */
    public
    function getMissingInformations()
    {
        return $this->missingInformations;
    }

    /**
     * @param array $missingInformations
     */
    public
    function setMissingInformations($missingInformations)
    {
        $this->missingInformations = $missingInformations;
    }

}