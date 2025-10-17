<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Asset;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Console\Command;

final class GenerateAssetQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:generate-qr {--force : Regenerate QR codes for all assets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate QR codes for assets without QR codes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        $query = Asset::query();
        
        if (!$force) {
            $query->whereNull('qr_code_path');
        }

        $assets = $query->get();

        if ($assets->isEmpty()) {
            $this->info('No assets need QR code generation.');
            return self::SUCCESS;
        }

        $this->info("Generating QR codes for {$assets->count()} assets...");

        $progressBar = $this->output->createProgressBar($assets->count());
        $progressBar->start();

        foreach ($assets as $asset) {
            $this->generateQRCode($asset);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('QR codes generated successfully!');

        return self::SUCCESS;
    }

    /**
     * Generate and store QR code for an asset.
     */
    private function generateQRCode(Asset $asset): void
    {
        // Delete old QR code if exists
        if ($asset->qr_code_path && file_exists(public_path($asset->qr_code_path))) {
            unlink(public_path($asset->qr_code_path));
        }

        // Generate QR code data
        $qrData = route('options.assets.show', $asset);

        // Check if logo exists
        $logoPath = public_path('imgs/qr_logo.png');
        $hasLogo = file_exists($logoPath);

        // Build QR code
        $builder = new Builder(
            writer: new PngWriter(),
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 400,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            logoPath: $hasLogo ? $logoPath : null,
            logoResizeToWidth: $hasLogo ? 80 : null,
            logoPunchoutBackground: $hasLogo
        );

        $result = $builder->build();

        // Save to file
        $filename = 'qr-' . $asset->code . '.png';
        $filePath = 'storage/assets_qr/' . $filename;
        $fullPath = public_path($filePath);

        file_put_contents($fullPath, $result->getString());

        // Update asset with QR path
        $asset->update(['qr_code_path' => $filePath]);
    }
}
