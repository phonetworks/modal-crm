<?php

namespace Pho\Crm\Model;

use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    public function site()
    {
        return $this->hasOne(Site::class, 'instance_id', 'id');
    }
}
