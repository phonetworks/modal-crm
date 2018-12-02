<?php

namespace Pho\Crm\Traits;

use Pho\Crm\Model\AccessToken;

trait AuthTrait
{
    public function isLoggedIn()
    {
        $accessTokenId = $_SESSION['access_token_id'] ?? null;
        if ($accessTokenId === null) {
            return false;
        }
        $accessToken = AccessToken::where('id', $accessTokenId)
            ->where('expires_at', '>', new \DateTime())->first();
        if ($accessToken === null) {
            return false;
        }
        if ($accessToken->revoked) {
            return false;
        }

        return true;
    }
}
