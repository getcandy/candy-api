<?php

namespace GetCandy\Api\Http\Middleware;

use Auth;
use Closure;
use Firebase\JWT\JWT;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\ResourceServer;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Encryption\DecryptException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Laravel\Passport\Http\Middleware\CheckClientCredentials as BaseMiddleware;

class CheckClientCredentials extends BaseMiddleware
{
    /**
     * The Resource Server instance.
     *
     * @var \League\OAuth2\Server\ResourceServer
     */
    protected $server;

    private $encrypter;

    protected $provider;

    protected $tokens;

    /**
     * Create a new middleware instance.
     *
     * @param  \League\OAuth2\Server\ResourceServer  $server
     * @return void
     */
    public function __construct(ResourceServer $server, Encrypter $encrypter, TokenRepository $tokens)
    {
        $this->server = $server;
        $this->encrypter = $encrypter;
        $this->provider = Auth::createUserProvider('users');
        $this->tokens = $tokens;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$scopes
     * @return mixed
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        $psr = (new DiactorosFactory)->createRequest($request);

        $cookies = $psr->getCookieParams();

        if (! empty($cookies[Passport::cookie()])) {
            try {
                $token = $this->decodeJwtTokenCookie($request);
            } catch (DecryptException $e) {
                throw new AuthenticationException;
            }

            if ($user = $this->provider->retrieveById($token['sub'])) {
                Auth::login($user);

                return $next($request);
            } else {
                throw new AuthenticationException;
            }
        }

        try {
            $psr = $this->server->validateAuthenticatedRequest($psr);
        } catch (OAuthServerException $e) {
            throw new AuthenticationException;
        }

        if ($user = $this->provider->retrieveById($psr->getAttribute('oauth_user_id'))) {
            Auth::login($user);

            return $next($request);
        }

        $this->validateScopes($psr, $scopes);

        return $next($request);
    }

    /**
     * Decode and decrypt the JWT token cookie.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function decodeJwtTokenCookie($request)
    {
        return (array) JWT::decode(
            $this->encrypter->decrypt($request->cookie(Passport::cookie()), Passport::$unserializesCookies),
            $this->encrypter->getKey(), ['HS256']
        );
    }

    /**
     * Determine if the CSRF / header are valid and match.
     *
     * @param  array  $token
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function validCsrf($token, $request)
    {
        return isset($token['csrf']) && hash_equals(
            $token['csrf'], (string) $request->header('X-CSRF-TOKEN')
        );
    }
}
