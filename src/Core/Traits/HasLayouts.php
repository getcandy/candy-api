<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Layouts\Models\Layout;

trait HasLayouts
{
    /**
     * Get the associated layout.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function layout()
    {
        return $this->belongsTo(Layout::class);
    }
}
