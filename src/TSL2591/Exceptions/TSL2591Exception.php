<?php

namespace DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Exceptions;

use Exception;

class TSL2591Exception extends Exception
{
    public static function invalidProperty(string $name): static
    {
        return new static("Invalid property $name");
    }

    public static function failedToFind(): static
    {
        return new static('Failed to find TSL2591, check wiring!');
    }

    public static function overflow(): static
    {
        return new static('TSL2591 channel overflow — reduce gain or integration time.');
    }
}
