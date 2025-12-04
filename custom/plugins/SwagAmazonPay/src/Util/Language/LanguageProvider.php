<?php declare(strict_types=1);

namespace Swag\AmazonPay\Util\Language;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\AmazonPay\Components\Config\ConfigService;

readonly class LanguageProvider implements LanguageProviderInterface
{
    /**
     * The default value for the checkout language.
     */
    public const DEFAULT_LANGUAGE = 'en_GB';

    /**
     * Languages which are currently supported by Amazon Pay.
     */
    public const VALID_LANGUAGE_CODES = [
        'en_GB',
        'en_US',
        'de_DE',
        'fr_FR',
        'it_IT',
        'es_ES',
        'ja_JP',
    ];

    public function __construct(
        private EntityRepository $languageRepository,
        private ConfigService $configService
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAmazonPayButtonLanguage(string $storefrontLanguageId, ?Context $context = null, ?string $salesChannelId = null): string
    {
        if ($context === null) {
            $context = Context::createDefaultContext();
        }

        $criteria = new Criteria([$storefrontLanguageId]);
        $criteria->addAssociation('locale');

        $language = $this->languageRepository->search($criteria, $context)->first();

        if ($language === null || $language->getLocale() === null) {
            $languageLocale = self::DEFAULT_LANGUAGE;
        } else {
            $languageLocale = $language->getLocale()->getCode();
        }

        // en-GB -> en_GB conversion
        $languageLocale = \str_replace('-', '_', $languageLocale);

        if (!\in_array($languageLocale, self::VALID_LANGUAGE_CODES, true)) {
            $shortLocale = substr($languageLocale, 0, 2);
            foreach(self::VALID_LANGUAGE_CODES as $code) {
                if(substr($code, 0, 2) === $shortLocale) {
                    $languageLocale = $code;
                    break;
                }
            }
        }

        if (!\in_array($languageLocale, self::VALID_LANGUAGE_CODES, true)) {
            $languageLocale = self::DEFAULT_LANGUAGE;
        }

        if ($languageLocale == 'en_GB') {
            try {
                // make en_US for US shops
                $pluginConfig = $this->configService->getPluginConfig($salesChannelId);
                if ($pluginConfig->getLedgerCurrency() == 'USD') {
                    $languageLocale = 'en_US';
                }
            }catch (\Exception $e) {
                // do nothing
            }
        }

        return $languageLocale;
    }
}
