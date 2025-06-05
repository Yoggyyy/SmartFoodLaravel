<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_product',
        'category',
        'price',
        'supermarket_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Relación con supermercado (muchos a uno)
     */
    public function supermarket(): BelongsTo
    {
        return $this->belongsTo(Supermarket::class);
    }

    /**
     * Relación con listas de compras (muchos a muchos)
     */
    public function shoppingLists(): BelongsToMany
    {
        return $this->belongsToMany(ShoppingList::class, 'shopping_list_products')
                    ->withPivot('quantity', 'content', 'completed')
                    ->withTimestamps();
    }
}
