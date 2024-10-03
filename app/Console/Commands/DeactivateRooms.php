<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Rooms;
use Carbon\Carbon;

class DeactivateRooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rooms:deactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate rooms automatically after midnight';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Gece yarısından sonra pasif hale getirilmesi gereken kullanıcıları güncelle
        Rooms::where('is_active', true)
            ->where('created_at', '<', $now->startOfDay())
            ->update(['is_active' => false]);

        $this->info('Inactive rooms deactivated successfully.');
    }
}
