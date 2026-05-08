<?php

namespace Goloba\GolobaRMA\DataGrids\Seller;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use Webkul\DataGrid\DataGrid;
use Webkul\RMA\Repositories\RMAStatusRepository;

class SellerRmaDataGrid extends DataGrid
{
    /**
     * Constructor for the class.
     */
    public function __construct(
        protected RMAStatusRepository $rmaStatusRepository,
    ) {
    }

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $sellerId = auth()->guard('seller')->user()->id;
        $table_prefix = DB::getTablePrefix();

        $queryBuilder = DB::table('rma')
            ->leftJoin('orders', 'orders.id', '=', 'rma.order_id')
            ->leftJoin('rma_items', 'rma_items.rma_id', '=', 'rma.id')
            ->leftJoin('marketplace_order_items', 'marketplace_order_items.order_item_id', '=', 'rma_items.order_item_id')
            ->leftJoin('marketplace_products', 'marketplace_products.id', '=', 'marketplace_order_items.marketplace_product_id')
            ->where('marketplace_products.marketplace_seller_id', $sellerId)
            ->select(
                'rma.id',
                'rma.order_id',
                'rma.rma_type',
                DB::raw('CONCAT(' . $table_prefix . 'orders.customer_first_name, " ", ' . $table_prefix . 'orders.customer_last_name) as customer_name'),
                'rma.status',
                'rma.rma_status',
                'rma.order_status as rma_order_status',
                'rma.created_at',
                'orders.status as order_status'
            )
            ->groupBy('rma.id');
                
        $this->addFilter('id', 'rma.id');
        $this->addFilter('order_id', 'rma.order_id');
        $this->addFilter('rma_status', 'rma.rma_status');
        $this->addFilter('rma_type', 'rma.rma_type');
        $this->addFilter('created_at', 'rma.created_at');
        $this->addFilter('customer_name', DB::raw('CONCAT(' . $table_prefix . 'orders.customer_first_name, " ", ' . $table_prefix . 'orders.customer_last_name)'));      

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'order_id',
            'label'      => 'Orden',
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'customer_name',
            'label'      => 'Cliente',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'rma_type',
            'label'      => 'Tipo RMA',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->rma_type === 'retracto'
                    ? '<p class="label-active" style="background:#1d4ed8;">Derecho de Retracto</p>'
                    : '<p class="label-active" style="background:#6b7280;">Estándar</p>';
            },
        ]);

        $this->addColumn([
            'index'      => 'rma_status',
            'label'      => 'Estado RMA',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                $statusColor = \DB::table('rma_status')
                    ->where('title', $row->rma_status)
                    ->value('color') ?? '#6b7280';

                return '<p class="label-active" style="background:' . $statusColor . ';">' . $row->rma_status . '</p>';
            },
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => 'Fecha Creación',
            'type'       => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('marketplace.seller.rma.view')) {
            $this->addAction([
                'index'  => 'view',
                'icon'   => 'icon-eye',
                'title'  => 'Ver',
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('goloba.seller.rma.view', $row->id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        // Por ahora sin acciones masivas
    }
