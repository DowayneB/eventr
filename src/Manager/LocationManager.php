<?php

namespace App\Manager;

use App\Entity\Location;
use App\Entity\LocationType;
use function PHPUnit\Framework\returnArgument;

class LocationManager
{

    public function createLocation(
        LocationType $locationType,
        string $country,
        string $province,
        string $city,
        string $suburb,
        string $streetName,
        string $streetNumber,
        string $latitude,
        string $longitude,
        string $placeId
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
}