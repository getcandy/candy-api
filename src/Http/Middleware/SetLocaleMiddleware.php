<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy\Api\Core\Languages\Actions\FetchDefaultLanguage;
use GetCandy\Api\Core\Languages\Actions\FetchEnabledLanguageByCode;
use Locale;

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
        $locale = $request->header('accept-language');

        $defaultLanguage = FetchDefaultLanguage::run()->lang;

        if (! $locale) {
            $locale = $defaultLanguage;
        } else {
            if (extension_loaded('intl')) {
                $languages = explode(',', Locale::getPrimaryLanguage($locale));
            } else {
                $languages = explode(',', $locale);
            }
            $requestedLocale = FetchEnabledLanguageByCode::run([
                'code' => $languages[0] ?? $languages,
            ]);
            if (! $requestedLocale) {
                $locale = $defaultLanguage;
            } else {
                $locale = $requestedLocale->lang;
            }
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
