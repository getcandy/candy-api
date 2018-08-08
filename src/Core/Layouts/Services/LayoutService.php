<?php

namespace GetCandy\Api\Core\Layouts\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Layouts\Models\Layout;

class LayoutService extends BaseService
{
    public function __construct()
    {
        $this->model = new Layout();
    }

    /**
     * Create a new layout.
     *
     * @param array $data
     * @return Layout
     */
    public function create(array $data)
    {
        $layout = new Layout();
        $layout->name = $data['name'];
        $layout->handle = $data['handle'];
        $layout->type = $data['type'];
        $layout->save();

        return $layout;
    }

    /**
     * Update an existing layout.
     *
     * @param string $id
     * @param array $data
     * @return Layout
     */
    public function update($id, array $data)
    {
        $layout = $this->getByHashedId($id);
        $layout->fill($data);
        $layout->save();

        return $layout;
    }
}
