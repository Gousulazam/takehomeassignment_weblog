<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_id', 'term_id', 'event_date', 'event_hour', 'revenue'];

    /**
     * Define the relationship to the campaign.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Define the relationship to the term.
     */
    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}
