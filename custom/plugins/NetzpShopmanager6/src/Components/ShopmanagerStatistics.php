<?php
namespace NetzpShopmanager6\Components;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\DateHistogramAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\CountAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\SumAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use DateTime;
use DateInterval;

class ShopmanagerStatistics
{
    final public const PRICEMODE_GROSS   = 'gross';
    final public const PRICEMODE_NET     = 'net';

    protected $timeZone = '';

    public function __construct(private readonly ShopmanagerHelper $helper,
                                private readonly ShopmanagerVisitors $visitors,
                                private readonly EntityRepository $orderRepository,
                                private readonly EntityRepository $customerRepository,
                                private readonly EntityRepository $statisticsRepository,
    )
    {
        $this->timeZone = trim((string)$this->helper->getConfig('timezone', ''));
        if($this->timeZone != '')
        {
            try
            {
                $tmpTimeZone = new \DateTimeZone($this->timeZone);
            }
            catch(\Exception)
            {
                $this->timeZone = '';
            }
        }
    }

    public function getStatistics($context, $range, $salesChannelId = '')
    {
        $data = [];
        $priceMode = $this->helper->getConfig('pricemode', ShopmanagerStatistics::PRICEMODE_GROSS, $salesChannelId);
        $countVisitors = $this->helper->getConfig('countvisitors', false, $salesChannelId);
        $demoMode = $this->helper->getConfig('demomode', false, $salesChannelId);

        $fromDate = null;
        $toDate = null;
        $fromDateGD = null;
        $rangeFrom = null;
        $rangeTo = null;
        $timeBack = null;
        $rangeGDFrom = null;
        $rangeGDTo = null;
        $rangeGD = null;

        $this->setRangesFromInterval(
            $range, $fromDate, $toDate, $fromDateGD, $rangeFrom, $rangeTo,
            $timeBack, $rangeGDFrom, $rangeGDTo, $rangeGD
        );

        $fromDateToday = new DateTime();
        if($this->timeZone != '')
        {
            $fromDateToday->setTimeZone(new \DateTimeZone($this->timeZone));
            $fromDateToday->setTime(0, 0, 0);
            $fromDateToday->setTimeZone(new \DateTimeZone('UTC'));
        }
        else
        {
            $fromDateToday->setTimeZone(new \DateTimeZone('UTC'));
            $fromDateToday->setTime(0, 0, 0);
        }

        $toDateToday = new DateTime();
        if($this->timeZone != '')
        {
            $toDateToday->setTimeZone(new \DateTimeZone($this->timeZone));
            $toDateToday->setTime(23, 59, 59);
            $toDateToday->setTimeZone(new \DateTimeZone('UTC'));
        }
        else
        {
            $toDateToday->setTimeZone(new \DateTimeZone('UTC'));
            $toDateToday->setTime(23, 59, 59);
        }

        $turnoverToday = $this->getTurnover($salesChannelId, $context,
                                            'day', $fromDateToday, $toDateToday, $priceMode);

        if((is_countable($turnoverToday) ? count($turnoverToday) : 0) > 0) {
            $turnoverToday = array_shift($turnoverToday);
        }

        $items = [];
        $sma = [];

        $this->getAlldata($salesChannelId, $context,
            $range, $fromDate, $fromDateGD, $toDate, $priceMode,
            $rangeTo, $rangeGDTo, $rangeGD, $demoMode,
            $items, $sma);

        $resOrderStatus = $this->getOrderStatus($salesChannelId, $context);
        $currentUsers = $this->visitors->getCurrentUsers($context, $salesChannelId);
        $conversionRate = $this->getConversionRate($items);

        if ( ! $demoMode) { // LIVE data
            $stats = [
                'todayTurnover'     => (float)$this->arrayGet($turnoverToday, 'turnover'),
                'todayVisitors'     => 0, // not needed - not calculated
                'todayNewCustomers' => 0, // not needed - not calculated
                'todayOrders'       => (int)$this->arrayGet($turnoverToday, 'orderCount'),

                'ordersOpen'        => (int)$this->arrayGet($resOrderStatus, 'ordersOpen'),
                'ordersPending'     => (int)$this->arrayGet($resOrderStatus, 'ordersPending'),
                'totalTurnover'     => 0, // calculated in app 
                'averageTurnover'   => 0, // calculated in app
                'totalOrders'       => 0, // calculated in app 
                'totalCustomers'    => 0, // calculated in app
                'totalVisitors'     => 0, // calculated in app

                'conversion'        => $countVisitors ? round($conversionRate, 2) : -1,
                'currentUsers'      => $countVisitors ? (int)$currentUsers : -1,

                'gd'                => array_values($sma),

                'dateFrom'          => $fromDate,
                'dateTo'            => $toDate
            ];
        }

        else { // DEMO data
            $stats = [
                'todayTurnover'     => random_int(5000, 50000),
                'todayVisitors'     => 0, // not needed - not calculated
                'todayNewCustomers' => 0, // not needed - not calculated
                'todayOrders'       => random_int(100, 500),

                'ordersOpen'        => random_int(10, 50),
                'ordersPending'     => random_int(10, 50),
                'totalTurnover'     => 0, // calculated in app 
                'averageTurnover'   => 0, // calculated in app
                'totalOrders'       => 0, // calculated in app 
                'totalCustomers'    => 0, // calculated in app 
                'totalVisitors'     => 0, // calculated in app

                'conversion'        => $countVisitors ? random_int(300, 600) / 100 : -1,
                'currentUsers'      => $countVisitors ? random_int(10, 299) : -1,

                'gd'                => [], // not calculated in demo mode

                'dateFrom'          => $fromDate,
                'dateTo'            => $toDate
            ];
        }

        $data['statistics']   = array_merge($stats, ['data' => $items]);
        $data['dateInterval'] = $range;
        $data['dateFrom']     = $fromDate->format('Y-m-d');
        $data['dateTo']       = $toDate->format('Y-m-d');

        unset($data['statistics']['dateFrom']);
        unset($data['statistics']['dateTo']);

        return $data;

    }

    private function setRangesFromInterval(
        &$range, &$fromDate, &$toDate,
        &$fromDateGD, &$rangeFrom, &$rangeTo, &$timeBack,
        &$rangeGDFrom, &$rangeGDTo, &$rangeGD)
    {

        $fromDate = new DateTime();
        $fromDateGD = new DateTime();
        $toDate = new DateTime();

        if($this->timeZone != '')
        {
            $fromDate->setTimeZone(new \DateTimeZone($this->timeZone));
            $fromDateGD->setTimeZone(new \DateTimeZone($this->timeZone));
            $toDate->setTimeZone(new \DateTimeZone($this->timeZone));
        }

        if ($range == 'day') {
            $fromDate = $fromDate->sub(new DateInterval('P7D'));
            $fromDateGD = $fromDateGD->sub(new DateInterval('P28D'));

            $rangeFrom = 0;
            $rangeTo = 7;
            $timeBack = 6; // time range for conversion ratio

            $rangeGDFrom = 0;
            $rangeGDTo = 28;
            $rangeGD = 7;
        }

        elseif ($range == 'day2') {
            $fromDate = $fromDate->sub(new DateInterval('P29D'));
            $fromDateGD = $fromDateGD->sub(new DateInterval('P56D'));

            $rangeFrom = 0;
            $rangeTo = 29;
            $timeBack = 29; // time range for conversion ratio

            $rangeGDFrom = 0;
            $rangeGDTo = 56;
            $rangeGD = 30;
        }

        elseif ($range == 'week') {
            $fromDate = $fromDate->sub(new DateInterval('P8W'));
            $fromDate = $fromDate->add(new DateInterval('P1D'));
            $fromDateGD = $fromDateGD->sub(new DateInterval('P16W'));
            $fromDateGD = $fromDateGD->add(new DateInterval('P1D'));

            $rangeFrom = 0;
            $rangeTo = 7;
            $timeBack = 56; // time range for conversion ratio

            $rangeGDFrom = 0;
            $rangeGDTo = 14;
            $rangeGD = 7;
        }

        elseif ($range == 'week2') {
            $fromDate = $fromDate->sub(new DateInterval('P16W'));
            $fromDate = $fromDate->add(new DateInterval('P1D'));
            $fromDateGD = $fromDateGD->sub(new DateInterval('P32W'));
            $fromDateGD = $fromDateGD->add(new DateInterval('P1D'));

            $rangeFrom = 0;
            $rangeTo = 15;
            $timeBack = 112; // time range for conversion ratio

            $rangeGDFrom = 0;
            $rangeGDTo = 28;
            $rangeGD = 14;
        }

        elseif ($range == 'month') {
            $fromDate = $fromDate->sub(new DateInterval('P12M'));
            $fromDate->setDate((int)$fromDate->format('Y'), (int)$fromDate->format('m'), 1);
            $fromDateGD = $fromDateGD->sub(new DateInterval('P24M'));
            $fromDateGD->setDate((int)$fromDateGD->format('Y'), (int)$fromDateGD->format('m'), 1);

            $rangeFrom = 0;
            $rangeTo = 12;
            $timeBack = 365; // time range for conversion ratio

            $rangeGDFrom = 0;
            $rangeGDTo = 24;
            $rangeGD = 12;
        }

        elseif ($range == 'month2') {
            $fromDate = $fromDate->sub(new DateInterval('P24M'));
            $fromDate->setDate((int)$fromDate->format('Y'), (int)$fromDate->format('m'), 1);
            $fromDateGD = $fromDateGD->sub(new DateInterval('P48M'));
            $fromDateGD->setDate((int)$fromDateGD->format('Y'), (int)$fromDateGD->format('m'), 1);

            $rangeFrom = 0;
            $rangeTo = 24;
            $timeBack = 730; // time range for conversion ratio

            $rangeGDFrom = 0;
            $rangeGDTo = 48;
            $rangeGD = 31;
        }

        elseif ($range == 'year') {
            $fromDate = $fromDate->sub(new DateInterval('P5Y'));
            $fromDate->setDate((int)$fromDate->format('Y'), 1, 1);
            $fromDateGD = $fromDateGD->sub(new DateInterval('P10Y'));
            $fromDateGD->setDate((int)$fromDateGD->format('Y'), 1, 1);

            $rangeFrom = 0;
            $rangeTo = 4;
            $timeBack = 5 * 365; // time range for conversion ratio

            $rangeGDFrom = 0;
            $rangeGDTo = 4;
            $rangeGD = 4;
        }

        elseif ($range == 'year2') {
            $fromDate = $fromDate->sub(new DateInterval('P10Y'));
            $fromDate->setDate((int)$fromDate->format('Y'), 1, 1);
            $fromDateGD = $fromDateGD->sub(new DateInterval('P10Y'));
            $fromDateGD->setDate((int)$fromDateGD->format('Y'), 1, 1);

            $rangeFrom = 0;
            $rangeTo = 9;
            $timeBack = 10 * 365; // time range for conversion ratio

            $rangeGDFrom = 0;
            $rangeGDTo = 9;
            $rangeGD = 9;
        }

        $fromDate->setTime(0, 0, 0);
        $toDate->setTime(23, 59, 59);

        if($this->timeZone != '')
        {
            $fromDate->setTimeZone(new \DateTimeZone('UTC'));
            $fromDateGD->setTimeZone(new \DateTimeZone('UTC'));
            $toDate->setTimeZone(new \DateTimeZone('UTC'));
        }
    }

    private function sma(array $data, $range)
    {
        if(count($data) == 0 || count($data) < $range){
            return [];
        }

        $sum = array_sum(array_slice($data, 0, $range));
        $result = [$range - 1 => $sum / $range];

        for ($i = $range, $n = count($data); $i != $n; ++$i) {
            $result[$i] = $result[$i - 1] + ($data[$i] - $data[$i - $range]) / $range;
        }

        return $result;
    }

    private function assignKeys($items)
    {
        $results = [];
        foreach($items as $item) {
            $results[(string)$item['date']] = $item;
        }

        return $results;
    }

    private function initItems($range, $rangeTo, &$items)
    {
        $items = [];

        $_startDate = new DateTime();
        if($this->timeZone != '')
        {
            $_startDate->setTimeZone(new \DateTimeZone($this->timeZone));
        }

        for($n = 0; $n <= $rangeTo; $n++)
        {
            $key = '';
            if($range == 'day' || $range == 'day2') {
                $key = $_startDate->format('Y-m-d 00:00:00');
                $_startDate->modify('-1 day');
            }
            elseif($range == 'week' || $range == 'week2') {
                $key = $_startDate->format('Y W');
                $_startDate->modify('-1 week');
            }
            elseif($range == 'month' || $range == 'month2') {
                $key = $_startDate->format('Y-m-01 00:00:00');
                $_startDate->modify('-1 month');
            }
            elseif($range == 'year' || $range == 'year2') {
                $key = $_startDate->format('Y-01-01 00:00:00');
                $_startDate->modify('-1 year');
            }

            $items['_' . $key] = [
                'item'          => $n,
                'turnover'      => 0.00,
                'visitors'      => 0,
                'newCustomers'  => 0,
                'orders'        => 0
            ];
        }
    }

    private function copyItems($rangeTo, $demoMode, $data, &$items) 
    {
        $_itemKeys = array_keys($items);
        for($n = 0; $n <= $rangeTo; $n++) {
            $_key = $_itemKeys[$n];

            $entry = [
                'turnover'      => 0,
                'visits'        => 0,
                'registrations' => 0,
                'orderCount'    => 0
            ];
            if(array_key_exists($_key, $data)) {
                $entry = $data[$_key];
            }

            if( ! $demoMode) { // LIVE data
                $items[$_key] = [
                    'item'          => $items[$_key]['item'],
                    'turnover'      => array_key_exists('turnover', $entry) ? 
                                        round($entry['turnover'], 2) : 
                                        0.00,
                    'visitors'      => array_key_exists('visits', $entry) ? 
                                        (int)$entry['visits'] 
                                        : 0,
                    'newCustomers'  => array_key_exists('registrations', $entry) ? 
                                        (int)$entry['registrations'] 
                                        : 0,
                    'orders'        => array_key_exists('orderCount', $entry) 
                                        ? (int)$entry['orderCount'] 
                                        : 0
                ];
            }
            else { // DEMO data
                $items[$_key] = [
                    'item'          => $items[$_key]['item'],
                    'turnover'      => round(random_int(1000, 39999), 2),
                    'visitors'      => 0,
                    'newCustomers'  => random_int(100, 999),
                    'orders'        => random_int(50, 399)
                ];
            }
        }

        $items = array_slice(array_values($items), 0, $rangeTo+1, true);
    }

    private function getAllData(
        $salesChannelId, $context,
        $range, $fromDate, $fromDateGD, $toDate,
        $priceMode, $rangeTo, $rangeGDTo, $rangeGD,
        $demoMode = false,
        &$items = [], &$sma = []
    ) {
        $turnoverAll = $this->getTurnover($salesChannelId, $context, $range, $fromDateGD, $toDate, $priceMode);
        $turnoverAll = $this->assignKeys($turnoverAll);

        $turnover = $turnoverAll;

        $visitors = $this->getVisitors($salesChannelId, $context, $range, $fromDate, $toDate);
        $visitors = $this->assignKeys($visitors);

        $registrations = $this->getRegistrations($salesChannelId, $context, $range, $fromDate, $toDate);
        $registrations = $this->assignKeys($registrations);

        $data = array_merge_recursive($turnover, $visitors, $registrations);
        $this->initItems($range, $rangeTo+1, $items);
        $this->copyItems($rangeTo, $demoMode, $data, $items);

        $smaData = array_values(array_map(fn($value) => $value['turnover'], $turnoverAll));

        $sma = $this->sma(array_values($smaData), $rangeGDTo - $rangeGD + 1);
    }

    private function getOrderStatus($salesChannelId, $context) 
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);

        if($salesChannelId != '') {
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        }

        $criteria->addAggregation(
            new FilterAggregation('ordersOpen',
                new CountAggregation('ordersOpen', 'id'),
                [
                    new ContainsFilter('stateMachineState.technicalName', 'open')
                ]
            ),
            new FilterAggregation('ordersPending',
                new CountAggregation('ordersPending', 'id'),
                [
                    new ContainsFilter('stateMachineState.technicalName', 'in_progress')
                ]
            )
        );

        $result = $this->orderRepository->search($criteria, $context);
        return [
            'ordersOpen'     => $result->getAggregations()->get('ordersOpen')->getCount(),
            'ordersPending'  => $result->getAggregations()->get('ordersPending')->getCount()
        ];
    }

    public function getTurnover($salesChannelId, $context, $interval, DateTime $from, DateTime $to, $priceMode)
    {
        $criteria = $this->createTurnoverCriteria($salesChannelId, $interval, $from, $to, $priceMode);

        $data = [];
        $result = $this->orderRepository->search($criteria, $context);
        $turnover = $result->getAggregations()->get('turnover');

        foreach ($turnover->getBuckets() as $bucket)
        {
            $data[] = [
                'date'       => '_' . $bucket->getKey(),
                'orderCount' => $bucket->getCount(),
                'turnover'   => $bucket->getResult()->getSum()
            ];
        }

        return $data;
    }

    public function getRegistrations($salesChannelId, $context,
                                     $interval, \DateTime $from, \DateTime $to)
    {
        $criteria = $this->createRegistrationsCriteria($salesChannelId, $interval, $from, $to);

        $data = [];
        $result = $this->customerRepository->search($criteria, $context);
        $registrations = $result->getAggregations()->get('registrations');

        foreach ($registrations->getBuckets() as $bucket)
        {
            $data[] = [
                'date'          => '_' . $bucket->getKey(),
                'registrations' => $bucket->getCount(),
            ];
        }

        return $data;
    }

    public function getVisitors($salesChannelId, $context, 
                                $interval, \DateTime $from, \DateTime $to)
    {
        $criteria = $this->createVisitorsCriteria($salesChannelId, $interval, $from, $to);

        $data = [];
        $result = $this->statisticsRepository->search($criteria, $context);
        $visitors = $result->getAggregations()->get('visitors');

        foreach ($visitors->getBuckets() as $bucket)
        {
            $data[] = [
                'date'      => '_' . $bucket->getKey(),
                'visits'    => $bucket->getCount(),
            ];
        }

        return $data;
    }

    public function getConversionRate(array $items): float
    {
        $sumOrders = 0;
        $sumVisitors = 0;

        $n = 0;
        foreach($items as $item)
        {
            if($n < count($items) - 1) { // das letzte item ist der Vorperioden-Vergleich, dieser geht nicht in die Berechnung mit ein
                $sumOrders += $item['orders'];
                $sumVisitors += $item['visitors'];
            }
            $n++;
        }

        if($sumVisitors <= 0) {
            return 0;
        }
        return $sumOrders / $sumVisitors * 100;
    }

    protected function createTurnoverCriteria($salesChannelId,
                                              $interval, \DateTime $from, \DateTime $to,
                                              $priceMode)
    {
        $amountColumn = $priceMode == self::PRICEMODE_NET ? 'amountNet' : 'amountTotal';

        $criteria = new Criteria();
        $criteria->setLimit(1);

        if($salesChannelId != '') {
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        }

        if($this->timeZone != '') {
            $criteria->addAggregation(
                new DateHistogramAggregation(
                    'turnover',
                    'orderDateTime',
                    $this->getShopwareInterval($interval),
                    null,
                    new SumAggregation('totalAmount', $amountColumn),
                    null,
                    $this->timeZone
                )
            );
        }
        else {
            $criteria->addAggregation(
                new DateHistogramAggregation(
                    'turnover',
                    'orderDateTime',
                    $this->getShopwareInterval($interval),
                    null,
                    new SumAggregation('totalAmount', $amountColumn)
                )
            );
        }

        $criteria->addFilter(
            new RangeFilter('orderDateTime', [
                RangeFilter::GTE => $from->format('Y-m-d H:i:s'),
                RangeFilter::LTE => $to->format('Y-m-d H:i:s')
            ])
        );

        if($this->helper->getConfig('onlypaid', false, $salesChannelId))
        {
            $criteria->addFilter(
                new EqualsFilter('order.transactions.stateMachineState.technicalName', 'paid')
            );
        }

        /*
        $criteria->addFilter(
            new NotFilter(NotFilter::CONNECTION_AND, [
                new EqualsAnyFilter('order.transactions.stateMachineState.technicalName',
                    ['cancelled', 'failed', 'chargeback', 'refunded'])
            ])
        );
        */

        $criteria->addFilter(
            new NotFilter(NotFilter::CONNECTION_AND, [
                new EqualsFilter('order.stateMachineState.technicalName', 'cancelled')
            ])
        );

        return $criteria;
    }

    protected function createRegistrationsCriteria($salesChannelId,
                                                   $interval, \DateTime $from, \DateTime $to)
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        if($salesChannelId != '') {
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        }

        if($this->timeZone != '') {
            $criteria->addAggregation(
                new DateHistogramAggregation(
                    'registrations',
                    'createdAt',
                    $this->getShopwareInterval($interval),
                    null,
                    null,
                    null,
                    $this->timeZone
                )
            );
        }
        else {
            $criteria->addAggregation(
                new DateHistogramAggregation(
                    'registrations',
                    'createdAt',
                    $this->getShopwareInterval($interval)
                )
            );
        }

        $criteria->addFilter(
            new RangeFilter('createdAt', [
                RangeFilter::GTE => $from->format('Y-m-d H:i:s'),
                RangeFilter::LTE => $to->format('Y-m-d H:i:s')
            ])
        );

        return $criteria;
    }

    protected function createVisitorsCriteria($salesChannelId, 
                                              $interval, \DateTime $from, \DateTime $to)
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        if($salesChannelId != '') {
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        }

        if($this->timeZone != '') {
            $criteria->addAggregation(
                new DateHistogramAggregation(
                    'visitors',
                    'createdAt',
                    $this->getShopwareInterval($interval),
                    null,
                    null,
                    null,
                    $this->timeZone
                )
            );
        }
        else {
            $criteria->addAggregation(
                new DateHistogramAggregation(
                    'visitors',
                    'createdAt',
                    $this->getShopwareInterval($interval)
                )
            );
        }

        $criteria->addFilter(
            new RangeFilter('createdAt', [
                RangeFilter::GTE => $from->format('Y-m-d H:i:s'),
                RangeFilter::LTE => $to->format('Y-m-d H:i:s')
            ])
        );

        return $criteria;
    }

    private function addDateRangeCondition($builder, 
                                           \DateTime $from = null, \DateTime $to = null,
                                           $column = null)
    {
        if ($from instanceof \DateTime) {
            $builder->andWhere($column . ' >= :fromDate')
                ->setParameter('fromDate', $from->format("Y-m-d H:i:s"));
        }
        if ($to instanceof \DateTime) {
            $builder->andWhere($column . ' <= :toDate')
                ->setParameter('toDate', $to->format("Y-m-d H:i:s"));
        }

        return $this;
    }

    private function getShopwareInterval($interval) {

        if($interval == 'week' || $interval == 'week2') {
            return DateHistogramAggregation::PER_WEEK;
        }
        elseif($interval == 'month' || $interval == 'month2') {
            return DateHistogramAggregation::PER_MONTH;
        }
        elseif($interval == 'year' || $interval == 'year2') {
            return DateHistogramAggregation::PER_YEAR;
        }

        return DateHistogramAggregation::PER_DAY;
    }

    private function arrayGet($arr, $key, $default = 0) {

        if(array_key_exists($key, $arr)) {
            return $arr[$key];
        }
        else {
            return $default;
        }
    }
}
