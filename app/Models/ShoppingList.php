<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShoppingList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_list',
        'budget',
        'user_id',
        'supermarket_id',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
    ];

    /**
     * Relación con usuario (muchos a uno)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con supermercado (muchos a uno)
     */
    public function supermarket(): BelongsTo
    {
        return $this->belongsTo(Supermarket::class);
    }

    /**
     * Relación con productos (muchos a muchos)
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'shopping_list_products')
                    ->withPivot('quantity', 'content')
                    ->withTimestamps();
    }
}
