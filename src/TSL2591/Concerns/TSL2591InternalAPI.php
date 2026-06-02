<?php

namespace DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Concerns;

use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591ReadRegister;

trait TSL2591InternalAPI
{
    protected int $expected_device_id = 0x50;

    protected function specialWrite(int $cmd): void
    {
        $payload = [$cmd & 0xFF];
        $this->carrier->write($payload);
    }

    protected function write(int $register_hex, array $command_data = []): ?int
    {
        $cmd = (0xA0 | $register_hex) & 0xFF;
        $payload = [$cmd, ...$command_data];

        return $this->carrier->write($payload);
    }

    protected function read(TSL2591ReadRegister $register_hex, int $length): array
    {
        $cmd = (0xA0 | $register_hex->value) & 0xFF;

        return $this->carrier->readWrite([$cmd], $length);
    }

    protected function readU16LE(TSL2591ReadRegister $register): int
    {
        $bytes = $this->read($register, 2);

        return isset($bytes[0], $bytes[1]) ? (($bytes[1] << 8) | $bytes[0]) : -1;
    }

    protected function hasValidDeviceId(): bool
    {
        return $this->device_id === $this->expected_device_id;
    }
}
