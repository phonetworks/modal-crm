<?php

namespace Pho\Crm;

use Pho\Crm\Model\User;

class Auth
{
    private $user;

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
