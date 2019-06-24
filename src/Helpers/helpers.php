<?php

use \GetCandy\Api\Core\Taxes\Models\Tax;
use Illuminate\Support\Facades\Log;

if (!function_exists('makeGenericTaxIfNotObject')) {
    /**
     * If the tax object passed in is null, or not an object, then it will
     * create the default tax object manually and return it. Otherwise it returns
     * the original tax object.
     *
     * @param Tax|null $tax
     * @return Tax
     */
    function makeGenericTaxIfNotObject(Tax $tax = null) {
        if (is_null($tax) || !is_object($tax)) {
            $backtrace = debug_backtrace();
            $caller = array_shift($backtrace);
            Log::warning('Call to tax->percentage but tax is not an object. Using default tax object', [
                'file' => $caller['file'],
                'line' => $caller['line']
            ]);
            // We have this method inside of the Whirli codebase, so if it exists
            // we should use it here too
            if (function_exists('reportException')) {
                reportException(new \RuntimeException('Call to tax->percentage but tax is not an object. Using default tax object'));
            }

            $tax = new Tax();
            $tax->id = 'xndgx1jz';
            $tax->name = 'VAT';
            $tax->percentage = 20;
            $tax->default = true;
        }

        return $tax;
    }
}
