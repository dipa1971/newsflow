<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Bangla
{
    private const DIGITS = [
        '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
        '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯',
    ];

    private const MONTHS = [
        1 => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ', 4 => 'এপ্রিল',
        5 => 'মে', 6 => 'জুন', 7 => 'জুলাই', 8 => 'আগস্ট',
        9 => 'সেপ্টেম্বর', 10 => 'অক্টোবর', 11 => 'নভেম্বর', 12 => 'ডিসেম্বর',
    ];

    // Carbon-এ রবিবার = 0
    private const DAYS = [
        'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার',
        'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার',
    ];

    public static function digits($value): string
    {
        return strtr((string) $value, self::DIGITS);
    }

    // কার্ডে ছাপার তারিখ: "মঙ্গলবার, ১১ নভেম্বর ২০২৬"
    public static function date(CarbonInterface $date, bool $withDay = true): string
    {
        $d = $date->copy()->timezone('Asia/Dhaka');
        $text = self::digits($d->day) . ' ' . self::MONTHS[$d->month] . ' ' . self::digits($d->year);

        return $withDay ? self::DAYS[$d->dayOfWeek] . ', ' . $text : $text;
    }

    // প্যানেলে সময় দেখানো: "১০/১১/২০২৬, ০৩:২০ PM"
    public static function dateTime(?CarbonInterface $date): string
    {
        if (!$date) {
            return '—';
        }
        return self::digits($date->copy()->timezone('Asia/Dhaka')->format('d/m/Y, h:i A'));
    }
}
