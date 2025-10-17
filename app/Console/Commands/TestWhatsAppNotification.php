<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use App\Services\PushoverService;
use Illuminate\Console\Command;

final class TestWhatsAppNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test {chatId=62811337678@c.us}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test WhatsApp notification';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $whatsAppService, PushoverService $pushoverService): int
    {
        $chatId = $this->argument('chatId');
        
        $this->info("Sending test WhatsApp notification to: {$chatId}");
        
        // Mimic an Asset Disposal notification (one of the notifications we implemented)
        $message = "🚨 *Asset Disposal Alert* (TEST)\n\n";
        $message .= "Asset: *Test Equipment X-100* (CODE-12345)\n";
        $message .= "WO: WO-2025-001\n";
        $message .= "Disposal Reason: Equipment beyond repair after inspection\n\n";
        $message .= "⚠️ 3 maintenance schedule(s) have been automatically deactivated.\n\n";
        $message .= "This is a TEST notification from SIGAP system.\n";
        $message .= "Time: " . now()->format('Y-m-d H:i:s');
        
        $this->line("\n--- Message Content ---");
        $this->line($message);
        $this->line("--- End of Message ---\n");
        
        $success = $whatsAppService->sendMessage($chatId, $message);
        
        if ($success) {
            $this->info("✅ WhatsApp notification sent successfully!");
            return Command::SUCCESS;
        } else {
            $this->error("❌ WhatsApp notification failed!");
            $this->warn("Attempting Pushover fallback...");
            
            $pushoverSuccess = $pushoverService->sendWhatsAppFailureNotification(
                'Test Asset Disposal Alert',
                $chatId,
                $message
            );
            
            if ($pushoverSuccess) {
                $this->info("✅ Pushover fallback notification sent successfully!");
            } else {
                $this->error("❌ Pushover fallback also failed!");
            }
            
            return Command::FAILURE;
        }
    }
}

