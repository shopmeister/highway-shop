<?php

namespace ShopmasterZalandoConnectorSix\Services\Config;

interface ConfigServiceInterface
{
    /**
     * All possible Zalando sales channels.
     * this const is static and it`s not change
     * @link https://developers.merchants.zalando.com/docs/sales-channels.html#sales-channel-ids
     */
    const SALES_CHANNELS = [
        'de' => '01924c48-49bb-40c2-9c32-ab582e6db6f4',
        'nl' => '00f2a393-6889-4fc0-8cd9-86e454e6dfa3',
        'fr' => '733af55a-4133-4d7c-b5f3-d64d42c135fe',
        'it' => 'ebf57ebf-e26d-4ebd-8009-6ad519073d2a',
        'uk' => '83c4e87f-6819-41bb-af61-46cddb8453f5',
        'at' => '53075bd4-0205-4b5d-8145-e7a7745ab137',
        'ch' => 'c2bd90da-0090-4751-8f16-35dea7002071',
        'pl' => 'ca9d5f22-2a1b-4799-b3b7-83f47c191489',
        'be' => '043ec789-a3c7-4556-92df-bf1845c741ab',
        'se' => '091dcbdd-7839-4f39-aa05-324eb4599df0',
        'fi' => 'aadd3adf-500f-4372-8137-dc0e4b2f0582',
        'dk' => '7ce94f55-7a4d-4416-95c1-bf34193a47e8',
        'es' => '1e161d6e-0427-4cfc-a357-e2b501188a15',
        'no' => 'ef064ea7-1d91-442c-bcbb-9d20749af19b',
        'cz' => 'b773b421-c719-4dfd-afc8-e97da508a88d',
        'ie' => 'a13a1960-5d57-4c51-a3ea-7e8d28e2c0b7',
        'pt' => '5d99621e-7a9e-4393-8df3-1084ac3bb8a7',
    ];
}