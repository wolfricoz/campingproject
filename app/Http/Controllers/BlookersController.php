<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use RuntimeException;

class BlookersController extends Controller
{
    /**
     * @throws RuntimeException
     */
    public function store(Request $request): never
    {
        throw new RuntimeException('Blooker Integratie not implemented yet (Not required)');
    }
}
