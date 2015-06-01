<?php namespace NZTim\Queue;

use Illuminate\Support\Facades\Facade;

class QueueMgrFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'queuemgr';
    }
}