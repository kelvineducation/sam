<?php

namespace App\Pages;

use App\Models\Asset;
use App\Routers\NotFoundException;
use The\Request;
use The\Response;

class AssetsPage extends FwPage
{
    public function __invoke(Response $response, Request $request, string $action, array $vars = [])
    {
        parent::__invoke($response, $request, $action, array_merge([$response], $vars));
    }

    public function invoke(Response $response, $package, $version, $path_path)
    {
        $asset = Asset::findByRequestPath($package, $version, $path_path);

        if (!$asset) {
            throw new NotFoundException();
        }

        $response->withHeader('Content-Type', $asset->getData('content_type'));
        $response->write($asset->getBlob()->getContent());
    }
}
