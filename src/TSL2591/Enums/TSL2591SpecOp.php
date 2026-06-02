<?php

namespace DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums;

enum TSL2591SpecOp: int
{
    case CLEAR_INTERRUPT = 0x06;
    case CLEAR_ALL_INTERRUPTs = 0x07;
    case CLEAR_PERSIST_INTERRUPT = 0x0A;

}
