<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Subscriber;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event\AfterRenderHtmlEvent;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\Logger;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\TwigEnvironmentFactory;
use DOMDocument;
use DOMElement;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

/** @noinspection PhpUnused */
class HtmlCleanupSubscriber implements EventSubscriberInterface
{
    private string $normalizedBaseUrl = '';

    private string $basePath = '';

    public function __construct(
        private readonly Logger        $logger,
        private readonly TwigEnvironmentFactory $twigEnvironmentFactory,
        private readonly string $appUrl = ''
    ) {
        $this->initializeBaseUrl($this->appUrl);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterRenderHtmlEvent::class => ['onAfterRenderHtml', -1016], // Medium priority
        ];
    }

    /** @noinspection PhpUnused */
    public function onAfterRenderHtml(AfterRenderHtmlEvent $event): void
    {
        $this->logger->logExecutionDuration(function () use ($event) {
            $html = $this->postManipulateHtml($event->getHtml());
            $event->setHtml($html);
        }, "- Cleanup html duration: %s ms");
    }

    /**
     * @param $html
     * @return string
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function postManipulateHtml($html): string
    {
        $doc = new DOMDocument();

        $doc->loadHTML($html);
        if ($doc->hasChildNodes()) {
            $this->postManipulateTree($doc, $doc);
        }
        $newHtml = $doc->saveHTML();

        // Clean up
        $newHtml = str_replace('background:;', '', $newHtml);
        $newHtml = str_replace('background-color:;', '', $newHtml);
        $newHtml = str_replace('style=""', '', $newHtml);
        $newHtml = str_replace("\u{00a0}", ' ', $newHtml);
        return preg_replace('/<!--.*?-->/s', '', $newHtml);
    }

    /**
     * Recursive Method, which walks the Node Tree of DOMDocument
     * @throws SyntaxError
     * @throws LoaderError
     */
    private function postManipulateTree($doc, $node): void
    {
        /** @var $node DOMElement */
        /** @var $childNode DOMElement */
        foreach ($node->childNodes as $childNode) {
            if ($childNode->hasChildNodes()) {
                $this->postManipulateTree($doc, $childNode);
            }

            // Remove zero font-size style attribute settings in page break sections
            if (method_exists($childNode, "hasAttribute") && method_exists($childNode, "getAttribute") && method_exists($childNode, "setAttribute") && $childNode->hasAttribute("style")) {
                if ($childNode->hasAttribute("class") && str_contains($childNode->getAttribute("class"), "mj-section--page-break")) {
                    if ($childNode->getElementsByTagName("td")->length == 1) {
                        $pageBreakTd = $childNode->getElementsByTagName("td")->item(0);
                        $zeroFontSizePattern = "/(.*)font-size:0px;?(.*)/";
                        if ($pageBreakTd->hasAttribute("style") && $style = $pageBreakTd->getAttribute("style")) {
                            if (preg_match($zeroFontSizePattern, $style)) {
                                $pageBreakTd->setAttribute("style", preg_replace($zeroFontSizePattern, "$1$2", $style));
                            }
                        }
                    }
                }
            }

            // Give <img>-Tags with empty src attribute a default src url
            if (isset($childNode->tagName) && $childNode->tagName == "img") {
                if (
                    method_exists($childNode, "hasAttribute") && method_exists($childNode, "getAttribute") && method_exists($childNode, "setAttribute") && $childNode->hasAttribute("alt") && $childNode->hasAttribute("src")
                    && ($childNode->getAttribute("src") == "" || !$this->remoteFileExists($childNode->getAttribute("src")))
                ) {
                    $twigEnvironment = $this->twigEnvironmentFactory->createTwigEnvironment();
                    $twig = $twigEnvironment->createTemplate("{{ asset('bundles/administration/static/img/empty-states/media-empty-state.svg', 'asset') }}");
                    $fallbackAssetUrl = trim(strip_tags($twig->render()));
                    $childNode->setAttribute("src", $fallbackAssetUrl);
                }
            }

            // Remove "font-size:0px" styles from mj-spacers
            if (
                isset($childNode->tagName) &&
                $childNode->tagName == "td" &&
                method_exists($childNode, "hasAttribute") &&
                method_exists($childNode, "getAttribute") &&
                method_exists($childNode, "setAttribute") &&
                $childNode->hasAttribute("class") &&
                $childNode->hasAttribute("style") &&
                str_contains($childNode->getAttribute("class"), "mj-spacer")
            ) {

                $style = $childNode->getAttribute("style");
                $style = str_replace("font-size:0px;", "", $style);
                $childNode->setAttribute("style", $style);
            }
        }
    }

    private function remoteFileExists($url): bool
    {
        if (preg_match('/^data:image\/[a-zA-Z0-9.+-]+;base64,[a-zA-Z0-9\/+=]+$/', $url)) {
            // Base64 encoded images should never be evaluated
            return true;
        }

        $normalizedUrl = $this->normalizeUrl($url);
        if ($normalizedUrl === '') {
            return true;
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $normalizedUrl);

        // Don't fetch the actual page, you only want to check the connection is ok
        curl_setopt($curl, CURLOPT_NOBODY, true);

        // Do request
        $result = curl_exec($curl);

        $ret = false;

        // If request didn't fail
        if ($result !== false) {
            //if the request was ok, check the response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($statusCode == 200) {
                $ret = true;
            }
        }

        curl_close($curl);

        return $ret;
    }

    private function initializeBaseUrl(string $appUrl): void
    {
        $parsedAppUrl = parse_url($appUrl);

        if ($parsedAppUrl === false || !isset($parsedAppUrl['host'])) {
            $this->normalizedBaseUrl = '';
            $this->basePath = '';
            return;
        }

        $scheme = $parsedAppUrl['scheme'] ?? 'https';
        $host = $parsedAppUrl['host'];
        $port = isset($parsedAppUrl['port']) ? ':' . $parsedAppUrl['port'] : '';

        $this->normalizedBaseUrl = rtrim(sprintf('%s://%s%s', $scheme, $host, $port), '/');
        $this->basePath = rtrim($parsedAppUrl['path'] ?? '', '/');
    }

    private function normalizeUrl(string $url): string
    {
        $trimmed = trim($url);

        if ($trimmed === '') {
            return '';
        }

        if ($this->normalizedBaseUrl === '') {
            return $trimmed;
        }

        if (str_starts_with($trimmed, '//')) {
            $scheme = parse_url($this->normalizedBaseUrl, PHP_URL_SCHEME) ?? 'https';
            return $scheme . ':' . $trimmed;
        }

        if (!preg_match('#^https?://#i', $trimmed)) {
            $path = '/' . ltrim($trimmed, '/');

            if ($this->basePath !== '' && str_starts_with($path, $this->basePath . '/')) {
                $path = substr($path, strlen($this->basePath));
                $path = '/' . ltrim($path, '/');
            }

            return $this->normalizedBaseUrl . $this->basePath . $path;
        }

        if ($this->basePath !== '') {
            $normalizedBasePath = '/' . ltrim($this->basePath, '/');
            $doubleBasePath = $normalizedBasePath . $normalizedBasePath;

            $trimmed = preg_replace(
                '#(^https?://[^/]+)' . preg_quote($doubleBasePath, '#') . '#i',
                '$1' . $normalizedBasePath,
                $trimmed,
                1
            );
        }

        return $trimmed;
    }
}
