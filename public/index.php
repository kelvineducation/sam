<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Pages\AssetsPage;
use App\RouterContext;
use App\Routers\ExplicitRouter;
use App\Routers\ResourcefulRouter;
use The\ApiContext;
use The\App;

$routers = [
    new ExplicitRouter([
        '/assets/:package/:version/:path' => AssetsPage::class,
    ]),
    new ResourcefulRouter(),
];

App::run(RouterContext::init($routers, new ApiContext()));
