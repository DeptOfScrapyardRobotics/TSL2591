<?php

namespace DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums;

enum TSL2591EnableFlag: int
{
    case POWER_OFF = 0x00;
    case POWER_ON = 0x01;
    case ENABLE_ALS = 0x02;
    case ENABLE_ALS_INTERRUPT = 0x10;
}
