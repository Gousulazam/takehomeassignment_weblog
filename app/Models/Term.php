<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Define the relationship to the stats.
     */
    public function stats()
    {
        return $this->hasMany(Stat::class);
    }
}
