<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenTemporal extends Model
{
    protected $table = 'usuarios_temporales';
    protected $primaryKey = 'idToken';

    protected $fillable = [
        'token',
        'expiracion',
    ];

    public $timestamps = false;
}
