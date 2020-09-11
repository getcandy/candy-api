<?php

namespace GetCandy\Api\Core\Reports\Actions;

use Illuminate\Support\Carbon;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;

class FetchOrderAveragesReport extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('view-reports');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'from' => 'required|date',
            'to' => 'required|date|after:from',
            'model' => 'string'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return array
     */
    public function handle(ReportManagerContract $reports)
    {
        return $reports->with('orders')
            ->mode($this->mode ?: 'monthly')
            ->between(
                Carbon::parse($this->from),
                Carbon::parse($this->to)
            )->averages();
    }

    /**
     * Returns the response from the action.
     *
     * @param   array $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        return response()->json($result);
    }
}
