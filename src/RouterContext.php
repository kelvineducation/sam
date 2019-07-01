<?php

namespace App;

use App\Routers\MethodNotAllowedException;
use App\Routers\NotFoundException;
use The\App;
use The\AppContext;
use function The\json;
use The\Request;
use The\Response;
use The\ResponseWriterInterface;
use Throwable;

class RouterContext extends AppContext
{
    /**
     * @var RouterInterface[]
     */
    private $routers;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var AppContext $child_context
     */
    private $child_context;

    /**
     * @param RouterInterface[] $routers
     * @param AppContext|null $child_context
     * @return RouterContext
     */
    public static function init(array $routers, AppContext $child_context = null)
    {
        return new static($routers, $child_context);
    }

    /**
     * @param RouterInterface[] $routers
     * @param AppContext|null $child_context
     */
    public function __construct(array $routers, AppContext $child_context = null)
    {
        parent::__construct();

        $this->child_context = $child_context ?? new AppContext();

        $this->routers = $routers;
        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * @param App $app
     */
    public function configure(App $app)
    {
        $this->child_context->configure($app);
    }

    public function run()
    {
        try {
            $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            $routed = false;
            foreach ($this->routers as $router) {
                /** @var $router RouterInterface */
                $routed = $router->route($this->response, $this->request, $request_path);
                if ($routed) {
                    break;
                }
            }

            if (!$routed) {
                throw new NotFoundException();
            }
        } catch (NotFoundException $e) {
            $this->handleNotFound($this->response);
        } catch (MethodNotAllowedException $e) {
            $this->handleMethodNotAllowed($this->response);
        } catch (Throwable $e) {
            $this->defaultErrorHandler($e);
        } finally {
            $this->response->send();
        }
    }

    /**
     * @param Throwable $e
     */
    public function defaultErrorHandler(Throwable $e)
    {
        $this->response->withStatus(500);

        $this->handleServerError($this->response, $e);
        $this->response->send();
    }

    /**
     * @param ResponseWriterInterface $w
     * @param Throwable $e
     */
    private function handleServerError(ResponseWriterInterface $w, Throwable $e)
    {
        if (method_exists($this->child_context, 'handleServerError')) {
            $this->child_context->handleServerError($w, $e);
        }
    }

    /**
     * @param ResponseWriterInterface $w
     */
    private function handleNotFound(ResponseWriterInterface $w)
    {
        if (method_exists($this->child_context, 'handleNotFound')) {
            $this->child_context->handleNotFound($w);
        }
    }

    /**
     * @param ResponseWriterInterface $w
     */
    private function handleMethodNotAllowed(ResponseWriterInterface $w)
    {
        if (method_exists($this->child_context, 'handleMethodNotAllowed')) {
            $this->child_context->handleMethodNotAllowed($w);
        }

        json($w, [
            'code'  => 405,
            'error' => "Method not allowed",
        ], 405);
    }
}
