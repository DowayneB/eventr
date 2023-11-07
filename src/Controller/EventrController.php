<?php

namespace App\Controller;

use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventrController extends AbstractController
{
    protected SerializerInterface $serializer;
    public function __construct(
        SerializerInterface $serializer,
    )
    {
        $this->serializer = $serializer;
    }

    protected function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }
}