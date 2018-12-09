<?php

namespace Pho\Crm\Model;

use Illuminate\Database\Eloquent\Model;

class ServiceConversation extends Model
{
    const SOURCE_EMAIL = 1;
    const SOURCE_WEBSITE = 2;

    protected $table = 'service-conversations';

    public $timestamps = false;

    protected $fillable = [
        'uuid', 'user_id', 'text', 'source',
    ];
}
