<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 10/03/2017
 * Time: 17:12
 */

namespace AppBundle\Manager;

use AppBundle\Entity\ActivityDirectory\Activity;
use AppBundle\Entity\Utils\Geo\City;
use AppBundle\Entity\Utils\Geo\Department;
use AppBundle\Entity\Utils\Geo\Region;
use AppBundle\Repository\Utils\Geo\CityRepository;
use AppBundle\Repository\Utils\Geo\DepartmentRepository;
use Doctrine\ORM\EntityManager;

class UtilsManager
{

    /** @var EntityManager */
    private $entityManager;


    public function __construct(EntityManager $doctrine)
    {
        $this->entityManager = $doctrine;
    }

    public function getPlaceAutocompleteResult($pattern)
    {
        $result = array();
        /**
         * @var $region Region
         */
        foreach ($this->entityManager->getRepository(Region::class)->getPlaceByStartName($pattern) as $region) {
            $result[] = array('value' => $region->getName(), 'data' => 'r' . $region->getId());
        }
        /**
         * @var $department Department
         */
        foreach ($this->entityManager->getRepository(Department::class)->getPlaceByStartName($pattern) as $department) {
            $result[] = array('value' => $department->getName() . ' (' . $department->getNumber() . ')', 'data' => 'd' . $department->getId());
        }
        /**
         * @var $city City
         */
        foreach ($this->entityManager->getRepository(City::class)->getPlaceByStartName($pattern) as $city) {
            $result[] = array('value' => $city->getName() . ' (' . $city->getPostalCode() . ')', 'data' => 'c' . $city->getId());
        }
        return $result;
    }

}