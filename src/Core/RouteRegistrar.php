<?php

namespace GetCandy\Api\Core;

use Illuminate\Contracts\Routing\Registrar as Router;

class RouteRegistrar
{
    /**
     * The router implementation.
     *
     * @var \Illuminate\Contracts\Routing\Registrar
     */
    protected $router;

    /**
     * Create a new route registrar instance.
     *
     * @param  \Illuminate\Contracts\Routing\Registrar  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Register routes for clients and admins.
     *
     * @return void
     */
    public function all()
    {
        $this->guest();
        $this->auth();
    }

    /**
     * Register the client routes.
     *
     * @return void
     */
    public function guest()
    {
        $this->router->group([], __DIR__.'/../../routes/api.client.php');
    }

    /**
     * Register the auth routes.
     *
     * @return  void
     */
    public function auth()
    {
        $this->router->group([], __DIR__.'/../../routes/api.php');
    }

    /**
     * Provide a sanctum template to use.
     *
     * @return  void
     */
    public function templateSanctum()
    {
        $this->router->group([
            'middleware' => ['auth:sanctum', 'api'],
        ], function () {
            $this->auth();
        });
        $this->router->group([
            'middleware' => ['api'],
        ], function () {
            $this->guest();
        });
    }
}
