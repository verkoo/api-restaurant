<?php

namespace App\Listeners;

use App\Entities\Inventory;
use Verkoo\Common\Entities\Settings;
use Verkoo\Common\Events\SessionCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetStock
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SessionCreated  $event
     * @return void
     */
    public function handle(SessionCreated $event)
    {
        if (Settings::get('recount_stock_when_open_cash')){
            Inventory::resetStockInRecountableCategories();
        }
    }
}
