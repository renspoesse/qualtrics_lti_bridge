<?php

/**
 * Class Config
 *
 * The Config class contains configuration settings for the tool. Settings can be accessed like this:
 *
 * Config::set("qualtricsUrl", "newValue");
 * Config::get("qualtricsUrl");
 */
class Config
{
    /**
     * An array of configuration settings used throughout the tool.
     * Default values are provided here and can be retrieved or overriden later using
     * the get() and set() methods below.
     */
    protected static $config = array(

        "qualtricsUrl"      => "https://nlpsych.qualtrics.com", // The base url for Qualtrics surveys to address.
        "allowUrlOverrides" => true                             // Whether or not Tool Consumers are allowed to override qualtricsUrl by specifying a custom value.
    );

    /**
     * Config constructor.
     *
     * The constructor is private to prevent initiating the Config class, as it's meant to be treated statically.
     */
    private function __construct() { }

    /**
     * Sets a configuration value.
     *
     * @param string $key The key to set, for example: "qualtricsUrl".
     * @param mixed  $val The value to set, for example: "youruniversity.qualtrics.com".
     */
    protected static function set($key, $val)
    {
        self::$config[$key] = $val;
    }

    /**
     * Gets a configuration value.
     *
     * @param string $key The key of the value to retrieve.
     *
     * @return mixed
     */
    protected static function get($key)
    {
        return self::$config[$key];
    }
}