<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'auto_dns' => 'boolean',
        'cf_nameservers' => 'array',
    ];

    public function clusterNode()
    {
        return $this->belongsTo(ClusterNode::class, 'cluster_node_id');
    }

    public function aliases()
    {
        return $this->hasMany(DomainAlias::class);
    }
}
