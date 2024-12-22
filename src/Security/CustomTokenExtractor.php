<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class CustomTokenExtractor implements AccessTokenExtractorInterface
{
    public function extractAccessToken(Request $request): ?string
    {
        // Cargo does not use the header:
        //
        //      Authorization: Bearer <token>
        //
        // but instead
        //
        //    Authorization: <token>
        //
        // This is not standard in symfony, so we need to have this custom extractor that
        // extracts the token from the header.
        $token = $request->headers->get('Authorization');
        if ($token === null) {
            return null;
        }

        return $token;
    }
}