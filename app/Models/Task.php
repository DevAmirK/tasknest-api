<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['text', 'done', 'status', 'deleted_at', 'color'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
