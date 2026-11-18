<?php

return [
    'chrome_path' => env('CHROME_PATH', ''),
    'chrome_no_sandbox' => env('CHROME_NO_SANDBOX', false),
    'allow_private_urls' => env('FEED_ALLOW_PRIVATE', false),

    'card_width' => 1080,
    'card_height' => 1350,
    'max_new_per_check' => 10,
    'connector_same_host' => env('CONNECTOR_SAME_HOST', true),

    // ব্যর্থ পোস্ট আবার চেষ্টার বিরতি (সেকেন্ডে): ১ মিনিট, ৫ মিনিট, ১৫ মিনিট
    'retry_delays' => [60, 300, 900],

    'facebook' => [
        'app_id' => env('FB_APP_ID'),
        'config_id' => env('FB_CONFIG_ID'),
        'app_secret' => env('FB_APP_SECRET'),
        'graph_version' => env('FB_GRAPH_VERSION', 'v25.0'),
        // `pages_manage_posts`-এর সঙ্গে `pages_read_engagement` ও `pages_show_list` একসাথে লাগে
        // আর কমেন্ট করতে লাগে `pages_manage_engagement`
        'scopes' => [
            'pages_show_list',
            'pages_read_engagement',
            'pages_manage_posts',
            'pages_manage_engagement',
        ],
    ],
     'fonts' => [
        'noto_sans' => ['label' => 'Noto Sans Bengali', 'file' => 'noto-sans-bengali.ttf'],
        'noto_serif' => ['label' => 'Noto Serif Bengali (সেরিফ)', 'file' => 'noto-serif-bengali.ttf'],
        'anek' => ['label' => 'Anek Bangla', 'file' => 'anek-bangla.ttf'],
        'baloo' => ['label' => 'Baloo Da 2 (গোল ধাঁচ)', 'file' => 'baloo-da-2.ttf'],
    ],
];
