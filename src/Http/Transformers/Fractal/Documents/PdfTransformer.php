<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Documents;

use Barryvdh\DomPDF\PDF;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class PdfTransformer extends BaseTransformer
{
    public function transform(PDF $pdf)
    {
        return [
            'encoding' => 'base64',
            'contents' => base64_encode($pdf->output()),
        ];
    }
}
