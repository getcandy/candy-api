<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy\Api\Core\Languages\Actions\FetchDefaultLanguage;
use GetCandy\Api\Core\Languages\Actions\FetchEnabledLanguageByCode;
use SupportPal\AcceptLanguageParser\Parser;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $defaultLanguage = FetchDefaultLanguage::run();
        $parser = new Parser($request->header('accept-language'));

        $language = collect($parser->parse())->first();

        $code = $defaultLanguage->code;
        if ($language) {
            $code = $language->code();
        }

        $languageModel = FetchEnabledLanguageByCode::run([
            'code' => $code,
        ]);

        if (! $languageModel) {
            $code = $defaultLanguage->code;
        }

        app()->setLocale($code);

        return $next($request);
    }
}
