<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DocumentBorrowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class SendBorrowReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borrows:send-reminders 
                            {--due-soon : Send reminders for documents due soon (within 1 day)}
                            {--overdue : Send notices for overdue documents}
                            {--all : Send both due soon reminders and overdue notices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp reminders for document borrows that are due soon or overdue';

    public function __construct(
        private readonly DocumentBorrowService $borrowService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sendDueSoon = $this->option('due-soon') || $this->option('all');
        $sendOverdue = $this->option('overdue') || $this->option('all');

        // If no options specified, default to sending both
        if (!$sendDueSoon && !$sendOverdue) {
            $sendDueSoon = true;
            $sendOverdue = true;
        }

        $this->info('Starting document borrow reminders...');

        $dueSoonCount = 0;
        $overdueCount = 0;

        // Send due soon reminders
        if ($sendDueSoon) {
            $this->info('Processing due soon reminders...');
            $dueSoonBorrows = $this->borrowService->getBorrowsDueSoon(1);

            foreach ($dueSoonBorrows as $borrow) {
                try {
                    $this->borrowService->sendDueDateReminder($borrow);
                    $dueSoonCount++;
                    $this->line("  - Sent reminder for borrow #{$borrow->id} ({$borrow->document->title})");
                } catch (\Exception $e) {
                    $this->error("  - Failed to send reminder for borrow #{$borrow->id}: {$e->getMessage()}");
                    Log::error('Failed to send due soon reminder', [
                        'borrow_id' => $borrow->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->info("Sent {$dueSoonCount} due soon reminders.");
        }

        // Send overdue notices
        if ($sendOverdue) {
            $this->info('Processing overdue notices...');
            $overdueBorrows = $this->borrowService->getOverdueBorrows();

            foreach ($overdueBorrows as $borrow) {
                try {
                    $this->borrowService->sendOverdueNotice($borrow);
                    $overdueCount++;
                    $this->line("  - Sent overdue notice for borrow #{$borrow->id} ({$borrow->document->title})");
                } catch (\Exception $e) {
                    $this->error("  - Failed to send overdue notice for borrow #{$borrow->id}: {$e->getMessage()}");
                    Log::error('Failed to send overdue notice', [
                        'borrow_id' => $borrow->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->info("Sent {$overdueCount} overdue notices.");
        }

        $this->info('Document borrow reminders completed.');
        $this->info("Summary: {$dueSoonCount} due soon reminders, {$overdueCount} overdue notices sent.");

        return Command::SUCCESS;
    }
}

