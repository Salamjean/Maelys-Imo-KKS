<?php

namespace App\Console\Commands;

use App\Models\PaiementSession;
use Illuminate\Console\Command;

class CleanPaymentSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-payment-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = PaiementSession::where('expires_at', '<', now())->delete();
        $this->info("Sessions de paiement expirées supprimées: {$deleted}");
    }
}
