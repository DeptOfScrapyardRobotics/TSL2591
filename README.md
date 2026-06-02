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
* `The ScrapyardIO Framework <https://github.com/ScrapyardIO/framework>`_

This package also requires one of the following extensions in order to interface with I2C
* `POSI Extension v^0.4.0 or newer <https://github.com/php-io-extensions/posi>`_
* `FTDI Extension v^0.4.0 or newer <https://github.com/php-io-extensions/ftdi>`_

In addition, an extension wrapper package is needed

For ext-posi
* `Microscrap POSIX Package v0.4.0 or newer <https://github.com/microscrap/posix>`_
* `Microscrap Native I2C Package v0.4.0 or newer <https://github.com/microscrap/gpio>`_

For ext-ftdi
* `Microscrap FTDI Package v0.4.0 or newer <https://github.com/microscrap/ftdi>`_
* `Microscrap MPSSE Package v0.4.0 or newer <https://github.com/microscrap/mpsse>`_

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
    
$lux = $native->sensor->lux;

```

Injected into the Framework
=========

To inject into the framework, that is, bootstrap the device directly then pass it off to an AmbientLightSensor object
simply do the following:

```php

use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\TSL2591;
use DeptOfScrapyardRobotics\Sensors\TSL2591\TSL2591\Enums\TSL2591I2CAddress;
use RealityInterface\Sensors\Applied\AmbientLighting\AmbientLightSensor;

$usb_sensor = TSL2591::connection('usb')
    ->i2c('ft232h', TSL2591I2CAddress::DEFAULT->value)
    ->create();
    
$als = AmbientLightSensor::as($usb_sensor);

$lux = $tsl2591->getLuminance();

```

Calibration
==========

To calibrate the sensor on the chip, you will need a reference sensor, that is shown to 
give out reliable Luminance readings. 
* Bootstrap or inject the sensor into an AmbientLightSensor object.
* Make sure your sensor and the reference sensor are looking at the same target
* use the getLuminance method on the TSL2591 at the same time as you pull a measurement from the reference.
* Use the AmbientLightSensor's calibrate method

Refer to this simulated example
```php

use RealityInterface\Sensors\Applied\AmbientLighting\AmbientLightSensor;

$tsl2591 = AmbientLightSensor::using('tsl2591-native');

// Simulated out of scope event measuring both sensors at the same time
$current_lux = $tsl2591->getLuminance();
$ref = (int) "your-reference-lux";

// Add the values from your calibration session here. 
$tsl2591 = $tsl2591->calibrate($ref, $current_lux);

// The output of getLuminance() will now be adjusted with your compensation factor
// computed from the calibration.
$adjusted_lux = $tsl2591->getLuminance();

```
