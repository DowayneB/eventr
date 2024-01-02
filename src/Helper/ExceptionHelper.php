<?php

namespace App\Helper;

use App\Exception\ActionProhibitedException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;

abstract class ExceptionHelper
{
    public static function eventNotFoundException(): NotFoundException
    {
        return new NotFoundException("Event not found.");
    }

    public static function alreadyActionedException(): ActionProhibitedException
    {
        return new ActionProhibitedException("This change has already been made");
    }

    public static function validationFieldRequiredException(string $field) : ValidationException
    {
        return new ValidationException("Field \"{$field}\" must be supplied");
    }

    public static function validationFieldIncorrectException(string $message = ""): ValidationException
    {
        return new ValidationException($message);
    }
}