<?php

namespace GetCandy\Api\Http\Middleware;

use Locale;
use Closure;
use SupportPal\AcceptLanguageParser\Parser;
use GetCandy\Api\Core\Languages\Actions\FetchDefaultLanguage;
use GetCandy\Api\Core\Languages\Actions\FetchEnabledLanguageByCode;

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

        $locale = collect($parser->parse())->first();

        $code = $defaultLanguage->code;
        if ($locale) {
            $code = $locale->code();
        }

        $language = FetchEnabledLanguageByCode::run([
            'code' => $code,
        ]);

        if (!$language) {
            $code = $defaultLanguage->code;
        }

        app()->setLocale($code);

        return $next($request);
    }
}
