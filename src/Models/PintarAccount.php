<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PintarAccount extends Model
{
    use HasFactory;
    use HasUuids;
    protected $table = 'pintar_accounts';

    protected $fillable = [
        'user_id',
        'pintar_id',
    ];
}
