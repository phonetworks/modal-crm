<?php

namespace Pho\Crm\Traits;

use Pho\Crm\Model\AccessToken;

trait AuthTrait
{
    public function isLoggedIn()
    {
        $accessToken = $this->getAccessToken();

        return ($accessToken !== null)
            && (! $accessToken->revoked);
    }

    public function getAccessToken()
    {
        $accessTokenId = $_SESSION['access_token_id'] ?? null;
        if ($accessTokenId === null) {
            return null;
        }
        $accessToken = AccessToken::where('id', $accessTokenId)
            ->where('expires_at', '>', new \DateTime())->first();
        return $accessToken;
    }

    public function getCurrentUser()
    {
        $accessToken = $this->getAccessToken();
        return $accessToken->user;
    }
}
