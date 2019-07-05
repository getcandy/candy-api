<?php

namespace GetCandy\Api\Core\Utils\Import;

use Excel;
use GetCandy\Api\Core\Utils\Import\Models\Import;

abstract class AbstractImporter
{
    /**
     * The import to process.
     *
     * @var Import
     */
    protected $import;

    protected $rows;

    public function using(Import $import)
    {
        $this->import = $import;
        $this->rows = Excel::load(storage_path("app/{$import->file}"));

        return $this;
    }

    /**
     * Handle the import.
     *
     * @return void
     */
    abstract public function handle();
}
