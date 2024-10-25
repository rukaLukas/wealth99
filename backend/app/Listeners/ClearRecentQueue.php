<?php
namespace App\Listeners;

use Illuminate\Support\Facades\DB;
use App\Jobs\FetchRecentCryptoData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class ClearRecentQueue
{
    public function handle($event)
    {
        if ($event->job->resolveName() === FetchRecentCryptoData::class) {
            // Clear all other jobs from the "recent" queue
            // Queue::getRedis()->del('queues:recent');
            dump("remove jobs");
            Log::info("remove Jobs from queue recent");
            DB::table('jobs')
                ->where('queue', 'recent')
                ->where('payload', 'LIKE', '%FetchRecentCryptoData%')
                ->skip(1) // keep the first job
                ->delete();
        }
    }
}