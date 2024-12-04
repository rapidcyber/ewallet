<?php

namespace App\Http\Middleware;

use Laravel\Passport\Http\Middleware\CheckCredentials;
use Laravel\Passport\Exceptions\AuthenticationException;
use Laravel\Passport\Exceptions\MissingScopeException;

class CheckPartnerCredentials extends CheckCredentials
{
    /**
     * Validate token credentials.
     *
     * @param  \Laravel\Passport\Token  $token
     * @return void
     *
     * @throws \Laravel\Passport\Exceptions\AuthenticationException
     */
    protected function validateCredentials($token)
    {
        if (!$token) {
            throw new AuthenticationException;
        }

        $is_partner = $token->client->provider == 'partners';
        $is_eligible = $token->client->user?->status == 'verified';
        if (!$is_partner || ! $is_eligible) {
            throw new AuthenticationException;
        }
    }

    /**
     * Validate token credentials.
     *
     * @param  \Laravel\Passport\Token  $token
     * @param  array  $scopes
     * @return void
     *
     * @throws \Laravel\Passport\Exceptions\MissingScopeException
     */
    protected function validateScopes($token, $scopes)
    {
        if (in_array('*', $token->scopes)) {
            return;
        }

        foreach ($scopes as $scope) {
            if ($token->cant($scope)) {
                throw new MissingScopeException($scope);
            }
        }
    }
}