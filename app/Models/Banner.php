<?php

// app/Models/Banner.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['judul', 'deskripsi', 'foto', 'status'];
}
