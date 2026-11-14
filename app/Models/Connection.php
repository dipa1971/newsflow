<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    protected $fillable = ['site_id', 'provider', 'external_id', 'name', 'token', 'status'];

    protected $casts = ['token' => 'encrypted'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
