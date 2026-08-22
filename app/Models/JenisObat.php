<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisObat extends Model
{
    use HasFactory;

    protected $table = 'jenis_obat'; // nama tabel di database
    protected $primaryKey = 'idjenis'; // primary key

    public $timestamps = false; // karena tidak ada kolom created_at dan updated_at

    protected $fillable = [
        'jenisobat',
        'ket',
    ];

    public function getJenisobatLabelAttribute()
    {
        return match ($this->jenisobat) {
            'OTC1' => 'Obat Warung',
            'OTC2' => 'Obat Herbal & Vitamin',
            'OTC3' => 'Obat Maag & Sirplus',
            'ETC1' => 'Obat Generik Ethical',
            'ETC2' => 'Obat Branded Ethical',
            default => $this->jenisobat,
        };
    }

    public function produks()
    {
        return $this->hasMany(Product::class, 'jenisobat', 'jenisobat');
    }
}
