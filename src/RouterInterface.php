<?php

namespace App;

use App\Routers\MethodNotAllowedException;
use App\Routers\NotFoundException;
use The\Request;
use The\Response;

interface RouterInterface
{
    /**
     * @param Response $response
     * @param Request $request
     * @param string $request_uri
     * @return bool
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     */
    public function route(Response $response, Request $request, string $request_uri);
}
