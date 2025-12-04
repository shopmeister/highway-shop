<?php declare(strict_types=1);

namespace Dtgs\RichSnippets\Subscriber;

use Dtgs\RichSnippets\Components\Helper\CustomerHelper;
use Dtgs\RichSnippets\Components\Helper\ProductHelper;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Page;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Storefront\Page\Product\Review\ProductReviewLoader;

class DetailSubscriber implements EventSubscriberInterface
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var ProductReviewLoader
     */
    private $productReviewLoader;

    const BEST_RATING_POINTS = 5;
    const WORST_RATING_POINTS = 1;

    /**
     * DetailSubscriber constructor.
     *
     * @param SystemConfigService $systemConfigService
     * @param ProductHelper $productHelper
     * @param CustomerHelper $customerHelper
     */
    public function __construct(SystemConfigService $systemConfigService,
                                ProductHelper $productHelper,
                                CustomerHelper $customerHelper,
                                ProductReviewLoader $productReviewLoader)
    {
        $this->systemConfigService = $systemConfigService;
        $this->productHelper = $productHelper;
        $this->customerHelper = $customerHelper;
        $this->productReviewLoader = $productReviewLoader;
    }

    public static function getSubscribedEvents(): array
    {
        return[
            ProductPageLoadedEvent::class => 'onProductPageLoaded'
        ];
    }

    /**
     * Event für alle Seiten
     *
     * @param ProductPageLoadedEvent $event
     * @throws \Exception
     */
    public function onProductPageLoaded($event): void
    {
        /** @var Page $page */
        $page = $event->getPage();
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();
        $productFromPage = $event->getPage()->getProduct();
        $product = $this->productHelper->getProductById($productFromPage->getId(), $event->getSalesChannelContext());
        $richSnippetConfig = $this->systemConfigService->get('DtgsRichSnippetsSw6.config', $salesChannelId);

        //load reviews
        $reviews = $this->productReviewLoader->load($event->getRequest(), $event->getSalesChannelContext());
        $reviews->setParentId($product->getParentId() ?? $product->getId());
        $event->getPage()->setReviews($reviews);

        $data = array();
        //Stammdaten
        $data['productID'] = $product->getId();
        $data['productName'] = $product->getTranslation('name');
        $data['productImage'] = ($product->getCover() !== null) ? $product->getCover()->getMedia()->getUrl() : '';
        //zusätzlicher Check eingebaut in 6.1.3 - Ticket CDVRS-0000017
        $data['productLink'] = ($product->getSeoUrls() !== null && $product->getSeoUrls()->first() !== null) ? $product->getSeoUrls()->first()->getSeoPathInfo() : '';
        $data['productPrice'] = 0;
        $data['productEAN'] = $product->getEan();
        $data['productSku'] = $product->getProductNumber();

        switch ($richSnippetConfig['fieldForMpn']) {
            case 'ean':
                $data['productMpn'] = $product->getEan();
                break;
            case 'suppliernumber':
                $data['productMpn'] = $product->getManufacturerNumber();
                break;
            case 'mpn_ean_separately':
            default:
                $data['productMpn'] = $product->getManufacturerNumber();
                //if ean is given, check the length and use different array keys
                $ean = $product->getEan();
                if($ean && strlen($ean) > 0) {
                    if(strlen($ean) == 8) {
                        $data['productGtin8'] = $ean;
                    }
                    elseif(strlen($ean) == 12) {
                        $data['productGtin12'] = $ean;
                    }
                    elseif(strlen($ean) == 13) {
                        $data['productGtin13'] = $ean;
                    }
                    elseif(strlen($ean) == 14) {
                        $data['productGtin14'] = $ean;
                    }
                }
                break;
        }

        $data['brandName'] = ($product->getManufacturer() !== null) ? $product->getManufacturer()->getTranslation('name') : '';
        $data['description'] = '';
        //Beschreibung
        $description = $product->getTranslation('description');
        if($description && strlen($description) > 0) {
            $data['description'] = $description;
        }
        else {
            $data['description'] = '';
        }
        $data['description'] = html_entity_decode(strip_tags($data['description']));

        //Offer
        $data['priceCurrency'] = $event->getSalesChannelContext()->getCurrency()->getIsoCode();

        //Neue Preislogik ab 6.1.8
        if($productFromPage->getCalculatedPrices()->count() > 1) {
            //Staffelpreise
            $data['tierPrices'] = true;
            $data['lowPrice'] = false;
            $data['highPrice'] = false;
            foreach($productFromPage->getCalculatedPrices() as $price) {
                if($price->getUnitPrice() < $data['lowPrice'] || $data['lowPrice'] === false) {
                    $data['lowPrice'] = $price->getUnitPrice();
                }
                if($price->getUnitPrice() > $data['highPrice']) {
                    $data['highPrice'] = $price->getUnitPrice();
                }
                $data['offerCount'] = $productFromPage->getCalculatedPrices()->count();
                $tierPrice['price'] = (is_float($price->getUnitPrice())) ? $price->getUnitPrice() : str_replace(',', '.', $price->getUnitPrice());
                $data['tierPricesArray'][] = $tierPrice;
            }
        }
        else {
            //Einzelpreis
            $data['tierPrices'] = false;
            $price = $productFromPage->getCalculatedPrice()->getUnitPrice();
            $data['price'] = (is_float($price)) ? $price : str_replace(',', '.', $price);
        }

        $data['itemCondition'] = 'https://schema.org/NewCondition';
        $data['availability'] = $this->getAvailability($product, $richSnippetConfig);
        $data['priceValidUntil'] = date('Y-m-d', strtotime("+5 years"));
        $data['sellerName'] = $event->getSalesChannelContext()->getSalesChannel()->getName();

        //Reviews
        if(isset($richSnippetConfig['showReviewTexts'])) {
            //Rating
            $data['bestRating'] = self::BEST_RATING_POINTS;
            $data['worstRating'] = self::WORST_RATING_POINTS;
        }

        $page->addExtension('RichSnippetData', new ArrayEntity([
            'data' => $data
        ]));
    }

    /**
     * since 6.1.1
     *
     * @param ProductEntity $product
     * @param $pluginConfig
     * @return string
     */
    private function getAvailability(ProductEntity $product, $pluginConfig): string
    {

        if($product->getStock() > 0) {
            return 'https://schema.org/InStock';
        }
        else {
            if(!$product->getIsCloseout() && $pluginConfig['outofstockOnlyForClearanceProducts']) {
                return 'https://schema.org/InStock';
            }
            return 'https://schema.org/OutOfStock';
        }

    }

}
