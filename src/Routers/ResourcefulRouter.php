<?php

namespace App\Routers;

use App\Pages\ResourcefulPage;
use App\RouterInterface;
use The\Request;
use The\Response;

class ResourcefulRouter implements RouterInterface
{
    /**
     * @param Response $response
     * @param Request $request
     * @param string $request_uri
     * @return bool
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * @throws InvalidPageException
     */
    public function route(Response $response, Request $request, string $request_uri)
    {
        $path = trim(preg_replace('#/+#', '/', $request_uri), '/');

        $pieces = explode('/', $path);
        if (count($pieces) % 2 !== 0) {
            $pieces[] = null;
        }

        $pairs = array_chunk($pieces, 2);
        [$last_model, $last_id] = end($pairs);

        if ($last_model === 'edit' && !$last_id) {
            array_pop($pairs);
        }

        $models_criteria = [];
        $page_class = '\App\Pages\\';
        foreach ($pairs as [$model, $id]) {
            $route_name = str_replace('-', '', ucwords($model, '-'));
            $page_class .= $route_name;
            $model_name = $this->depluralize($route_name);

            if ($id) {
                $models_criteria[] = [
                    'name' => $model_name,
                    'id'   => $id,
                ];
            }
        }

        $page_class .= 'Page';
        if (!class_exists($page_class)) {
            return false;
        }

        $models = $this->findModels($models_criteria);
        $action = $this->getAction($request->getMethod(), $last_model, $last_id);

        $page = call_user_func([$page_class, 'factory']);
        $page->__invoke($response, $request, $action, $models);

        return true;
    }

    /**
     * @param string $method
     * @param string $model_name
     * @param mixed $id
     * @return string
     * @throws MethodNotAllowedException
     */
    private function getAction(string $method, string $model_name, ?string $id)
    {
        if ($model_name === 'edit' && !$id) {
            $actions = ['GET' => 'edit'];
        } elseif (!$id) {
            $actions = [
                'GET'  => 'index',
                'POST' => 'create',
            ];
        } elseif ($id === 'new') {
            $actions = ['GET' => 'new'];
        } else {
            $actions = [
                'GET'    => 'show',
                'PATCH'  => 'update',
                'PUT'    => 'update',
                'DELETE' => 'destroy',
            ];
        }

        if (array_key_exists($method, $actions)) {
            return $actions[$method];
        }

        throw new MethodNotAllowedException();
    }

    /**
     * @param array $models_criteria
     * @return array
     * @throws NotFoundException
     */
    private function findModels($models_criteria)
    {
        $last_model = null;
        $models = [];
        foreach ($models_criteria as $criteria) {
            ['name' => $name, 'id' => $id] = $criteria;

            $possible_callbacks = [
                [$last_model, "find{$name}ByLookup"],
                [$last_model, "find{$name}"],
                ["\\App\\Models\\{$name}", "findByLookup"],
                ["\\App\\Models\\{$name}", "find"],
            ];

            $callback = null;
            foreach ($possible_callbacks as $possible_callback) {
                if (is_callable($possible_callback)) {
                    $callback = $possible_callback;
                    break;
                }
            }
            if (!$callback) {
                throw new \LogicException('A valid lookup method could not be found.');
            }

            $model = call_user_func($callback, $id);
            if (!$model) {
                throw new NotFoundException();
            }

            $models[] = $last_model = $model;
        }

        return $models;
    }

    private function depluralize($model_name)
    {
        return preg_replace('/s$/', '', $model_name);
    }
}
