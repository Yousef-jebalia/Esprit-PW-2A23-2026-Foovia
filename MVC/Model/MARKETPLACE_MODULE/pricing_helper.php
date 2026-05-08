<?php

declare(strict_types=1);

if (!function_exists('foovia_product_normalized_name')) {
    function foovia_product_normalized_name(array $product): string
    {
        $name = strtolower(trim((string) ($product['name_march'] ?? '')));
        $name = str_replace(['_', '-', '(', ')'], ' ', $name);

        return preg_replace('/\s+/', ' ', $name) ?? $name;
    }
}

if (!function_exists('foovia_product_unit')) {
    function foovia_product_unit(array $product): string
    {
        $name = foovia_product_normalized_name($product);
        $categories = strtolower((string) ($product['category_names'] ?? ''));

        $liquidNames = [
            'milk',
            'olive oil',
            'soy sauce',
        ];
        $pieceNames = [
            'eggs',
            'egg',
            'lettuce',
        ];

        if (in_array($name, $pieceNames, true)) {
            return 'piece';
        }

        if (in_array($name, $liquidNames, true)) {
            return 'L';
        }

        if (str_contains($categories, 'beverages') || str_contains($categories, 'sauces and marinades')) {
            return 'L';
        }

        return 'kg';
    }
}

if (!function_exists('foovia_product_unit_price')) {
    function foovia_product_unit_price(array $product): float
    {
        $basePrice = (float) ($product['price_march'] ?? 0);
        $name = foovia_product_normalized_name($product);

        $realisticPrices = [
            'apples' => 4.900,
            'apple' => 4.900,
            'orange' => 3.200,
            'oranges' => 3.200,
            'banana' => 5.900,
            'bananas' => 5.900,
            'blueberries' => 28.000,
            'lemon' => 4.200,
            'avocado' => 18.500,
            'tomato' => 2.400,
            'tomatoes' => 2.400,
            'cucumber' => 2.200,
            'lettuce' => 1.800,
            'spinach' => 6.500,
            'potato' => 1.800,
            'potatoes' => 1.800,
            'carrot' => 2.100,
            'bell pepper' => 5.200,
            'garlic' => 12.000,
            'onion' => 1.700,
            'milk' => 1.650,
            'yogurt' => 4.800,
            'butter' => 22.000,
            'eggs' => 0.850,
            'parmesan' => 46.000,
            'cheddar' => 32.000,
            'chicken breast' => 16.900,
            'ground beef' => 32.000,
            'bacon' => 39.000,
            'salmon' => 58.000,
            'shrimp' => 42.000,
            'tofu' => 13.500,
            'chickpeas' => 7.500,
            'black beans' => 8.200,
            'lentils' => 6.900,
            'rice white' => 3.200,
            'rice' => 3.200,
            'pasta' => 4.500,
            'flour' => 2.300,
            'sugar' => 3.100,
            'salt' => 0.900,
            'olive oil' => 28.000,
            'soy sauce' => 8.500,
            'paprika' => 24.000,
            'honey' => 26.000,
            'almonds' => 38.000,
            'walnuts' => 34.000,
            'oats' => 8.900,
        ];

        if (isset($realisticPrices[$name])) {
            return $realisticPrices[$name];
        }

        return round(max($basePrice, 1.000), 3);
    }
}

if (!function_exists('foovia_format_price')) {
    function foovia_format_price(mixed $price): string
    {
        return rtrim(rtrim(number_format((float) $price, 3, '.', ''), '0'), '.');
    }
}

if (!function_exists('foovia_format_unit_price')) {
    function foovia_format_unit_price(array $product): string
    {
        return foovia_format_price(foovia_product_unit_price($product)) . ' TND / ' . foovia_product_unit($product);
    }
}
