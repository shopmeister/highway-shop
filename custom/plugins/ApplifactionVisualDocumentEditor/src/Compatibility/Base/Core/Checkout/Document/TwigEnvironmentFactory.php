<?php declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document;

use Cocur\Slugify\Bridge\Twig\SlugifyExtension;
use Shopware\Core\Framework\Adapter\Twig\Extension\PhpSyntaxExtension;
use Shopware\Core\Framework\Adapter\Twig\SecurityExtension;
use Cocur\Slugify\SlugifyInterface;
use Shopware\Core\Framework\Adapter\Twig\TwigEnvironment;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\ArrayLoader;

class TwigEnvironmentFactory
{

    private iterable $twigExtensions;
    private SlugifyInterface $slugify;

    public function __construct(iterable $twigExtensions, SlugifyInterface $slugify)
    {
        $this->twigExtensions = $twigExtensions;
        $this->slugify = $slugify;
    }

    /**
     * @param ExtensionInterface[] $twigExtensions
     */
    public function createTwigEnvironment(): Environment
    {
        if (class_exists('Shopware\Core\Framework\Adapter\Twig\TwigEnvironment')) {
            $twig = new TwigEnvironment(new ArrayLoader());
        } else {
            $twig = new Environment(new ArrayLoader());
        }
        $twig->setCache(false);
        $twig->disableStrictVariables();

        if (class_exists('Cocur\Slugify\Bridge\Twig\SlugifyExtension')) {
            $twig->addExtension(new SlugifyExtension($this->slugify));
        }
        if (class_exists('Shopware\Core\Framework\Adapter\Twig\Extension\PhpSyntaxExtension')) {
            $twig->addExtension(new PhpSyntaxExtension());
        }
        if (class_exists('Shopware\Core\Framework\Adapter\Twig\SecurityExtension')) {
            $twig->addExtension(new SecurityExtension([]));
        }

        foreach ($this->twigExtensions as $twigExtension) {
            if (!$twig->hasExtension(\get_class($twigExtension))) {
                $twig->addExtension($twigExtension);
            }
        }
        return $twig;
    }
}
