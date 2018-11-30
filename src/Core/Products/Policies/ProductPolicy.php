<?php

namespace GetCandy\Api\Core\Products\Policies;

use GetCandy\Api\Core\Products\Models\Product;

class ProductPolicy
{
    public function before()
    {
        // dd('before');
        return true;
    }

    public function update(User $user, Product $product)
    {
        return true;
    }

    public function create(User $user, Product $product)
    {
        return true;
    }

    public function edit()
    {
        return true;
    }

    public function view()
    {
        return true;
    }
}
