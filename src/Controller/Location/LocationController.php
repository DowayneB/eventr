<?php

namespace App\Controller\Location;

use App\Entity\LocationType;
use App\Manager\LocationManager;
use App\Repository\LocationTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route("/api/location")]
class LocationController extends AbstractController
{
    #[Route(null, methods: ["POST"])]
    public function createLocation(
        Request                $request,
        LocationManager        $locationManager,
        EntityManagerInterface $entityManager,
        LocationTypeRepository $locationTypeRepository,
        ValidatorInterface     $validator,
        SerializerInterface     $serializer
    ): Response
    {
        $constraints = new Assert\Collection([
            'location_type_id' => new Assert\Type('integer'),
            'country' => new Assert\Type('string'),
            'province' => new Assert\Type('string'),
            'city' => new Assert\Type('string'),
            'suburb' => new Assert\Type('string'),
            'street_name' => new Assert\Type('string'),
            'street_number' => new Assert\Type('string'),
            'latitude' => new Assert\Type('float'),
            'longitude' => new Assert\Type('float'),
            'place_id' => new Assert\Type('string'),
        ]);

        $violations = $validator->validate($request->getPayload()->all(),$constraints);

        if (count($violations) > 0) {
            if (count($violations) > 0) {
                $errors = array_map(function ($violation) {
                    return [
                        'field' => trim($violation->getPropertyPath(), '[]'),
                        'message' => $violation->getMessage(),
                    ];
                },[...$violations]);

                return new JsonResponse(
                    $serializer->serialize(['errors' => $errors], 'json'),
                    Response::HTTP_BAD_REQUEST,
                    [],true
                );
            }
        }

        $locationType = $locationTypeRepository->find($request->getPayload()->get( 'location_type_id'));

        if (!$locationType instanceof LocationType) {
            return new JsonResponse(
                $serializer->serialize(['errors' => [
                    'field' => 'location_type_id',
                    'message' => "Location type with id {$locationType->getId()} not found",
                ]], 'json'),
                Response::HTTP_NOT_FOUND,
                [],true
            );
        }

        $location = $locationManager->createLocation(
            $locationType,
            $request->getPayload()->get('country'),
            $request->getPayload()->get('province'),
            $request->getPayload()->get( 'city'),
            $request->getPayload()->get( 'suburb'),
            $request->getPayload()->get( 'street_name'),
            $request->getPayload()->get( 'street_number'),
            $request->getPayload()->get( 'latitude'),
            $request->getPayload()->get( 'longitude'),
            $request->getPayload()->get( 'place_id')
        );

        $entityManager->persist($location);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize(['errors' => [
                'field' => 'location_type',
                'message' => "Location type with id {$locationType->getId()} not found",
            ]], 'json'),
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl(
                    'api_location_get',[
                        'location_id' => $location->getId()
                    ]
                ),
            ],true
        );

    }

    #[Route(
        "/{location_id}",
        name: 'api_location_get',
        methods: ["GET"]
    )]
    public function getLocation(
        int $locationId,
        LocationManager $locationManager,
        SerializerInterface $serializer,
    ): Response
    {
        return new JsonResponse(
            $serializer->serialize(['location' => $locationManager->findLocation($locationId)], 'json'),
            Response::HTTP_OK,
            [], true
        );
    }
}