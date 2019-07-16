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

        // smax-age is respected by reverse proxies (Cloudflare) rather than the browser
        //      60*60*24*365=31536000
        // max-age is respected by browsers
        //      14400=60*60*4=4 hours
        $response->withHeader('Cache-Control', 'smax-age=31536000');
        $response->withHeader('Content-Type', $asset->getData('content_type'));
        $response->write($asset->getBlob()->getContent());
    }
}
