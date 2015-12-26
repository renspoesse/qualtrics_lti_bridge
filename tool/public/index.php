<?php

/**
 * This is the default entry point for the tool. It should be specified as the LTI endpoint for the Tool Consumer.
 */

require_once "../Config.php";               // Tool settings.

try {

    require_once "../ConsumerSecrets.php";  // Authentication class for Tool Consumers.
    require_once "../LTI.php";              // LTI class; contains the main logic for the tool.

    // Create the OAuth data store holding consumer secrets. All secrets defined in the configuration are added to the data store.

    $secrets = new ConsumerSecrets();

    foreach (Config::get("consumerSecrets") as $key => $value)
        $secrets->set_consumer($key, $value);

    // Create an instance of the LTI class.

    $launchParams = $_REQUEST;

    if (!Config::get("allowUrlOverrides") || empty($launchParams["ext_qualtrics_url"]))
        $launchParams["ext_qualtrics_url"] = Config::get("ext_qualtrics_url");

    if (!Config::get("allowIdOverrides") || empty($launchParams["ext_survey_id"]))
        $launchParams["ext_survey_id"] = Config::get("ext_survey_id");

    $lti = new LTI(

        $launchParams,  // Pass the launch parameters for the LTI request.
        $secrets        // Pass the collection of consumers that can be authenticated.
    );

    // Check if the request is a grading callback from Qualtrics.

    if ($lti->isValidGradingCallback()) {

        if (Config::get("provideGrading"))
            $lti->performGradingCallback();

        echo "Your result has been received from Qualtrics. This means everything went fine :)" . "<br />";
        echo "You can close this window now.";
    }
    else {

        // 1. Validate the launch request.

        if (!$lti->isValidLaunchRequest()) {

            // The Tool Consumer made an invalid LTI request.
            // Set the HTTP response to 400 (Bad Request) and stop script execution.
            // It's the Tool Consumer's responsibility to handle the response code.

            http_response_code(400);
            exit("Not a valid LTI launch request.");
        }

        // 2. Identify the user.

        if (!$lti->isAuthenticated()) {

            // The request didn't pass OAuth authentication.
            // Set the HTTP response to 402 (Unauthorized) and stop script execution.
            // It's the Tool Consumer's responsibility to handle the response code.

            http_response_code(402);
            exit("Launch request could not be authorized.");
        }

        // 3. Register a session to perform the grading callback if allowed and supported.

        if (Config::get("provideGrading"))
            $lti->registerCallbackSession();

        // 4. Launch the learning tool.

        $lti->launch();

        // A HTTP redirect has been performed and this code is never executed. This is the end of script execution.
    }
}
catch (Exception $ex) {

    http_response_code(500);
    exit(Config::get("debug") ? $ex : "Oops, something went wrong on the server.");
}