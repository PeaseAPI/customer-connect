<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CountryCurrencySeeder extends Seeder
{
    public function run(): void
    {
        // 中国及常用国家
        $countries = [
            ['name' => '中国', 'code' => 'CN', 'phonecode' => '+86', 'iso3' => 'CHN'],
            ['name' => '美国', 'code' => 'US', 'phonecode' => '+1', 'iso3' => 'USA'],
            ['name' => '日本', 'code' => 'JP', 'phonecode' => '+81', 'iso3' => 'JPN'],
            ['name' => '韩国', 'code' => 'KR', 'phonecode' => '+82', 'iso3' => 'KOR'],
            ['name' => '新加坡', 'code' => 'SG', 'phonecode' => '+65', 'iso3' => 'SGP'],
            ['name' => '英国', 'code' => 'GB', 'phonecode' => '+44', 'iso3' => 'GBR'],
            ['name' => '德国', 'code' => 'DE', 'phonecode' => '+49', 'iso3' => 'DEU'],
            ['name' => '法国', 'code' => 'FR', 'phonecode' => '+33', 'iso3' => 'FRA'],
            ['name' => '澳大利亚', 'code' => 'AU', 'phonecode' => '+61', 'iso3' => 'AUS'],
            ['name' => '加拿大', 'code' => 'CA', 'phonecode' => '+1', 'iso3' => 'CAN'],
            ['name' => '印度', 'code' => 'IN', 'phonecode' => '+91', 'iso3' => 'IND'],
            ['name' => '马来西亚', 'code' => 'MY', 'phonecode' => '+60', 'iso3' => 'MYS'],
            ['name' => '泰国', 'code' => 'TH', 'phonecode' => '+66', 'iso3' => 'THA'],
            ['name' => '越南', 'code' => 'VN', 'phonecode' => '+84', 'iso3' => 'VNM'],
            ['name' => '阿联酋', 'code' => 'AE', 'phonecode' => '+971', 'iso3' => 'ARE'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }

        // 常用货币
        $currencies = [
            ['currency_name' => '人民币', 'currency_code' => 'CNY', 'currency_symbol' => '¥', 'exchange_rate' => 1, 'is_active' => true],
            ['currency_name' => '美元', 'currency_code' => 'USD', 'currency_symbol' => '$', 'exchange_rate' => 0.14, 'is_active' => true],
            ['currency_name' => '欧元', 'currency_code' => 'EUR', 'currency_symbol' => '€', 'exchange_rate' => 0.13, 'is_active' => true],
            ['currency_name' => '英镑', 'currency_code' => 'GBP', 'currency_symbol' => '£', 'exchange_rate' => 0.11, 'is_active' => true],
            ['currency_name' => '日元', 'currency_code' => 'JPY', 'currency_symbol' => '¥', 'exchange_rate' => 20.5, 'is_active' => true],
            ['currency_name' => '港币', 'currency_code' => 'HKD', 'currency_symbol' => 'HK$', 'exchange_rate' => 1.09, 'is_active' => true],
            ['currency_name' => '新加坡元', 'currency_code' => 'SGD', 'currency_symbol' => 'S$', 'exchange_rate' => 0.19, 'is_active' => true],
            ['currency_name' => '韩元', 'currency_code' => 'KRW', 'currency_symbol' => '₩', 'exchange_rate' => 186.5, 'is_active' => true],
        ];

        foreach ($currencies as $currency) {
            Currency::create($currency);
        }

        $this->command->info('已创建 ' . count($countries) . ' 个国家和 ' . count($currencies) . ' 种货币');
    }
}
