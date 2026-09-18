<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CountryCurrencySeeder extends Seeder
{
    public function run(): void
    {
        // China and common countries
        $countries = [
            ['name' => 'China', 'code' => 'CN', 'phonecode' => '+86', 'iso3' => 'CHN'],
            ['name' => 'United States', 'code' => 'US', 'phonecode' => '+1', 'iso3' => 'USA'],
            ['name' => 'Japan', 'code' => 'JP', 'phonecode' => '+81', 'iso3' => 'JPN'],
            ['name' => 'South Korea', 'code' => 'KR', 'phonecode' => '+82', 'iso3' => 'KOR'],
            ['name' => 'Singapore', 'code' => 'SG', 'phonecode' => '+65', 'iso3' => 'SGP'],
            ['name' => 'United Kingdom', 'code' => 'GB', 'phonecode' => '+44', 'iso3' => 'GBR'],
            ['name' => 'Germany', 'code' => 'DE', 'phonecode' => '+49', 'iso3' => 'DEU'],
            ['name' => 'France', 'code' => 'FR', 'phonecode' => '+33', 'iso3' => 'FRA'],
            ['name' => 'Australia', 'code' => 'AU', 'phonecode' => '+61', 'iso3' => 'AUS'],
            ['name' => 'Canada', 'code' => 'CA', 'phonecode' => '+1', 'iso3' => 'CAN'],
            ['name' => 'India', 'code' => 'IN', 'phonecode' => '+91', 'iso3' => 'IND'],
            ['name' => 'Malaysia', 'code' => 'MY', 'phonecode' => '+60', 'iso3' => 'MYS'],
            ['name' => 'Thailand', 'code' => 'TH', 'phonecode' => '+66', 'iso3' => 'THA'],
            ['name' => 'Vietnam', 'code' => 'VN', 'phonecode' => '+84', 'iso3' => 'VNM'],
            ['name' => 'UAE', 'code' => 'AE', 'phonecode' => '+971', 'iso3' => 'ARE'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }

        // Common currencies
        $currencies = [
            ['currency_name' => 'Chinese Yuan', 'currency_code' => 'CNY', 'currency_symbol' => '¥', 'exchange_rate' => 1, 'is_active' => true],
            ['currency_name' => 'US Dollar', 'currency_code' => 'USD', 'currency_symbol' => '$', 'exchange_rate' => 0.14, 'is_active' => true],
            ['currency_name' => 'Euro', 'currency_code' => 'EUR', 'currency_symbol' => '€', 'exchange_rate' => 0.13, 'is_active' => true],
            ['currency_name' => 'British Pound', 'currency_code' => 'GBP', 'currency_symbol' => '£', 'exchange_rate' => 0.11, 'is_active' => true],
            ['currency_name' => 'Japanese Yen', 'currency_code' => 'JPY', 'currency_symbol' => '¥', 'exchange_rate' => 20.5, 'is_active' => true],
            ['currency_name' => 'Hong Kong Dollar', 'currency_code' => 'HKD', 'currency_symbol' => 'HK$', 'exchange_rate' => 1.09, 'is_active' => true],
            ['currency_name' => 'Singapore Dollar', 'currency_code' => 'SGD', 'currency_symbol' => 'S$', 'exchange_rate' => 0.19, 'is_active' => true],
            ['currency_name' => 'Korean Won', 'currency_code' => 'KRW', 'currency_symbol' => '₩', 'exchange_rate' => 186.5, 'is_active' => true],
        ];

        foreach ($currencies as $currency) {
            Currency::create($currency);
        }

        $this->command->info('Created ' . count($countries) . ' countries and ' . count($currencies) . ' currencies');
    }
}
