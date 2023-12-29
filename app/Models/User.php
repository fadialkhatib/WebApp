<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $user_id)
 */
class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
