<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receptionist extends Model
{
    use HasFactory;

    protected $fillable = ['receptionist_id', 'user_id', 'full_name', 'phone', 'shift'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
