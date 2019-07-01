<?php

namespace App\Pages;

use App\Models\Package;
use App\Routers\MethodNotAllowedException;
use App\Routers\NotFoundException;

class PackagesPage extends FwPage
{
    /**
     * @throws NotFoundException
     */
    protected function index()
    {
        throw new NotFoundException();
    }

    /**
     * @throws NotFoundException
     */
    protected function new()
    {
        throw new NotFoundException();
    }

    protected function create()
    {
        if (Package::findByLookup($this->getParam('name'))) {
            $this->json([
                'error' => 'A package by that name already exists',
                'code'  => 409,
            ], 409);
            return;
        }

        $package = Package::create([
            'name' => $this->getParam('name'),
        ]);

        $this->json([
            'package_id' => $package->getId(),
            'name'       => $package->getData('name'),
        ], 201);
    }

    protected function show(Package $package)
    {
        ini_set('default_mimetype', '');
        $this->json([
            'package_id' => $package->getId(),
            'name'       => $package->getData('name'),
        ]);
    }

    /**
     * @throws NotFoundException
     */
    protected function edit()
    {
        throw new NotFoundException();
    }

    /**
     * @throws MethodNotAllowedException
     */
    protected function update()
    {
        throw new MethodNotAllowedException();
    }

    /**
     * @throws MethodNotAllowedException
     */
    protected function destroy()
    {
        throw new MethodNotAllowedException();
    }
}
