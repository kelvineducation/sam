<?php

namespace App\Pages;

use App\Routers\NotFoundException;
use function The\json;
use function The\html;
use function The\path;
use function The\redirect;
use The\Form;
use The\Request;
use The\Response;

abstract class FwPage
{
    protected $vars;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Request
     */
    private $request;

    private $layout = '';
    private $render_vars = [
        'CSS_FILES' => [],
        'JS_FILES'  => [
            'HEAD' => [],
            'BODY' => [],
        ],
    ];

    public static function factory(): callable
    {
        $page = new static();
        return $page;
    }

    /**
     * @param Response $response
     * @param Request $request
     * @param string $action
     * @param array $vars
     * @throws NotFoundException
     */
    public function __invoke(Response $response, Request $request, string $action, array $vars = [])
    {
        $this->response = $response;
        $this->request = $request;
        $this->vars = $vars;
        if (method_exists($this, 'before')) {
            $this->before();
        }
        if (!$this->response->isRedirect() && !$this->response->hasBody()) {
            $callback = [$this, $action];
            if (!is_callable($callback)) {
                throw new NotFoundException();
            }

            call_user_func_array($callback, $vars);
            if (method_exists($this, 'after')) {
                $this->after();
            }
        }
    }

    protected function getSessionParam(string $key, $default = null)
    {
        return $this->request->getSessionParam($key, $default);
    }

    protected function setSessionParam(string $key, string $value)
    {
        $this->response->setSessionParam($key, $value);
    }

    protected function getParam(string $key, $default = null)
    {
        return $this->request->getParam($key, $default);
    }

    protected function redirect(string $page, array $vars = [])
    {
        $this->redirectToUrl(path($page, $vars));
    }

    protected function redirectToUrl(string $url)
    {
        redirect($this->response, $this->request, $url);
    }

    protected function handleTurbolinksRedirects()
    {
        if ($turbolinks_location = $this->getSessionParam('_turbolinks_location')) {
            $this->setSessionParam('_turbolinks_location', '');
            $this->withHeader('Turbolinks-Location', $turbolinks_location);
        }
    }

    protected function withHeader(string $name, string $value)
    {
        $this->response->withHeader($name, $value);
    }

    protected function write(string $data): int
    {
        return $this->response->write($data);
    }

    protected function setLayout(string $layout)
    {
        $this->layout = $layout;
    }

    protected function set(string $key, $value)
    {
        $this->render_vars[$key] = $value;
    }

    protected function addCssFile(string $css_file)
    {
        $this->render_vars['CSS_FILES'][] = \K\asset_url($css_file);
    }

    /**
     * @param string $js_file
     * @param string $to HEAD to put in the <head> or BODY to append to <body>
     */
    protected function addJsFile(string $js_file, string $to = 'HEAD')
    {
        $this->render_vars['JS_FILES'][$to][] = \K\asset_url($js_file);
    }

    protected function render(string $tmpl, array $vars = [])
    {
        html($this->response, $tmpl, $this->layout, array_merge($this->render_vars, $vars));
    }

    protected function json($data, int $status = null)
    {
        json($this->response, $data, $status);
    }

    protected function txt($data, int $status = null)
    {
        if ($status !== null) {
            $this->response->withStatus($status);
        }
        $this->response->withHeader('Content-Type', 'text/text');
        $this->response->write($data);
    }

    protected function makeForm(string $name, string $action, string $method = 'get')
    {
        return new Form($this->request, $name, $action, $method);
    }
}
