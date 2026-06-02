<?php

namespace DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums;

enum TSL2591IntegrationTime: int
{
    case MS100 = 0x00;
    case MS200 = 0x01;
    case MS300 = 0x02;
    case MS400 = 0x03;
    case MS500 = 0x04;
    case MS600 = 0x05;

    public function milliseconds(): int
    {
        return match ($this) {
            self::MS100 => 100,
            self::MS200 => 200,
            self::MS300 => 300,
            self::MS400 => 400,
            self::MS500 => 500,
            self::MS600 => 600,
        };
    }
}
