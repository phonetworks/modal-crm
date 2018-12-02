<?php

namespace Pho\Crm\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    public $timestamps = false;

    protected $casts = [
        'is_verified' => 'boolean',
    ];
}
