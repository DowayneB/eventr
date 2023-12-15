<?php

namespace App\Manager;

use App\Repository\StatusRepository;

class StatusManager
{
    private StatusRepository $statusRepository;
    public function __construct(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    public function getStatus(int $id)
    {
        return $this->getRepository()->find($id);
    }

    private function getRepository(): StatusRepository
    {
        return $this->statusRepository;
    }
}