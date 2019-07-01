<?php

namespace App\Routers;

use App\Pages\BasicPage;
use App\Pages\FwPage;
use App\RouterInterface;
use The\Request;
use The\Response;

class ExplicitRouter implements RouterInterface
{
    /**
     * @var array
     */
    private $routes;

    /**
     * @param array $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @param Response $response
     * @param Request $request
     * @param string $request_uri
     * @return bool
     */
    public function route(Response $response, Request $request, string $request_uri)
    {
        $path = trim(preg_replace('#/+#', '/', $request_uri), '/');

        foreach ($this->routes as $pattern => $page_class) {
            $quoted = preg_quote($pattern, '/');
            $regex = preg_replace(
                ['/\\\:([a-z\-]+)\\\/', '/\\\:([a-z\-]+)/'],
                ['(?P<\\1>[^\/]*?)\\',  '(?P<\\1>.*?)'],
                $quoted
            );

            if (preg_match("/^{$regex}[\/]?$/", "/{$path}/", $matches)) {
                $params = array_map(function ($match) {
                    return trim($match, '/');
                }, array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));

                $page = call_user_func([$page_class, 'factory']);
                $page->__invoke($response, $request, 'invoke', $params);

                return true;
            }
        }

        return false;
    }
}
