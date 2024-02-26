<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;

trait JsonRequest
{
    public function get(
        Request $request,
        string $field
    ) {
        $request = $request->toArray();
        if (array_key_exists($field, $request)) {
            return $request[$field];
        }
        return null;
    }
}