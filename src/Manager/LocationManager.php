<?php

namespace App\Manager;

use App\Entity\Location;
use App\Entity\LocationType;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use function PHPUnit\Framework\returnArgument;

class LocationManager
{
    private LocationRepository $locationRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->locationRepository = $this->getEntityManager()->getRepository(Location::class);
    }

    public function createLocation(
        LocationType $locationType,
        string       $country,
        string       $province,
        string       $city,
        string       $suburb,
        string       $streetName,
        string       $streetNumber,
        string       $latitude,
        string       $longitude,
        string       $placeId
    ): Location {
        return (new Location())
            ->setLocationType($locationType)
            ->setCountry($country)
            ->setProvince($province)
            ->setCity($city)
            ->setSuburb($suburb)
            ->setStreetName($streetName)
            ->setStreetNumber($streetNumber)
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setPlaceId($placeId);
    }

    public function findLocation(int $id): ?Location
    {
        return $this->getLocationRepository()->find($id);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    private function getLocationRepository(): EntityRepository
    {
        return $this->locationRepository;
    }
}