<?php

namespace GetCandy\Api\Core\Reports\Actions;

use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchMetricsReport extends AbstractAction
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
            'subject' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return array
     */
    public function handle(ReportManagerContract $reports)
    {
        return $reports->with($this->subject)->metrics();
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
