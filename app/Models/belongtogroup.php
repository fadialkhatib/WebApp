<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $id)
 * @method static create(array $array)
 */
class belongtogroup extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','group_id'];
}
