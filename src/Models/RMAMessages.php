<?php

namespace Goloba\GolobaRMA\Models;

use Webkul\RMA\Models\RMAMessages as BaseRMAMessages;

class RMAMessages extends BaseRMAMessages
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message',
        'rma_id',
        'is_admin',
        'is_seller',
        'attachment_path',
        'attachment',
    ];
}
