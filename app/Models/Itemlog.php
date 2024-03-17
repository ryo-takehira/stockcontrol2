<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itemlog extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',
        'item_id',
        'item_name',
        'item_type',
        'user_name',
        'user_type',
        'operation',
        'detail',
        ];
}
