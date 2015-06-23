<?php namespace FintechFab\Smstraffic;

class SmsStatus
{
    const DELIVERED = 1;
    const NON_DELIVERED = 2;
    const LOST_NOTIFICATION = 3;
    const BUFFERED_IN_SMS_CENTER = 4;
    const ACCEPTED = 5;
    const EXPIRED = 6;
    const DELETED = 7;
    const UNKNOWN = 8;
    const REJECTED = 9;
    const BLANK = 10;

    public static $finalStatuses = [
        self::DELIVERED,
        self::NON_DELIVERED,
        self::LOST_NOTIFICATION,
        self::EXPIRED,
        self::DELETED,
        self::UNKNOWN,
        self::REJECTED,
        self::BLANK,
    ];

    private static $mapping = [
        self::DELIVERED => 'Delivered',
        self::NON_DELIVERED => 'Non Delivered',
        self::LOST_NOTIFICATION => 'Lost Notification',
        self::BUFFERED_IN_SMS_CENTER => 'Buffered SMSC',
        self::ACCEPTED => 'Acceptd',
        self::EXPIRED => 'Expired',
        self::DELETED => 'Deleted',
        self::UNKNOWN => 'Unknown status',
        self::REJECTED => 'Rejected',
        self::BLANK => '',
    ];

    /**
     * Is status final?
     *
     * @param int $status
     *
     * @return bool
     */
    public static function isFinal($status)
    {
        return in_array($status, self::$finalStatuses);
    }

    /**
     * Convert status from API to internal
     *
     * @param $rawStatus
     *
     * @return int
     */
    public static function convert($rawStatus)
    {
        return array_search($rawStatus, self::$mapping);
    }
}
