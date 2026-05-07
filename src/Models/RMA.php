<?php

namespace Goloba\GolobaRMA\Models;

use Webkul\RMA\Models\RMA as BaseRMA;

class RMA extends BaseRMA
{
    /**
     * Fillable attributes
     */
    protected $fillable = [
        'resolution',
        'information',
        'order_status',
        'rma_status',
        'order_id',
        'status',
        'package_condition',
        'rma_type',
        'retracto_expires_at',
        'retracto_seal_intact',
    ];

    protected $casts = [
        'status'               => 'boolean',
        'retracto_seal_intact' => 'boolean',
        'retracto_expires_at'  => 'datetime',
    ];

    /**
     * Verificar si este RMA pertenece a un vendedor específico.
     */
    public function belongsToSeller(int $sellerId): bool
    {
        // Verificar usando query directa a través de las relaciones del marketplace
        $exists = \DB::table('rma_items')
            ->where('rma_items.rma_id', $this->id)
            ->join('marketplace_order_items', 'marketplace_order_items.order_item_id', '=', 'rma_items.order_item_id')
            ->join('marketplace_products', 'marketplace_products.id', '=', 'marketplace_order_items.marketplace_product_id')
            ->where('marketplace_products.marketplace_seller_id', $sellerId)
            ->exists();
            
        return $exists;
    }

    /**
     * Scope para filtrar RMAs de un vendedor específico
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $sellerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSeller($query, int $sellerId)
    {
        return $query->whereHas('rmaItems', function($q) use ($sellerId) {
            $q->whereHas('marketplaceOrderItem', function($moi) use ($sellerId) {
                $moi->whereHas('marketplaceProduct', function($mp) use ($sellerId) {
                    $mp->where('marketplace_seller_id', $sellerId);
                });
            });
        });
    }

    /**
     * Relación con RMA Items
     */
    public function rmaItems()
    {
        return $this->hasMany(\Webkul\RMA\Models\RMAItems::class, 'rma_id');
    }

    /**
     * Relación con los items de la orden a través de rma_items y marketplace
     */
    public function orderItems()
    {
        return $this->hasManyThrough(
            \Webkul\Sales\Models\OrderItem::class,
            \Webkul\RMA\Models\RMAItems::class,
            'rma_id',
            'id',
            'id',
            'order_item_id'
        );
    }
}
