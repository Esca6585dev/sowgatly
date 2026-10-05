<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Date labels for the admin panel. Carbon's "tk" locale numbers weekdays
 * from Monday, which puts every Turkmen weekday name one day off, so Turkmen
 * is handled here and other locales go through Carbon.
 */
class AdminDate
{
    private const TK_WEEKDAYS = ['Ýekşenbe', 'Duşenbe', 'Sişenbe', 'Çarşenbe', 'Penşenbe', 'Anna', 'Şenbe'];

    private const TK_WEEKDAYS_SHORT = ['Ýek', 'Duş', 'Siş', 'Çar', 'Pen', 'Ann', 'Şen'];

    private const TK_MONTHS = ['Ýanwar', 'Fewral', 'Mart', 'Aprel', 'Maý', 'Iýun', 'Iýul', 'Awgust', 'Sentýabr', 'Oktýabr', 'Noýabr', 'Dekabr'];

    public static function carbonLocale(?string $appLocale = null): string
    {
        return ['tm' => 'tk', 'ru' => 'ru', 'en' => 'en', 'tr' => 'tr'][$appLocale ?? app()->getLocale()] ?? 'en';
    }

    /** "D MMM, HH:mm" style: "5 Okt, 14:30". */
    public static function dayMonthTime(Carbon $date, ?string $appLocale = null): string
    {
        $appLocale = $appLocale ?? app()->getLocale();
        if ($appLocale === 'tm') {
            return $date->day.' '.mb_substr(self::TK_MONTHS[$date->month - 1], 0, 3).', '.$date->format('H:i');
        }

        return $date->locale(self::carbonLocale($appLocale))->isoFormat('D MMM, HH:mm');
    }

    /** "Ýekşenbe, 4 Oktýabr" / "Sunday, 4 October" */
    public static function long(Carbon $date, ?string $appLocale = null): string
    {
        $appLocale = $appLocale ?? app()->getLocale();
        if ($appLocale === 'tm') {
            return self::TK_WEEKDAYS[$date->dayOfWeek].', '.$date->day.' '.self::TK_MONTHS[$date->month - 1];
        }

        return $date->locale(self::carbonLocale($appLocale))->isoFormat('dddd, D MMMM');
    }

    /** "4 Okt 15:25" / "4 Oct 15:25" */
    public static function short(Carbon $date, ?string $appLocale = null): string
    {
        $appLocale = $appLocale ?? app()->getLocale();
        if ($appLocale === 'tm') {
            return $date->day.' '.mb_substr(self::TK_MONTHS[$date->month - 1], 0, 3).' '.$date->format('H:i');
        }

        return $date->locale(self::carbonLocale($appLocale))->isoFormat('D MMM HH:mm');
    }

    /** "Ýek" / "Sun" */
    public static function weekdayShort(Carbon $date, ?string $appLocale = null): string
    {
        $appLocale = $appLocale ?? app()->getLocale();
        if ($appLocale === 'tm') {
            return self::TK_WEEKDAYS_SHORT[$date->dayOfWeek];
        }

        return $date->locale(self::carbonLocale($appLocale))->isoFormat('dd');
    }

    /** "2 sag" style relative time; Turkmen handled by Carbon's tk units which are correct. */
    public static function ago(?Carbon $date, ?string $appLocale = null): string
    {
        if (! $date) {
            return '';
        }

        return $date->locale(self::carbonLocale($appLocale))->diffForHumans(null, true, true);
    }
}
