<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\GuideService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GuideController extends Controller
{
    public function __construct(
        private readonly GuideService $guideService
    ) {
    }

    /**
     * Display the guides index page
     */
    public function index()
    {
        $guides = $this->guideService->getAllGuides();

        return view('guides.index', [
            'guides' => $guides,
        ]);
    }

    /**
     * Display a single guide in HTML format
     */
    public function show(string $filename): RedirectResponse|\Illuminate\Contracts\View\View
    {
        $content = $this->guideService->getGuideContent($filename);

        if ($content === null) {
            return redirect()->route('guides.index')
                ->with('error', 'Guide not found.');
        }

        $html = $this->guideService->generateGuideHtml($filename);

        if ($html === null) {
            return redirect()->route('guides.index')
                ->with('error', 'Failed to generate guide content.');
        }

        $title = $this->guideService->getGuideTitle($filename);

        return view('guides.show', [
            'title' => $title,
            'content' => $html,
            'filename' => $filename,
        ]);
    }

    /**
     * Download a single guide as PDF
     */
    public function downloadPdf(string $filename): StreamedResponse|RedirectResponse
    {
        $pdfContent = $this->guideService->generateGuidePdf($filename);

        if ($pdfContent === null) {
            return redirect()->route('guides.index')
                ->with('error', 'Guide not found or could not be generated.');
        }

        $title = $this->guideService->getGuideTitle($filename);

        $safeFilename = \Illuminate\Support\Str::slug($title) . '.pdf';

        return ResponseFacade::streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $safeFilename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $safeFilename . '"',
        ]);
    }

    /**
     * Download combined PDF handbook with all guides
     */
    public function downloadCombinedPdf(): StreamedResponse
    {
        $pdfContent = $this->guideService->generateCombinedPdf();

        $filename = 'SIGaP-Complete-User-Handbook-' . now()->format('Y-m-d') . '.pdf';

        return ResponseFacade::streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

