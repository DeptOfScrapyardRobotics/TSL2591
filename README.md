Introduction
============

PHP Package for the TSL2591 high precision ambient light sensor.

Compatible I2C Interfaces
===============
The TSL2591 communicates with your device over I2C, the InterIntegrated Circuit Protocol.

You can interface with a TSL2591 with this package the following ways:
* A Linux Single-Board Computer's exposed GPIO pins using the dedicated I2C SDA/SCL pins
* An MPSSE-enabled USB-to-Serial device such as an FT232H generally using D0 and SCL and D1 for SDA connected to nearly any Linux or MacOS USB port.

Dependencies
=============
This package makes use of modules within:
* [The ScrapyardIO Framework](https://github.com/ScrapyardIO/framework)

This package also requires one of the following extensions in order to interface with I2C
* [POSI Extension v^0.4.0 or newer](https://github.com/php-io-extensions/posi)
* [FTDI Extension v^0.4.0 or newer](https://github.com/php-io-extensions/ftdi)

In addition, an extension wrapper package is needed

For ext-posi
* [Microscrap POSIX Package v0.4.0 or newer](https://github.com/microscrap/posix)
* [Microscrap Native I2C Package v0.4.0 or newer](https://github.com/microscrap/gpio)

For ext-ftdi
* [Microscrap FTDI Package v0.4.0 or newer](https://github.com/microscrap/ftdi)
* [Microscrap MPSSE Package v0.4.0 or newer](https://github.com/microscrap/mpsse)

Installing from Composer
====================
Inside the root of your PHP Project, simply require the TSL2591 package from composer
```shell
composer require dept-of-scrapyard-robotics/tsl2591
```

Framework Configuration
====================

If you would like to use the ScrapyardIO Framework to bootstrap your sensor without
wasting lines configuring your sensor right in the script you can add your desired
configuration to scrapyard-io.php, such as in this example: 
```php

use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\TSL2591;

return [
    'boards' => [
        'tsl2591-native' => [
            'class_name' => TSL2591::class,
            'connection' => [
                'driver' => 'native',
            ],
            'startup' => [
                'i2c' => [
                    'chip_device' => 1,
                    'slave_address' => 0x29,
                ],
            ],
        ],
        'tsl2591-usb' => [
            'class_name' => TSL2591::class,
            'connection' => [
                'driver' => 'usb',
            ],
            'startup' => [
                'i2c' => [
                    'chip_device' => 'ft232h',
                    'slave_address' => 0x29,
                ],
            ],
        ]              
    ]   
];
```

The keys you choose to represent the integrated circuit's definition are up to you.

To bootstrap quickly using a ScrapyardIO AmbientLightSensor object you can start with the following:

```php

use RealityInterface\Sensors\Applied\AmbientLighting\AmbientLightSensor;

$tsl2591 = AmbientLightSensor::using('tsl2591-native');

$lux = $tsl2591->getLuminance();

```

Basic Usage
============
To use the sensor directly without the framework simply do the following:
```php
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\TSL2591;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591I2CAddress;

$native_sensor = TSL2591::connection('native')
    ->i2c(1, TSL2591I2CAddress::DEFAULT->value)
    ->create();
    
$lux = $native_sensor->lux;

```

Advanced Usage
==============
Gain and integration time both influence sensitivity and exposure:
* Gain controls amplification of the light signal.
* Integration time controls how long the sensor accumulates light before a reading.

When setting up the sensor directly with the fluent API you can set both:
```php
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\TSL2591;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591Gain;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591I2CAddress;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591IntegrationTime;

$native_sensor = TSL2591::connection('native')
    ->i2c(1, TSL2591I2CAddress::DEFAULT->value)
    ->gain(TSL2591Gain::HIGH)
    ->integrationTime(TSL2591IntegrationTime::MS300)
    ->create();
```

When using the framework config, include them in the startup array:
```php
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\TSL2591;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591Gain;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591IntegrationTime;

return [
    'boards' => [
        'tsl2591-native' => [
            'class_name' => TSL2591::class,
            'connection' => [
                'driver' => 'native',
            ],
            'startup' => [
                'i2c' => [
                    'chip_device' => 1,
                    'slave_address' => 0x29,
                ],
                'gain' => [
                    TSL2591Gain::HIGH,
                ],
                'integrationTime' => [
                    TSL2591IntegrationTime::MS300,
                ],
            ],
        ],
    ],
];
```

When using the sensor directly, you can also read the active values:
```php
$gain = $native_sensor->gain;
$integration_time = $native_sensor->integration_time;
```

Injected into the Framework
=========

To inject into the framework, that is, bootstrap the device directly then pass it off to an `AmbientLightSensor` object
simply do the following:

```php

use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\TSL2591;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591I2CAddress;
use RealityInterface\Sensors\Applied\AmbientLighting\AmbientLightSensor;

$usb_sensor = TSL2591::connection('usb')
    ->i2c('ft232h', TSL2591I2CAddress::DEFAULT->value)
    ->create();
    
$als = AmbientLightSensor::as($usb_sensor);

$lux = $als->getLuminance();

```

Calibration
==========

To calibrate the sensor on the chip, you will need a reference sensor, that is shown to 
give out reliable Luminance readings. 
* Bootstrap or inject the sensor into an `AmbientLightSensor` object.
* Make sure your sensor and the reference sensor are looking at the same target
* use the getLuminance method on the TSL2591 at the same time as you pull a measurement from the reference.
* Use the `AmbientLightSensor`'s calibrate method

Refer to this simulated example
```php

use RealityInterface\Sensors\Applied\AmbientLighting\AmbientLightSensor;

$tsl2591 = AmbientLightSensor::using('tsl2591-native');

// Simulated out-of-scope event measuring both sensors at the same time
$current_lux = $tsl2591->getLuminance();
$ref = (int) "your-reference-lux";

// Add the values from your calibration session here. 
$tsl2591 = $tsl2591->calibrate($ref, $current_lux);

// The output of getLuminance() will now be adjusted with your compensation factor
// computed from the calibration.
$adjusted_lux = $tsl2591->getLuminance();

```

Sensor API
==========
The getters and setters in this API interface with the device directly (register reads/writes),
so you can use simple property access while still working against the chip itself.

Readable Properties (Getters)
-----------------------------
* `$sensor->device_id`  
  Reads and returns the device ID register value.

* `$sensor->gain` (`TSL2591Gain`)  
  Gets the sensor gain. Valid gain values are:
  * `TSL2591Gain::LOW` (1x)
  * `TSL2591Gain::MEDIUM` (25x)
  * `TSL2591Gain::HIGH` (428x)
  * `TSL2591Gain::MAX` (9876x)

* `$sensor->integration_time` (`TSL2591IntegrationTime`)  
  Gets the sensor integration time. Valid integration time values are:
  * `TSL2591IntegrationTime::MS100` (100 millis)
  * `TSL2591IntegrationTime::MS200` (200 millis)
  * `TSL2591IntegrationTime::MS300` (300 millis)
  * `TSL2591IntegrationTime::MS400` (400 millis)
  * `TSL2591IntegrationTime::MS500` (500 millis)
  * `TSL2591IntegrationTime::MS600` (600 millis)

* `$sensor->full_spectrum`  
  Reads the full spectrum (IR + visible) and returns it as a 16-bit unsigned value.

* `$sensor->infrared`  
  Reads the infrared channel and returns it as a 16-bit unsigned value.

* `$sensor->visible`  
  Reads the visible channel and returns it as a 16-bit unsigned value.

* `$sensor->raw_luminosity`  
  Reads both channels and returns `[channel0, channel1]`, where channel0 is IR + visible and channel1 is IR-only.

* `$sensor->lux`  
  Reads the sensor and calculates lux from both channels.

* `$sensor->persist`  
  Gets the interrupt persist filter (0-15), the number of consecutive out-of-range ALS cycles needed to trigger an interrupt.

* `$sensor->threshold_high`  
  Gets the ALS high threshold (16-bit). If readings stay above this threshold for the persist duration, an interrupt is generated.

* `$sensor->threshold_low`  
  Gets the ALS low threshold (16-bit). If readings stay below this threshold for the persist duration, an interrupt is generated.

* `$sensor->nopersist_threshold_high`  
  Gets the no-persist ALS high threshold (16-bit). Crossing this threshold triggers an immediate interrupt.

* `$sensor->nopersist_threshold_low`  
  Gets the no-persist ALS low threshold (16-bit). Crossing this threshold triggers an immediate interrupt.

Writable Properties (Setters)
-----------------------------
* `$sensor->gain = TSL2591Gain::HIGH;`  
  Sets sensor gain.

* `$sensor->integration_time = TSL2591IntegrationTime::MS300;`  
  Sets sensor integration time.

* `$sensor->persist = 5;`  
  Sets persist filter (only the lower 4 bits are written).

* `$sensor->threshold_high = 12000;`  
  Sets ALS high threshold.

* `$sensor->threshold_low = 300;`  
  Sets ALS low threshold.

* `$sensor->nopersist_threshold_high = 12000;`  
  Sets no-persist ALS high threshold.

* `$sensor->nopersist_threshold_low = 300;`  
  Sets no-persist ALS low threshold.
