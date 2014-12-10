<?php
use FintechFab\Smstraffic\SmsTraffic;

/**
 * @var SmsTraffic $sms
 */

$sms = App::make(SmsTraffic::class);

$id = $sms->send('%PHONE%', 'test message');

$status = $sms->status($id);

$balance = $sms->balance();

var_dump($id, $status, $balance);
