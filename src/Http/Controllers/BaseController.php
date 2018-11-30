<?php

namespace GetCandy\Api\Http\Controllers;

use Illuminate\Routing\Controller;
use GetCandy\Api\Core\Traits\Fractal;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Fractal;
}
