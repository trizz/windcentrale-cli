# Windcentrale API and CLI tool

This repository contains a simple API class and CLI tool to access the data
provided by [Windcentrale winddelen](https://www.windcentrale.nl/) ("windshares"). This makes it possible to get the 
produced energy per wind mill almost realtime.

## How to use the CLI tool
> To use the Windcentrale API class in your own code, see below.

- Download or clone this repository and run a `composer install`.
- Run `./windcentrale` to view all available commands.
- Run `./windcentrale mills` to get an overview of the available mills.
- Run `./windcentrale production <mill_short_name>` to get the production data of the given mill.
- Run `./windcentrale mill_data <mill_short_name>` to get the mill data of the given mill.
- Run `./windcentrale details <mill_short_name>` to get the both the production and mill data of the given mill.

For example `./windcentrale production jongeheld` will return the current production data of the mill 'De Jonge Held'.

## Using the export function
By default invoking the command will return the results optimized for viewing it from the console. However, there are
various possible methods of 'consuming' the data by passing the `--export` option. This can, for example, be used to
automate the process of reading the data.

| Name | Description
| --- | ---
| pretty | The default export method. Will print a nice table in your console with some of the values made a bit more readable.
| cli | Output the data in its most basic form.
| json | Output the data as a JSON string.
| influx | When correctly configured in `config.yml` the various datapoints are being written to an Influx database. No output is returned.

For example `./windcentrale production jongeheld --export=json` will output the production data in a JSON string.

## MQTT Daemon
When running `./windcentrale mqtt-daemon jongeheld` a long running process is started that will publish two messages
to the configured MQTT Broker every five seconds, one with the mill data and one with te production per windshare. The
used topics are `windcentale/{mill-slug}/production` and `windcentale/{mill-slug}/mill` (for example: 
`windcentrale/jongeheld/production`). The body of the message contains the same output JSON output as the `production`
and `mill_data` commands.

## Using the Windcentrale API Class
To use the Windcentrale API class to get the requested data as a [Collection](https://laravel.com/docs/5.5/collections) to use
in your own scripts, you can create a new instance of `trizz\WindcentraleApi\Windcentrale`. At this moment a Collection must be
passed as constructor argument, containing the parsed `settings.yml`, but it must have at least the `urls` part.

```php
$settings = new Illuminate\Support\Collection(['urls' => [
    'production' => 'https://zep-api.windcentrale.nl/production/%d', 
    'mill_data' => 'https://zep-api.windcentrale.nl/production/%d/live', 
]);

$windcentraleApi = new \trizz\WindcentraleApi\Windcentrale($settings);
// Mill ID 2 is 'De Jonge Held'.
$data = $windcentraleApi->getProductionData(2);
```

## Thanks
Parts of the Windcentrale API code is based on the original Perl script made by [damonnk](https://github.com/damonnk/windcentrale).
It provided a great starting point for creating this code.