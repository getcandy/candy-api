<?php

namespace GetCandy\Api\Core\Search\Contracts;

use Illuminate\Http\Request;

interface SearchDriverContract
{
    public function search(Request $request);
}
