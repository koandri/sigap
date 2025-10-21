<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\DocumentAccessRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class CleanupExpiredAccess extends Command
{
    protected $signature = 'dms:cleanup-expired-access';
    protected $description = 'Clean up expired document access requests';

    public function handle(): int
    {
        $this->info('Starting cleanup of expired document access...');

        // Get expired access requests
        $expiredRequests = DocumentAccessRequest::where('status', 'approved')
            ->whereNotNull('approved_expiry_date')
            ->where('approved_expiry_date', '<', now())
            ->get();

        if ($expiredRequests->isEmpty()) {
            $this->info('No expired access requests found.');
            return 0;
        }

        $this->info("Found {$expiredRequests->count()} expired access requests.");

        // Update status to expired
        $updated = DocumentAccessRequest::where('status', 'approved')
            ->whereNotNull('approved_expiry_date')
            ->where('approved_expiry_date', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Updated {$updated} access requests to expired status.");

        // Log the cleanup
        \Log::info('DMS: Cleaned up expired document access requests', [
            'count' => $updated,
            'timestamp' => now(),
        ]);

        return 0;
    }
}
