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
        $this->forClients();
        $this->forAdmins();
    }

    /**
     * Register the client routes.
     *
     * @return void
     */
    public function forClients()
    {
        $this->router->group([
            'middleware' => 'api.client',
        ], __DIR__.'/../../routes/api.client.php');
    }

    public function forAdmins()
    {
        $this->router->group([
            'middleware' => 'auth:api',
        ], __DIR__.'/../../routes/api.php');
    }
}
