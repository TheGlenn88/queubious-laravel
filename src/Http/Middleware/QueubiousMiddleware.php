<?php

namespace Queubious\Http\Middleware;

use Closure;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\FrozenClock;
use DateTimeImmutable;

class QueubiousMiddleware
{
    protected Clock $clock;

    public function handle($request, Closure $next)
    {
        $this->clock = new FrozenClock(new DateTimeImmutable());

        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded(config('queubious.secret'))
        );

        if (
            !$request->input('queubioustoken') &&
            !$request->cookie('queubioustoken')
        ) {
            return redirect(config('queubious.url'));
        }
        $token = $config
            ->parser()
            ->parse(
                $request->cookie('queubioustoken')
                    ? $request->cookie('queubioustoken')
                    : $request->input('queubioustoken')
            );

        $config->setValidationConstraints(
            new IssuedBy(config('queubious.url')),
            new PermittedFor(config('app.url')),
            new RelatedTo('queue-egress'),
            new SignedWith($config->signer(), $config->signingKey()),
            new ValidAt($this->clock)
        );

        $constraints = $config->validationConstraints();

        if ($config->validator()->validate($token, ...$constraints)) {
            $cookie = \Cookie::make(
                'queubioustoken',
                $token->toString(),
                $token->claims()->get('cexp'),
                '/',
                null,
                false,
                false
            );

            $response = $next($request);

            if ($request->input('queubioustoken')) {
                return $response
                    ->withCookie($cookie)
                    ->header('Location', \URL::current())
                    ->setStatusCode(302);
            }

            return $response->withCookie($cookie);
        }

        return redirect(config('queubious.url'));
    }
}
