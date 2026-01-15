<?php

namespace Goloba\GolobaRMA\Repositories;

use Webkul\RMA\Repositories\RMAMessagesRepository as BaseRMAMessagesRepository;

class RMAMessagesRepository extends BaseRMAMessagesRepository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return \Goloba\GolobaRMA\Models\RMAMessages::class;
    }
}
