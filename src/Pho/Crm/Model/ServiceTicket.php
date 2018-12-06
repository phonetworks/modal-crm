<?php

namespace Pho\Crm\Model;

use Illuminate\Database\Eloquent\Model;

class ServiceTicket extends Model
{
    protected $table = 'service-tickets';

    public $timestamps = false;

    public function serviceConversations()
    {
        return $this->hasMany(ServiceConversation::class, 'uuid', 'uuid');
    }
}
