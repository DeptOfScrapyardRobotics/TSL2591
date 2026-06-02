<?php

namespace DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Concerns;

use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591EnableFlag;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591Gain;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591IntegrationTime;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591ReadRegister;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591SpecOp;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591WriteRegister;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Exceptions\TSL2591Exception;

trait TSL2591API
{
    use TSL2591InternalAPI;

    protected int $spec_op_bit = 0xE0;

    public function deviceId(): int
    {
        return $this->read(TSL2591ReadRegister::DEVICE_ID_REGISTER, 1)[0] ?? -1;
    }

    public function enable(): void
    {
        $byte = TSL2591EnableFlag::POWER_ON->value | TSL2591EnableFlag::ENABLE_ALS->value;
        $this->write(TSL2591WriteRegister::ENABLE_REGISTER->value, [$byte]);
    }

    public function disable(): void
    {
        $byte = TSL2591EnableFlag::POWER_OFF->value;
        $this->write(TSL2591WriteRegister::ENABLE_REGISTER->value, [$byte]);
    }

    public function clearInterrupt(TSL2591SpecOp $operation): void
    {
        $control = ($this->spec_op_bit | $operation->value);
        $this->specialWrite($control);
    }

    public function enableInterrupt(int $interrupts): void
    {
        $enabled_functions = $this->read(TSL2591ReadRegister::ENABLE_REGISTER, 1)[0] ?? -1;
        $functions = ($enabled_functions | $interrupts);
        $this->specialWrite($functions);
    }

    public function disableInterrupt(int $interrupts): void
    {
        $enabled_functions = $this->read(TSL2591ReadRegister::ENABLE_REGISTER, 1)[0] ?? -1;
        $functions = ($enabled_functions & ~$interrupts);
        $this->specialWrite($functions);
    }

    public function fullSpectrum(): int
    {
        return $this->rawLuminosity()[0];
    }

    public function getGain(): TSL2591Gain
    {
        $control = $this->read(TSL2591ReadRegister::CONTROL_REGISTER, 1)[0] ?? 0;

        return TSL2591Gain::from($control & 0b00110000);
    }

    public function setGain(TSL2591Gain $gain): void
    {
        $control = $this->read(TSL2591ReadRegister::CONTROL_REGISTER, 1)[0] ?? 0;
        $control = ($control & 0b11001111) | $gain->value;
        $this->write(TSL2591WriteRegister::CONTROL_REGISTER->value, [$control]);
        $this->gain_enum = $gain;
    }

    public function infrared(): int
    {
        return $this->rawLuminosity()[1];
    }

    public function getIntegrationTime(): TSL2591IntegrationTime
    {
        $control = $this->read(TSL2591ReadRegister::CONTROL_REGISTER, 1)[0] ?? 0;

        return TSL2591IntegrationTime::from($control & 0b00000111);
    }

    public function setIntegrationTime(TSL2591IntegrationTime $integration_time): void
    {
        $control = $this->read(TSL2591ReadRegister::CONTROL_REGISTER, 1)[0] ?? 0;
        $control = ($control & 0b11111000) | $integration_time->value;
        $this->write(TSL2591WriteRegister::CONTROL_REGISTER->value, [$control]);
        $this->integration_time_enum = $integration_time;
    }

    /**
     * @throws TSL2591Exception
     */
    public function lux(): float
    {
        [$channel0, $channel1] = $this->rawLuminosity();

        $atime = (float) $this->integration_time_enum->milliseconds();

        $maxCounts = $this->integration_time_enum === TSL2591IntegrationTime::MS100
            ? 36863   // 0x8FFF
            : 65535;  // 0xFFFF

        if ($channel0 >= $maxCounts || $channel1 >= $maxCounts) {
            throw TSL2591Exception::overflow();
        }

        $cpl = ($atime * $this->gain_enum->multiplier()) / 408.0;

        $lux1 = ($channel0 - (1.64 * $channel1)) / $cpl;
        $lux2 = ((0.59 * $channel0) - (0.86 * $channel1)) / $cpl;

        return max($lux1, $lux2);
    }

    public function getNoPersistThresholdHigh(): int
    {
        return $this->readU16LE(TSL2591ReadRegister::NO_PERSIST_THRESHOLD_HIGH_REGISTER);
    }

    public function setNoPersistThresholdHigh(int $value): void
    {
        $this->write(TSL2591WriteRegister::NO_PERSIST_THRESHOLD_HIGH_LOW_REGISTER->value, [$value & 0xFF]);
        $this->write(TSL2591WriteRegister::NO_PERSIST_THRESHOLD_HIGH_HIGH_REGISTER->value, [($value >> 8) & 0xFF]);
    }

    public function getNoPersistThresholdLow(): int
    {
        return $this->readU16LE(TSL2591ReadRegister::NO_PERSIST_THRESHOLD_LOW_REGISTER);
    }

    public function setNoPersistThresholdLow(int $value): void
    {
        $this->write(TSL2591WriteRegister::NO_PERSIST_THRESHOLD_LOW_LOW_REGISTER->value, [$value & 0xFF]);
        $this->write(TSL2591WriteRegister::NO_PERSIST_THRESHOLD_LOW_HIGH_REGISTER->value, [($value >> 8) & 0xFF]);
    }

    public function getPersist(): int
    {
        $value = $this->read(TSL2591ReadRegister::PERSIST_FILTER_REGISTER, 1)[0] ?? 0;

        return $value & 0x0F;
    }

    public function setPersist(int $val): void
    {
        $this->write(TSL2591WriteRegister::PERSIST_FILTER_REGISTER->value, [$val & 0x0F]);
    }

    public function rawLuminosity(): array
    {
        $ch0 = $this->read(TSL2591ReadRegister::CHANNEL0_LOW_REGISTER, 2);
        $ch1 = $this->read(TSL2591ReadRegister::CHANNEL1_LOW_REGISTER, 2);

        $channel0 = isset($ch0[0], $ch0[1]) ? (($ch0[1] << 8) | $ch0[0]) : -1;
        $channel1 = isset($ch1[0], $ch1[1]) ? (($ch1[1] << 8) | $ch1[0]) : -1;

        return [$channel0, $channel1];
    }

    public function getThresholdHigh(): int
    {
        return $this->readU16LE(TSL2591ReadRegister::THRESHOLD_HIGH_REGISTER);
    }

    public function setThresholdHigh(int $value): void
    {
        $this->write(TSL2591WriteRegister::THRESHOLD_HIGH_LOW_REGISTER->value, [$value & 0xFF]);
        $this->write(TSL2591WriteRegister::THRESHOLD_HIGH_HIGH_REGISTER->value, [($value >> 8) & 0xFF]);
    }

    public function getThresholdLow(): int
    {
        return $this->readU16LE(TSL2591ReadRegister::THRESHOLD_LOW_REGISTER);
    }

    public function setThresholdLow(int $value): void
    {
        $this->write(TSL2591WriteRegister::THRESHOLD_LOW_LOW_REGISTER->value, [$value & 0xFF]);
        $this->write(TSL2591WriteRegister::THRESHOLD_LOW_HIGH_REGISTER->value, [($value >> 8) & 0xFF]);
    }

    public function visible(): int
    {
        [$channel0, $channel1] = $this->rawLuminosity();

        return $channel0 - $channel1;
    }
}
