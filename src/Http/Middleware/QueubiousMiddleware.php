<?php

namespace Queubious\Http\Middleware;

use Closure;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class QueubiousMiddleware
{
    public function handle($request, Closure $next)
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded(config('queubious.secret'))
        );

        if (!$request->input('token') && !$request->cookie('queue_token')) {
            return redirect(config('queubious.url'));
        }
        $token = $config
            ->parser()
            ->parse(
                $request->cookie('queue_token')
                    ? $request->cookie('queue_token')
                    : $request->input('token')
            );

        $config->setValidationConstraints(
            new IssuedBy(config('queubious.url')),
            new PermittedFor(config('app.url')),
            new RelatedTo('queue-egress'),
            new SignedWith($config->signer(), $config->signingKey())
        );

        $constraints = $config->validationConstraints();

        if ($config->validator()->validate($token, ...$constraints)) {
            $cookie = \Cookie::make('queue_token', $token->toString(), 20, '/');
            $response = $request;
            return $next($response)->withCookie($cookie);
        }

        return redirect(config('queubious.url'));
    }
}
