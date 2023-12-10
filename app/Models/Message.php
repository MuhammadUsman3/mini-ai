<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    
    // Define any fillable fields
    protected $fillable = ['user_id', 'user_message', 'bot_response'];

    // Define relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}