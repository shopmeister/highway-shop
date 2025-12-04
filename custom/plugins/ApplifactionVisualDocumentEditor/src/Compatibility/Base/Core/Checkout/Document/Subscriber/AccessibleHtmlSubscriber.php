<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Subscriber;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\DocumentEditorHelperInterface;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event\AfterRenderHtmlEvent;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event\BeforeRenderDomPdfEvent;
use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Service\Logger;
use DOMDocument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccessibleHtmlSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly DocumentEditorHelperInterface $helper,
        private readonly Logger                        $logger
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeRenderDomPdfEvent::class => 'onBeforeRender',
            AfterRenderHtmlEvent::class => ['onAfterRenderHtml', 1024], // Lowest priority
        ];
    }

    public function onAfterRenderHtml(AfterRenderHtmlEvent $event): void
    {
        $this->logger->logExecutionDuration(function () use ($event) {
            $context = $event->getContext();
            $document = $event->getDocument();

            $lang = 'en';
            if ($context) {
                $code = $this->helper->getLocaleCodeByLanguageId($context->getLanguageId());
                if ($code !== '') {
                    $lang = $code;
                }
            }

            $doc = new DOMDocument();
            @$doc->loadHTML($event->getHtml());

            $htmlElement = $doc->getElementsByTagName('html')->item(0);
            if (!$htmlElement) {
                $htmlElement = $doc->appendChild($doc->createElement('html'));
            }
            if (!$htmlElement->hasAttribute('lang')) {
                $htmlElement->setAttribute('lang', $lang);
            }

            $head = $doc->getElementsByTagName('head')->item(0);
            if (!$head) {
                $head = $doc->createElement('head');
                $htmlElement->insertBefore($head, $htmlElement->firstChild);
            }

            $hasCharset = false;
            foreach ($head->getElementsByTagName('meta') as $meta) {
                if ($meta->hasAttribute('charset')) {
                    $hasCharset = true;
                    break;
                }
            }
            if (!$hasCharset) {
                $meta = $doc->createElement('meta');
                $meta->setAttribute('charset', 'utf-8');
                $head->insertBefore($meta, $head->firstChild);
            }

            $title = 'Document';
            if ($document && $document->getDocumentType()) {
                $name = $document->getDocumentType()->getTechnicalName();
                if ($name) {
                    $title = $name;
                }
            }
            $titleElement = $head->getElementsByTagName('title')->item(0);
            if (!$titleElement) {
                $titleElement = $doc->createElement('title');
                $head->appendChild($titleElement);
            }
            $titleElement->nodeValue = $title;

            $author = '';
            if ($document) {
                $config = $document->getConfig();
                if (is_array($config) && isset($config['companyName'])) {
                    $author = (string)$config['companyName'];
                }
            }

            $hasAuthor = false;
            foreach ($head->getElementsByTagName('meta') as $meta) {
                if ($meta->hasAttribute('name') && strtolower($meta->getAttribute('name')) === 'author') {
                    $hasAuthor = true;
                    break;
                }
            }
            if (!$hasAuthor && $author !== '') {
                $meta = $doc->createElement('meta');
                $meta->setAttribute('name', 'author');
                $meta->setAttribute('content', $author);
                $head->appendChild($meta);
            }

            if ($author !== '') {
                foreach ($doc->getElementsByTagName('img') as $img) {
                    if (!$img->hasAttribute('alt')) {
                        $img->setAttribute('alt', $author);
                    }
                }
            }

            $hasDate = false;
            foreach ($head->getElementsByTagName('meta') as $meta) {
                if ($meta->hasAttribute('name') && strtolower($meta->getAttribute('name')) === 'date') {
                    $hasDate = true;
                    break;
                }
            }
            if (!$hasDate) {
                $meta = $doc->createElement('meta');
                $meta->setAttribute('name', 'date');
                $meta->setAttribute('content', (new \DateTime())->format('c'));
                $head->appendChild($meta);
            }

            $body = $doc->getElementsByTagName('body')->item(0);
            if (!$body) {
                $body = $doc->createElement('body');
                $htmlElement->appendChild($body);
            }

            if (!$body->hasAttribute('role')) {
                $body->setAttribute('role', 'document');
            }

            // Find div with largest font-size and convert it to h1
            $largestDiv = null;
            $largestSize = -INF;
            foreach ($doc->getElementsByTagName('div') as $div) {
                if ($div->hasAttribute('style')) {
                    $style = $div->getAttribute('style');
                    if (preg_match('/font-size\s*:\s*([0-9.]+)(px|pt|em|rem|%)/i', $style, $match)) {
                        $size = (float)$match[1];
                        if ($size > $largestSize) {
                            $largestSize = $size;
                            $largestDiv = $div;
                        }
                    }
                }
            }

            if ($largestDiv instanceof \DOMElement) {
                $h1 = $doc->createElement('h1');
                // transfer children
                while ($largestDiv->firstChild) {
                    $h1->appendChild($largestDiv->firstChild);
                }
                // copy attributes except style
                foreach ($largestDiv->attributes as $attr) {
                    if ($attr->nodeName !== 'style') {
                        $h1->setAttribute($attr->nodeName, $attr->nodeValue);
                    }
                }
                $style = $largestDiv->getAttribute('style');
                $style = rtrim($style);
                if ($style !== '' && !str_ends_with($style, ';')) {
                    $style .= ';';
                }
                $style .= 'margin-bottom: 0; margin-top: 0;';
                $h1->setAttribute('style', $style);
                $h1->setAttribute('tabindex', 0);
                $largestDiv->parentNode->replaceChild($h1, $largestDiv);
            }

            $tabIndex = 1;
            foreach ($doc->getElementsByTagName('img') as $img) {
                if (!$img->hasAttribute('tabindex')) {
                    $img->setAttribute('tabindex', $tabIndex++);
                }
            }
            foreach ($doc->getElementsByTagName('a') as $a) {
                if (!$a->hasAttribute('tabindex')) {
                    $a->setAttribute('tabindex', $tabIndex++);
                }
            }
            foreach ($doc->getElementsByTagName('div') as $div) {
                $style = $div->getAttribute('style');
                $isBold = false;
                if ($style !== '' && preg_match('/font-weight\s*:\s*([^;]+)/i', $style, $match)) {
                    $weight = trim($match[1]);
                    if ($weight === 'bold' || (is_numeric($weight) && (int)$weight >= 400)) {
                        $isBold = true;
                    }
                }
                if (!$isBold) {
                    foreach (['strong', 'b'] as $tag) {
                        if ($div->getElementsByTagName($tag)->length > 0) {
                            $isBold = true;
                            break;
                        }
                    }
                }
                if ($isBold && $this->containsPrice($div->textContent) && !$div->hasAttribute('tabindex')) {
                    $div->setAttribute('tabindex', $tabIndex++);
                }
            }

            $event->setHtml($doc->saveHTML());
        }, "- Accessibility html optimization duration: %s ms");
    }

    public function onBeforeRender(BeforeRenderDomPdfEvent $event): void
    {
        $this->logger->logExecutionDuration(function () use ($event) {

            $dompdf = $event->getDompdf();
            $document = $event->getDocument();

            $locale = '';
            $context = null;

            if (method_exists($document, 'getContext')) {
                // Old shopware versions (e.g., 6.9.0.0) don't have the getContext method
                $context = $document->getContext();
            }

            if ($context && method_exists($context, 'getLanguageId')) {
                $locale = $this->helper->getLocaleCodeByLanguageId($context->getLanguageId());
            } elseif (preg_match('/<html[^>]*lang="([^"]+)"/i', $document->getHtml(), $match)) {
                $locale = $match[1];
            }

            if ($locale !== '') {
                $dompdf->addInfo('Lang', $locale);
            }

            $author = '';
            $html = $document->getHtml();
            if (preg_match('/<meta[^>]*name=["\']author["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $match)) {
                $author = $match[1];
            } elseif (preg_match('/<meta[^>]*content=["\']([^"\']+)["\'][^>]*name=["\']author["\']/i', $html, $match)) {
                $author = $match[1];
            }

            if ($author !== '') {
                $dompdf->addInfo('Author', $author);
            }

            $title = '';
            if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $document->getHtml(), $match)) {
                $title = trim($match[1]);
            }

            if ($title !== '') {
                $dompdf->addInfo('Title', $title);
            }

        }, "- Accessibility document optimization duration: %s ms");
    }

    private function containsPrice(string $text): bool
    {
        return preg_match('/(?:\p{Sc}|[A-Z]{3})?\s*\d{1,3}(?:[.,\s]\d{3})*(?:[.,]\d+)?\s*(?:\p{Sc}|[A-Z]{3})?/u', $text) === 1;
    }

}
