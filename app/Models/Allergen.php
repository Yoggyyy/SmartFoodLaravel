<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Allergen extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_allergen',
    ];

    /**
     * Relación con usuarios (muchos a muchos)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_allergens');
    }
}
