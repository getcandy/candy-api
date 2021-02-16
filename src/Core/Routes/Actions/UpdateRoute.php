<?php

namespace GetCandy\Api\Core\Routes\Actions;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Routes\Resources\RouteResource;

class UpdateRoute extends AbstractAction
{
    protected $route;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-routes');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->route = FetchRoute::run([
            'encoded_id' => $this->encoded_id,
            'draft' => true,
        ]);

        return [
            'slug' => [
                'required',
                function ($attribute, $value, $fail) {
                    $ids = [
                        $this->route->id,
                    ];
                    if ($this->route->publishedParent) {
                        $ids[] = $this->route->publishedParent->id;
                    }
                    $result = DB::table('routes')->wherePath($this->path)->whereSlug($value)->whereNotIn('id', $ids)->exists();
                    if ($result) {
                        $fail('The path and slug have already been taken');
                    }
                },
            ],
            'lang' => 'nullable|string',
            'description' => 'nullable|string',
            'default' => 'boolean',
            'redirect' => 'boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Routes\Models\Route
     */
    public function handle()
    {
        $this->route->update($this->validated());

        return $this->route;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Routes\Models\Route  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Routes\Resources\RouteResource
     */
    public function response($result, $request)
    {
        return new RouteResource($result);
    }
}
