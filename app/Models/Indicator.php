<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicator extends Model
{
    protected $fillable = ['modul', 'kode', 'nama', 'satuan', 'deskripsi'];
}
