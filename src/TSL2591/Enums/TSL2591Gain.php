<?php

namespace DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums;

enum TSL2591Gain: int
{
    case LOW = 0x00;      // 1x
    case MEDIUM = 0x10;   // 25x
    case HIGH = 0x20;     // 428x
    case MAX = 0x30;      // 9876x

    public function multiplier(): float
    {
        return match ($this) {
            self::LOW => 1.0,
            self::MEDIUM => 25.0,
            self::HIGH => 428.0,
            self::MAX => 9876.0,
        };
    }
}
