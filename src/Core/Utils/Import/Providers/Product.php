<?php

namespace GetCandy\Api\Core\Utils\Import\Providers;

use Mail;
use Carbon\Carbon;
use Illuminate\Pipeline\Pipeline;
use GetCandy\Api\Core\Utils\Import\AbstractImporter;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Utils\Import\Pipelines\UpdateState;
use GetCandy\Api\Core\Utils\Import\Pipelines\UpdateVariant;
use GetCandy\Api\Core\Utils\Import\Pipelines\UpdateAttributes;

class Product extends AbstractImporter
{
    protected $pipes = [
        UpdateVariant::class,
        UpdateAttributes::class,
        UpdateState::class,
    ];

    public function handle()
    {
        $created = 0;
        $updated = 0;
        $deleted = 0;

        $this->rows->each(function ($line) use ($created, $updated, $deleted) {
            $variant = $this->getBySku($line->sku);

            if (! $variant) {
                return;
            }

            app(Pipeline::class)
            ->send([$variant, $line, $this->import])
            ->through($this->pipes)
            ->then(function ($content) {
                //
            });
        });

        $this->import->update([
            'completed_at' => Carbon::now(),
        ]);

        $message = "Your import has finished successfully
            Updated:{$this->import->updated}
            Created:{$this->import->created}
            Deleted:{$this->import->deleted}
            ";
        Mail::raw($message, function ($message) {
            $message->to($this->import->email)
              ->subject('Import completed');
        });
    }

    /**
     * Get the variant by the SKU.
     *
     * @param [type] $sku
     * @return void
     */
    protected function getBySku($sku)
    {
        return ProductVariant::withoutGlobalScopes()->whereSku($sku)->first();
    }
}
