<?php

namespace QualtricsLTIBridge;

/**
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

        "debug"             => true,                                // True to display debug messages. Should be disabled in production environment.
        "ext_qualtrics_url" => "https://nlpsych.qualtrics.com/SE",  // The base url for Qualtrics surveys to address.
        "ext_survey_id"     => "SV_7U4egZ3kOYyO52B",                // The (default) survey to address.
        #"ext_survey_id"     => "SV_bO9IijOwGYyAXzL",                // The (default) survey to address.
        "ext_pass_params"   => array(                               // The parameters that should be passed from the launch request to Qualtrics.

            "user_id",
            "lis_result_sourcedid" // Required for grading callbacks.
        ),
        "ext_pass_all"      => false,                               // True to ignore the ext_pass_params value and pass all parameters to Qualtrics.
        "allowUrlOverrides" => true,                                // Whether or not Tool Consumers are allowed to override ext_qualtrics_url by specifying a custom value.
        "allowIdOverrides"  => true,                                // Whether or not Tool Consumers are allowed to override ext_survey_id by specifying a custom value.
        "consumerSecrets"   => array(                               // Consumer secrets for authentication. These should be kept private!

            "LTI_Bridge_Demonstration" => "powertotheteachers",
            "LTI_Bridge_Development"   => "powertotheresearchers"
        ),
        "provideGrading"    => true                                 // SEE README FOR IMPORTANT INFORMATION REGARDING GRADING.
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
    public static function set($key, $val)
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
    public static function get($key)
    {
        return self::$config[$key];
    }
}
