<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Secret extends Model
{
    // adatbázis melyhez a model kapcsolódni fog
    
    protected $table = 'secret';
    protected $fillable = ['hash','name','remaining_views','minutes','expires_at'];
}
