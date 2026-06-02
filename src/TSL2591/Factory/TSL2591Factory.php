<?php

namespace DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Factory;

use BareMetal\CircuitFactory;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591Gain;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591IntegrationTime;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Exceptions\TSL2591Exception;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\TSL2591;
use Exception;
use Waveforms\Carriers\GPIO\Factory\GPIOConnectionBuilder;
use Waveforms\Carriers\I2C\Factory\I2CConnectionBuilder;

class TSL2591Factory extends CircuitFactory
{
    public string $consumer = 'tsl2591';

    public ?I2CConnectionBuilder $connection = null;

    protected TSL2591Gain $gain = TSL2591Gain::MEDIUM;

    protected ?GPIOConnectionBuilder $gpio_connection = null;

    protected TSL2591IntegrationTime $integration_time = TSL2591IntegrationTime::MS100;

    public function __construct(
        public I2CConnectionBuilder $i2c_connection,

    ) {}

    public function i2c(string|int $chip_device, int $slave_address): static
    {
        $this->connection = $this->i2c_connection->firstly($chip_device)
            ->slaveAddress($slave_address);

        return $this;
    }

    public function int(int $pin): static
    {
        return $this;
    }

    public function consumer(string $consumer): static
    {
        $this->consumer = $consumer;

        return $this;
    }

    public function gain(TSL2591Gain $gain): static
    {
        $this->gain = $gain;

        return $this;
    }

    public function integrationTime(TSL2591IntegrationTime $integration_time): static
    {
        $this->integration_time = $integration_time;

        return $this;
    }

    /**
     * @throws Exception
     * @throws TSL2591Exception
     */
    public function create(): TSL2591
    {
        $carrier = $this->connection?->boot();
        if (is_null($carrier)) {
            throw new Exception('A connection was not registered.');
        }

        $gpio = $this->gpio_connection?->consumer($this->consumer)->boot();

        return new TSL2591(
            $carrier, $gpio,
            $this->gain,
            $this->integration_time
        );
    }
}
