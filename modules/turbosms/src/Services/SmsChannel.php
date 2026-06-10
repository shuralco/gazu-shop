<?php

namespace App\Services\TurboSms;

/** Константи каналів відправки (узгоджені з SmsTemplate::CHANNEL_*). */
final class SmsChannel
{
    public const SMS = 'sms';
    public const VIBER = 'viber';
    public const HYBRID = 'hybrid';
}
