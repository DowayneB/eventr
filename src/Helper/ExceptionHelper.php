<?php

namespace App\Helper;

use App\Exception\ActionProhibitedException;
use App\Exception\NotFoundException;

final class ExceptionHelper
{
    public static function eventNotFoundException(): NotFoundException
    {
        return new NotFoundException("Event not found.");
    }

    public static function alreadyActionedException(): ActionProhibitedException
    {
        return new ActionProhibitedException("This change has already been made");
    }
}