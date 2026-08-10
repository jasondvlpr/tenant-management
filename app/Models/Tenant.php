<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'domains' => 'array',
    ];

    public function clusterNode()
    {
        return $this->belongsTo(ClusterNode::class, 'cluster_node_id');
    }
}
