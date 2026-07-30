<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClusterNode extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}
