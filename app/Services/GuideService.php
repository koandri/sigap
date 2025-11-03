<?php

declare(strict_types=1);

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Node;
use League\CommonMark\Normalizer\SlugNormalizer;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Xml\XmlNodeRendererInterface;

final class GuideService
{
    private const GUIDES_DIRECTORY = 'guides';

    private MarkdownConverter $markdownConverter;

    public function __construct()
    {
        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        // Add custom heading renderer with IDs
        $slugNormalizer = new SlugNormalizer();
        $environment->addRenderer(
            Heading::class,
            new class($slugNormalizer) implements NodeRendererInterface, XmlNodeRendererInterface
            {
                public function __construct(
                    private readonly SlugNormalizer $slugNormalizer
                ) {
                }

                public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string|null
                {
                    if (!($node instanceof Heading)) {
                        throw new \InvalidArgumentException('Incompatible node type: ' . get_class($node));
                    }

                    $tag = 'h' . $node->getLevel();
                    $attrs = $node->data->get('attributes', []);
                    
                    // Ensure attrs is an array
                    if (!is_array($attrs)) {
                        $attrs = [];
                    }

                    // Render children once
                    $renderedChildren = $childRenderer->renderNodes($node->children());

                    // Generate ID from heading text if not already set
                    if (empty($attrs['id'])) {
                        $headingTextPlain = strip_tags((string) $renderedChildren);
                        $id = $this->slugNormalizer->normalize($headingTextPlain);

                        if (!empty($id)) {
                            $attrs['id'] = $id;
                        }
                    }

                    return new HtmlElement($tag, $attrs, $renderedChildren);
                }

                public function getXmlTagName(Node $node): string
                {
                    return 'heading';
                }

                public function getXmlAttributes(Node $node): array
                {
                    if (!($node instanceof Heading)) {
                        throw new \InvalidArgumentException('Incompatible node type: ' . get_class($node));
                    }

                    $attrs = ['level' => (string) $node->getLevel()];

                    // Generate ID from heading text for XML too
                    $headingText = '';
                    foreach ($node->children() as $child) {
                        if (method_exists($child, 'getLiteral')) {
                            $headingText .= $child->getLiteral();
                        }
                    }

                    if (!empty($headingText)) {
                        $id = $this->slugNormalizer->normalize($headingText);
                        if (!empty($id)) {
                            $attrs['id'] = $id;
                        }
                    }

                    return $attrs;
                }
            }
        );

        $this->markdownConverter = new MarkdownConverter($environment);
    }

    /**
     * Get all available guides
     *
     * @return Collection<array{filename: string, title: string, description: string, path: string, modified: int}>
     */
    public function getAllGuides(): Collection
    {
        $guidesPath = base_path(self::GUIDES_DIRECTORY);
        
        if (!File::isDirectory($guidesPath)) {
            return collect();
        }

        $files = File::files($guidesPath);
        
        return collect($files)
            ->filter(fn ($file) => $file->getExtension() === 'md')
            ->map(function ($file) {
                $content = File::get($file->getPathname());
                $title = $this->extractTitle($content, $file->getFilenameWithoutExtension());
                
                return [
                    'filename' => $file->getFilename(),
                    'title' => $title,
                    'description' => $this->extractDescription($content),
                    'path' => $file->getPathname(),
                    'modified' => $file->getMTime(),
                ];
            })
            ->sortBy('title')
            ->values();
    }

    /**
     * Get guide content by filename
     */
    public function getGuideContent(string $filename): ?string
    {
        $filePath = base_path(self::GUIDES_DIRECTORY . '/' . $filename);
        
        if (!File::exists($filePath) || !File::isFile($filePath)) {
            return null;
        }

        return File::get($filePath);
    }

    /**
     * Convert markdown to HTML
     */
    public function markdownToHtml(string $markdown): string
    {
        return (string) $this->markdownConverter->convert($markdown)->getContent();
    }

    /**
     * Get title for a guide
     */
    public function getGuideTitle(string $filename): string
    {
        $content = $this->getGuideContent($filename);
        
        if ($content === null) {
            return pathinfo($filename, PATHINFO_FILENAME);
        }

        return $this->extractTitle($content, pathinfo($filename, PATHINFO_FILENAME));
    }

    /**
     * Generate HTML for a single guide
     */
    public function generateGuideHtml(string $filename, bool $includeHeader = true): ?string
    {
        $content = $this->getGuideContent($filename);
        
        if ($content === null) {
            return null;
        }

        $html = $this->markdownToHtml($content);
        $title = $this->extractTitle($content, pathinfo($filename, PATHINFO_FILENAME));

        if (!$includeHeader) {
            return $html;
        }

        return view('guides.show-html', [
            'title' => $title,
            'content' => $html,
            'filename' => $filename,
        ])->render();
    }

    /**
     * Generate PDF for a single guide
     */
    public function generateGuidePdf(string $filename): ?string
    {
        $content = $this->getGuideContent($filename);
        
        if ($content === null) {
            return null;
        }

        $html = $this->markdownToHtml($content);
        $title = $this->extractTitle($content, pathinfo($filename, PATHINFO_FILENAME));

        $pdf = Pdf::loadView('guides.show-pdf', [
            'title' => $title,
            'content' => $html,
            'filename' => $filename,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('margin-top', 20);
        $pdf->setOption('margin-bottom', 20);
        $pdf->setOption('margin-left', 20);
        $pdf->setOption('margin-right', 20);
        $pdf->setOption('enable-local-file-access', true);

        return $pdf->output();
    }

    /**
     * Generate combined PDF handbook with all guides
     */
    public function generateCombinedPdf(): string
    {
        $guides = $this->getAllGuides();
        
        $guidesData = $guides->map(function ($guide) {
            $content = $this->getGuideContent($guide['filename']);
            
            return [
                'title' => $guide['title'],
                'filename' => $guide['filename'],
                'html' => $content ? $this->markdownToHtml($content) : '',
            ];
        })->filter(fn ($guide) => !empty($guide['html']))->values();

        $pdf = Pdf::loadView('guides.combined-pdf', [
            'guides' => $guidesData,
            'generatedAt' => now()->format('F d, Y'),
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('margin-top', 20);
        $pdf->setOption('margin-bottom', 20);
        $pdf->setOption('margin-left', 20);
        $pdf->setOption('margin-right', 20);
        $pdf->setOption('enable-local-file-access', true);

        return $pdf->output();
    }

    /**
     * Extract title from markdown content
     */
    private function extractTitle(string $content, string $fallback): string
    {
        // Try to get the first H1
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        // Fallback to filename
        return str_replace(['_', '-'], ' ', ucwords($fallback, '_-'));
    }

    /**
     * Extract description from markdown content (first paragraph after title)
     */
    private function extractDescription(string $content): string
    {
        // Remove title line
        $content = preg_replace('/^#.*$/m', '', $content, 1);
        
        // Get first paragraph
        if (preg_match('/^\*\*(.+?)\*\*/m', $content, $matches)) {
            return trim(strip_tags($matches[1]));
        }

        // Try to get first paragraph
        $lines = explode("\n", trim($content));
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !preg_match('/^[#\-\*]/', $line) && strlen($line) > 20) {
                return substr($line, 0, 150) . (strlen($line) > 150 ? '...' : '');
            }
        }

        return '';
    }
}

