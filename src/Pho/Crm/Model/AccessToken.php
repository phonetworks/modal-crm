<?php

namespace Pho\Crm\Model;

use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    protected $table = 'access-tokens';

    public $timestamps = false;

    protected $casts = [
        'revoked' => 'boolean',
    ];
}
