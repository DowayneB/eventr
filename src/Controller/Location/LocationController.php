<?php

namespace App\Controller\Location;

use App\Controller\EventrController;
use App\Manager\LocationManager;
use App\Repository\LocationTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/api/location")]
class LocationController extends EventrController
{
    #[Route(null, methods: ["POST"])]
    public function createLocation(
        Request                $request,
        LocationManager        $locationManager,
        EntityManagerInterface $entityManager,
        LocationTypeRepository $locationTypeRepository
    ): Response
    {
        $locationType = $locationTypeRepository->find($this->get($request, 'location_type_id'));

        $location = $locationManager->createLocation(
            $locationType,
            $this->get($request, 'country'),
            $this->get($request, 'province'),
            $this->get($request, 'city'),
            $this->get($request, 'suburb'),
            $this->get($request, 'street_name'),
            $this->get($request, 'street_number'),
            $this->get($request, 'latitude'),
            $this->get($request, 'longitude'),
            $this->get($request, 'place_id')
        );

        $entityManager->persist($location);
        $entityManager->flush();

        return $this->makeSerializedResponse([
            'location' => $location
        ]);

    }
}