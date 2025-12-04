<?php

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Subscriber;

use Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\Event\BeforeRenderTwigTemplateEvent;
use DOMDocument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TwigTemplateOptimizationSubscriber implements EventSubscriberInterface
{
    private const EMPTY_STATE_IMAGE_PATH = 'administration/static/img/empty-states/media-empty-state.svg';

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeRenderTwigTemplateEvent::class => 'onBeforeEscapeTwigBlocks',
        ];
    }

    public function onBeforeEscapeTwigBlocks(BeforeRenderTwigTemplateEvent $event): void
    {
        $twigTemplate = $event->getTwigTemplate();

        $twigTemplate = $this->replaceEmptyStateImageSources($twigTemplate);
        $twigTemplate = $this->removeHtmlComments($twigTemplate);
        $twigTemplate = $this->removeUnusedHtmlClassesAndIds($twigTemplate);
        $twigTemplate = $this->escapeTwigBlocks($twigTemplate);
        $twigTemplate = $this->convertBackgroundUrlQuotes($twigTemplate);
        $twigTemplate = $this->formatTwigHtml($twigTemplate);

        $event->setTwigTemplate(htmlspecialchars_decode($twigTemplate));
    }

    /**
     * Entfernt alle class- und id-Attribute aus HTML, die im CSS nicht referenziert werden.
     * Klassen, die mit "mj-" beginnen, werden niemals entfernt.
     *
     * @param string $template HTML- oder Twig-Inhalt
     * @return string HTML ohne ungenutzte Klassen und IDs (außer mj-*)
     */
    function removeUnusedHtmlClassesAndIds(string $template): string
    {
        // 1. Alle im CSS verwendeten Klassen und IDs extrahieren
        $usedClasses = [];
        $usedIds = [];

        preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $template, $styleBlocks);
        foreach ($styleBlocks[1] as $css) {
            // Klassen: .classname
            preg_match_all('/\.([-_a-zA-Z0-9]+)/', $css, $classMatches);
            foreach ($classMatches[1] as $class) {
                $usedClasses[$class] = true;
            }

            // IDs: #idname
            preg_match_all('/#([-_a-zA-Z0-9]+)/', $css, $idMatches);
            foreach ($idMatches[1] as $id) {
                $usedIds[$id] = true;
            }
        }

        // 2. class-Attribute bereinigen
        $template = preg_replace_callback('/class=["\']([^"\']+)["\']/', function ($match) use ($usedClasses) {
            $classes = preg_split('/\s+/', trim($match[1]));
            $remaining = array_filter($classes, function ($c) use ($usedClasses) {
                return isset($usedClasses[$c]) || str_starts_with($c, 'mj-');
            });
            return count($remaining) > 0 ? 'class="' . implode(' ', $remaining) . '"' : '';
        }, $template);

        // 3. id-Attribute bereinigen
        $template = preg_replace_callback('/id=["\']([^"\']+)["\']/', function ($match) use ($usedIds) {
            return isset($usedIds[$match[1]]) ? $match[0] : '';
        }, $template);

        return $template;
    }

    /**
     * Escapes critical characters inside Twig code blocks like `{% ... %}` to make them safe for HTML display.
     *
     * It replaces:
     *   <  → &lt;
     *   >  → &gt;
     *   && → &amp;&amp;
     *
     * Example:
     *   {% if foo && bar < 5 %}
     *   becomes:
     *   {% if foo &amp;&amp; bar &lt; 5 %}
     *
     * @param string $twigTemplate The input template as a string.
     * @return string The modified template with escaped characters inside Twig blocks.
     */
    function escapeTwigBlocks(string $twigTemplate): string
    {
        // Regex findet Twig-Blöcke wie {% ... %}
        return preg_replace_callback(
            '/\{%[^%]*%\}/',
            function ($twigBlocks) {
                foreach ($twigBlocks as $twigBlock) {
                    $twigBlock = str_replace('<', '&lt;', $twigBlock);
                    $twigBlock = str_replace('>', '&gt;', $twigBlock);
                    $twigBlock = str_replace('&&', '&amp;&amp;', $twigBlock);
                    return $twigBlock;
                }
                return '';
            },
            $twigTemplate
        );
    }


    /**
     * Converts CSS background URLs in a Twig template from double quotes to single quotes.
     *
     * Example:
     *   background: url("https://example.com/image.jpg");
     *   becomes
     *   background: url('https://example.com/image.jpg');
     *
     * @param string $twigTemplate The content of the Twig template as a string.
     * @return string The modified Twig template with updated background URL quotes.
     */
    function convertBackgroundUrlQuotes(string $twigTemplate): string
    {
        // Regulärer Ausdruck sucht nach: background: url("...") mit doppelten Anführungszeichen
        // Erfassung erfolgt nur, wenn "background: url(" gefolgt von beliebigem Inhalt in doppelten Anführungszeichen ist
        $pattern = '/background:\s*url\("([^"]*)"\)/';

        // Ersetze die doppelten Anführungszeichen um die URL durch einfache Anführungszeichen
        // $1 ist der Inhalt innerhalb der Anführungszeichen
        $replacement = "background: url('$1')";

        // Wende das Muster auf das Template an und gib das Ergebnis zurück
        return preg_replace($pattern, $replacement, $twigTemplate);
    }

    private function replaceEmptyStateImageSources(string $twigTemplate): string
    {
        return preg_replace_callback('/<img\b[^>]*>/i', function (array $matches) {
            $imgTag = $matches[0];

            if (!preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/i', $imgTag, $srcMatch)) {
                return $imgTag;
            }

            $srcValue = $srcMatch[2];
            if (!str_ends_with($srcValue, self::EMPTY_STATE_IMAGE_PATH)) {
                return $imgTag;
            }

            if (!preg_match('/\balt\s*=\s*(["\'])(.*?)\1/i', $imgTag, $altMatch)) {
                return $imgTag;
            }

            $altValue = trim($altMatch[2]);
            if ($altValue === '' || !$this->isValidTwigVariableName($altValue)) {
                return $imgTag;
            }

            $newSrc = 'src="{{ ' . $altValue . ' }}"';

            return preg_replace('/\bsrc\s*=\s*(["\']).*?\1/i', $newSrc, $imgTag, 1);
        }, $twigTemplate) ?? $twigTemplate;
    }

    private function isValidTwigVariableName(string $variableName): bool
    {
        return (bool) preg_match('/^[A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)*$/', $variableName);
    }

    /**
     * Entfernt alle HTML-Kommentare aus einem Template
     *
     * @param string $twigTemplate HTML- oder Twig-Inhalt mit Kommentaren
     * @return string Kommentarfreier Inhalt
     */
    function removeHtmlComments(string $twigTemplate): string
    {
        return preg_replace('/<!--.*?-->/s', '', $twigTemplate);
    }

    private function formatTwigHtml(string $code): string
    {
        $indentLevel = 0;
        $indentSize = 4;
        $formattedLines = [];
        $htmlTagStack = [];

        // 1. Schritt: Vor jedem Twig-Tag eine Zeilenumbruch erzwingen, falls nicht schon am Zeilenanfang
        // Wir ersetzen alle `{%` mit "\n{%" mit Ausnahme, wenn schon am Zeilenanfang.
        // Dann splitten wir sauber.

        // Füge vor jedem Twig-Tag (ausgenommen am Zeilenanfang) einen Zeilenumbruch ein
        $code = preg_replace('/([^\n])(\{\%)/', "$1\n$2", $code);

        // Hilfsfunktion: Zerlegt eine Zeile in einzelne „Tokens“ an Twig- oder HTML-Tag-Grenzen

        $lines = preg_split('/\r\n|\r|\n/', $code);

        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if ($trimmedLine === '') continue; // Leere Zeilen überspringen

            $tokens = $this->splitIntoTokens($line);

            foreach ($tokens as $token) {
                if ($token === '') continue;

                // Twig End-Tags indent reduzieren
                if (preg_match('/^{%\s*end(?:if|for|block|macro|filter|autoescape|embed|spaceless|verbatim|raw|trans)\s*%}$/', $token)) {
                    $indentLevel = max(0, $indentLevel - 1);
                }

                // HTML Closing Tag indent reduzieren
                if (preg_match('#^</([a-zA-Z0-9\-]+)>#', $token, $matches)) {
                    $lastTag = end($htmlTagStack);
                    if ($lastTag === $matches[1]) {
                        array_pop($htmlTagStack);
                        $indentLevel = max(0, $indentLevel - 1);
                    }
                }

                // Zeile mit aktueller Einrückung speichern
                $formattedLines[] = str_repeat(' ', $indentLevel * $indentSize) . $token;

                // Twig Start-Tags indent erhöhen
                if (preg_match('/^{%\s*(?:if|for|block|macro|filter|autoescape|embed|spaceless|verbatim|raw|trans)[^%]*%}$/', $token)) {
                    $indentLevel++;
                }

                // HTML Opening Tag indent erhöhen (außer selbstschließend)
                if (preg_match('#^<([a-zA-Z0-9\-]+)(\s[^>]*)?>#', $token, $matches)) {
                    $tag = $matches[1];
                    if (!preg_match('#/>$#', $token) && !in_array(strtolower($tag), ['br', 'img', 'input', 'hr', 'meta', 'link'])) {
                        $htmlTagStack[] = $tag;
                        $indentLevel++;
                    }
                }
            }
        }

        return implode("\n", $formattedLines);
    }

    private function splitIntoTokens(string $line): array
    {
        $pattern = '/
            (
                {%\s*(?:end(?:if|for|block|macro|filter|autoescape|embed|spaceless|verbatim|raw|trans)|if|for|block|macro|filter|autoescape|embed|spaceless|verbatim|raw|trans)[^%]*%}   # Twig Tags (non-capturing Gruppen)
                |
                <\/?[a-zA-Z0-9\-]+(?:\s[^>]*?)?\/?>   # HTML Tags (inkl. selbstschließende)
            )
        /x';

        $parts = preg_split($pattern, $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return array_map('trim', $parts);
    }


}
