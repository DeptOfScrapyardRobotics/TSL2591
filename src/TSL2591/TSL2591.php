<?php

namespace DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591;

use BareMetal\IntegratedCircuit;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Concerns\TSL2591API;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591Gain;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591IntegrationTime;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Exceptions\TSL2591Exception;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Factory\TSL2591Factory;
use Exception;
use RealityInterface\Sensors\Attributes\MeasuresLuminance;
use RealityInterface\Sensors\Contracts\Applied\AmbientLighting\DualChannelLuxSensor;
use RealityInterface\Sensors\Enums\SensorType;
use Waveforms\Carriers\GPIO\GPIOBus;
use Waveforms\Carriers\I2C\I2C;
use Waveforms\Carriers\I2C\I2CDevice;

/**
 * @property int $device_id
 * @property int $full_spectrum
 * @property TSL2591Gain $gain
 * @property int $infrared
 * @property TSL2591IntegrationTime $integration_time
 * @property float $lux
 * @property int $nopersist_threshold_high
 * @property int $nopersist_threshold_low
 * @property int $persist
 * @property array $raw_luminosity
 * @property int $threshold_high
 * @property int $threshold_low
 * @property int $visible
 */
#[MeasuresLuminance(SensorType::LUX)]
class TSL2591 extends IntegratedCircuit implements DualChannelLuxSensor
{
    use TSL2591API;

    protected bool $booted = false;

    /**
     * @throws TSL2591Exception
     */
    public function __construct(
        protected readonly I2CDevice $carrier,
        protected readonly ?GPIOBus $gpio,
        protected TSL2591Gain $gain_enum,
        protected TSL2591IntegrationTime $integration_time_enum
    ) {
        $this->boot();

    }

    public function getLuminance(): int|float
    {
        return $this->lux;
    }

    /**
     * @throws TSL2591Exception
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'device_id' => $this->deviceId(),
            'gain' => $this->getGain(),
            'full_spectrum' => $this->fullSpectrum(),
            'infrared' => $this->infrared(),
            'integration_time' => $this->getIntegrationTime(),
            'lux' => $this->lux(),
            'nopersist_threshold_high' => $this->getNoPersistThresholdHigh(),
            'nopersist_threshold_low' => $this->getNoPersistThresholdLow(),
            'persist' => $this->getPersist(),
            'raw_luminosity' => $this->rawLuminosity(),
            'threshold_high' => $this->getThresholdHigh(),
            'threshold_low' => $this->getThresholdLow(),
            'visible' => $this->visible(),
            default => throw TSL2591Exception::invalidProperty($name)
        };
    }

    /**
     * @throws TSL2591Exception
     */
    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'gain' => $this->setGain($value),
            'integration_time' => $this->setIntegrationTime($value),
            'persist' => $this->setPersist((int) $value),
            'nopersist_threshold_high' => $this->setNoPersistThresholdHigh((int) $value),
            'nopersist_threshold_low' => $this->setNoPersistThresholdLow((int) $value),
            'threshold_high' => $this->setThresholdHigh((int) $value),
            'threshold_low' => $this->setThresholdLow((int) $value),
            default => throw TSL2591Exception::invalidProperty($name)
        };
    }

    /**
     * @throws TSL2591Exception
     */
    protected function boot(): void
    {
        if (! $this->booted) {
            if ($this->hasValidDeviceId()) {
                $this->enable();
                $this->gain = $this->gain_enum;
                $this->integration_time = $this->integration_time_enum;

                $this->booted = true;
            } else {
                throw TSL2591Exception::failedToFind();
            }
        }
    }

    /**
     * @throws Exception
     */
    public static function connection(string $driver): TSL2591Factory
    {
        return new TSL2591Factory(
            I2C::connection($driver)
        );
    }
}
