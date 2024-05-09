<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;
    /**
     * @var string[]
     */
    protected $fillable = ['message', 'file', 'line', 'trace'];
}
