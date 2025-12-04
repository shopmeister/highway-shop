<?php declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Shopware\Core\Framework\Log\Package;

#[AsCommand(
    name: 'dde:check:column-widths',
    description: 'Validate mj-column widths of mj-section elements in document templates.'
)]
#[Package('core')]
class CheckColumnWidthsCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $themeDir = __DIR__ . '/../Resources/app/administration/src/app/core/theme';

        $finder = new Finder();
        $finder->files()->in($themeDir)->name('*.json');

        foreach ($finder as $file) {
            $path = $file->getRealPath();
            if ($path === false) {
                continue;
            }

            $content = file_get_contents($path);
            if ($content === false) {
                continue;
            }

            $json = json_decode($content, true);
            if (!\is_array($json)) {
                continue;
            }

            $this->checkNode($json, $path, $io);
        }

        return Command::SUCCESS;
    }

    private function checkNode(array $node, string $path, SymfonyStyle $io): void
    {
        if (($node['tagName'] ?? '') === 'mj-section') {
            $sum = 0.0;
            $hasWidth = false;

            foreach ($node['children'] ?? [] as $child) {
                if (($child['tagName'] ?? '') !== 'mj-column') {
                    continue;
                }

                $width = $child['attributes']['width'] ?? null;
                if (\is_string($width)) {
                    $hasWidth = true;
                    $sum += (float) rtrim($width, '%');
                }
            }

            if ($hasWidth) {
                $sum = round($sum, 2);
                if (abs($sum - 100.0) > 0.01 && isset($node['uid'])) {
                    $io->writeln(sprintf('%s -> %s (%s%%)', $path, $node['uid'], $sum));
                }
            }
        }

        foreach ($node['children'] ?? [] as $child) {
            if (\is_array($child)) {
                $this->checkNode($child, $path, $io);
            }
        }
    }
}
