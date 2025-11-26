<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Helpers;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

final class Date
{
    /**
     * @return DateTimeZone
     */
    private static function timezone(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }

    /**
     * @param string $date
     * @return DateTimeImmutable
     * @throws Exception
     */
    public static function startOfDay(string $date): DateTimeImmutable
    {
        return new DateTimeImmutable($date . ' 00:00:00', self::timezone());
    }

    /**
     * @param string $date
     * @return DateTimeImmutable
     * @throws Exception
     */
    public static function endOfDay(string $date): DateTimeImmutable
    {
        return new DateTimeImmutable($date . ' 23:59:59', self::timezone());
    }

    /**
     * @param string $dateTime
     * @return DateTimeImmutable
     * @throws Exception
     */
    public static function at(string $dateTime): DateTimeImmutable
    {
        return new DateTimeImmutable($dateTime, self::timezone());
    }
}
