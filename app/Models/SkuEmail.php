<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkuEmail extends Model
{
    use HasFactory;

    public function email(){
        return $this->belongsTo(Emails::class, 'email_id');
    }
}
