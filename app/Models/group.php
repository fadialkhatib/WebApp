<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, mixed $group_id)
 * @method static create(array $array)
 */
class group extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;
}
