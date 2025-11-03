<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\GuideService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class GenerateGuideScreenshots extends Command
{
    protected $signature = 'guides:generate-screenshots {--url=http://127.0.0.1:8000}';

    protected $description = 'Generate screenshots for all guides';

    public function __construct(
        private readonly GuideService $guideService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Generating guide screenshots...');
        
        $guides = $this->guideService->getAllGuides();
        $baseUrl = $this->option('url');
        $screenshotsDir = public_path('guides-imgs');

        if (!File::isDirectory($screenshotsDir)) {
            File::makeDirectory($screenshotsDir, 0755, true);
        }

        foreach ($guides as $guide) {
            $filename = $guide['filename'];
            $slug = pathinfo($filename, PATHINFO_FILENAME);
            $screenshotName = strtolower(str_replace('_', '-', $slug)) . '.png';
            $screenshotPath = $screenshotsDir . '/' . $screenshotName;

            $this->info("Guide: {$guide['title']}");
            $this->info("URL: {$baseUrl}/guides/{$filename}");
            $this->info("Screenshot: {$screenshotPath}");
            $this->newLine();

            // Note: This command provides the information needed to generate screenshots
            // Actual screenshot generation should be done manually or via browser automation
            // that handles authentication
        }

        $this->info('Screenshot generation instructions displayed above.');
        $this->info('Please use a browser automation tool (e.g., Selenium, Puppeteer)');
        $this->info('or take screenshots manually after logging in.');

        return Command::SUCCESS;
    }
}

