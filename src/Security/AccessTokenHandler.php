<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    protected array $tokens;

    public function __construct(string $tokenPath) {
        $data = file_get_contents($tokenPath);
        if ($data === false) {
            throw new \Exception('Could not read tokens file: ' . $tokenPath);
        }

        $this->tokens = json_decode($data, true);
        if ($this->tokens === null) {
            throw new \Exception('Could not decode tokens file: ' . $tokenPath);
        }
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        foreach ($this->tokens as $token) {
            if ($token['token'] != $accessToken) {
                continue;
            }

            if (! $token['enabled']) {
                throw new CredentialsExpiredException('Token has been disabled.');
            }

            $dt = new \DateTime($token['expires_at']);
            if ($dt->getTimestamp() < time()) {
                throw new CredentialsExpiredException('Token has expired.');
            }

            return new UserBadge('crate_user');
        }

        throw new BadCredentialsException('Invalid token.');
    }
}