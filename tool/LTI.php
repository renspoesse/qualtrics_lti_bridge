<?php

require_once "lib/OAuth.php"; // OAuth library code.

/**
 * Basic LTI class that does the setup and provides utility functions.
 */
class LTI
{
    private $launchParams = array();
    private $consumerSecrets = null;

    /**
     * Creates a new instance of the LTI class.
     *
     * @param array               $launchParams    The LTI launch parameters for the request.
     * @param OAuthDataStore|null $consumerSecrets The OAuthDataStore that holds consumer secrets for authentication. Null to disable authentication.
     */
    public function __construct($launchParams, $consumerSecrets = null)
    {
        $this->launchParams = $launchParams;
        $this->consumerSecrets = $consumerSecrets;
    }

    /**
     * Checks if this is an LTI 1.1 launch request with minimum values to meet the protocol.
     * Required parameters have been taken from http://www.imsglobal.org/specs/ltiv1p1/implementation-guide
     *
     * @return bool True if valid, otherwise false.
     */
    public function isValidLaunchRequest()
    {
        if (empty($this->launchParams))
            return false;

        // This indicates that this is a basic launch message. This allows a TP to accept a number of different LTI
        // message types at the same launch URL. This parameter is required.

        if (!array_key_exists("lti_message_type", $this->launchParams) || $this->launchParams["lti_message_type"] != "basic-lti-launch-request")
            return false;

        // This indicates which version of the specification is being used for this particular message. Since launches
        // for version 1.1 are upwards compatible with 1.0 launches, this value is not advanced for LTI 1.1. This
        // parameter is required.

        else if (!array_key_exists("lti_version", $this->launchParams) || $this->launchParams["lti_version"] != "LTI-1p0")
            return false;

        // This is an opaque unique identifier that the TC guarantees will be unique within the TC for every placement
        // of the link. If the tool / activity is placed multiple times in the same context, each of those placements
        // will be distinct. This value will also change if the item is exported from one system or context and imported
        // into another system or context. This parameter is required.

        else if (!array_key_exists("resource_link_id", $this->launchParams) || empty($this->launchParams["resource_link_id"]))
            return false;

        // We have some custom parameters that apply to Qualtrics requests only.

        else if (!array_key_exists("ext_qualtrics_url", $this->launchParams) || empty($this->launchParams["ext_qualtrics_url"]))
            return false;

        else if (!array_key_exists("ext_survey_id", $this->launchParams) || empty($this->launchParams["ext_survey_id"]))
            return false;

        return true;
    }

    /**
     * Checks if this is a valid grading callback.
     * @return bool True if valid, otherwise false.
     */
    public function isValidGradingCallback()
    {
        if (empty($this->launchParams))
            return false;

        if (!array_key_exists("lis_result_sourcedid", $this->launchParams) || empty($this->launchParams["lis_result_sourcedid"]))
            return false;

        // We have some custom parameters that apply to Qualtrics requests only.

        else if (!array_key_exists("ext_grade", $this->launchParams) || empty($this->launchParams["ext_grade"]))
            return false;

        return true;
    }

    /**
     * Tries to authenticate the LTI launch request based on the provided launch parameters.
     *
     * @return bool True if authenticated, otherwise false.
     */
    public function isAuthenticated()
    {
        // Check if a consumer key was provided. If not, we have nothing to authenticate and therefore return false.

        if (!empty($this->launchParams["oauth_consumer_key"])) {

            // Check if a data store of consumer secrets has been set. If not, authentication has been disabled.

            if (!isset($this->consumerSecrets))
                return true;

            // Perform OAuth verification on the launch parameters.

            $server = new OAuthServer($this->consumerSecrets);
            $server->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());

            $request = OAuthRequest::from_request(null, null, $_REQUEST);

            try {

                $server->verify_request($request);
                return true;

            }
            catch (Exception $ex) {

                if (Config::get("debug"))
                    exit($ex);

                return false;
            }
        }

        return false;
    }

    /**
     * Launches the LTI tool after validation and authentication.
     *
     * @return string The Qualtrics response if not redirected.
     * @throws Exception
     */
    public function launch()
    {
        // Any parameters that have been specified by the Tool Consumer can be passed to Qualtrics to allow for customization.

        $urlParams = array(

            "SID" => $this->launchParams["ext_survey_id"]
        );

        foreach ($this->launchParams as $key => $val) {

            if (Config::get("ext_pass_all") || in_array($key, Config::get("ext_pass_params")))
                $urlParams[$key] = $val;
        }

        // Build the request to the Qualtrics endpoint.

        $url = $this->launchParams["ext_qualtrics_url"];
        $query = http_build_query($urlParams);

        // Perform the redirect.

        header("Location: " . $url . "?" . $query);
    }

    /**
     * Registers a session variable that holds information for the grading callback if applicable.
     * If not enough information for the callback is available, it won't be registered.
     *
     * @return bool True if the variable was registered, false otherwise.
     */
    public function tryRegisterCallbackSession()
    {
        // The lis_result_sourcedid is a unique identifier in the Tool Consumer's gradebook.

        $sourcedId = $this->launchParams["lis_result_sourcedid"];
        $outcomeUrl = $this->launchParams["lis_outcome_service_url"];

        // If we have enough information to perform a callback, store the launch parameters in a session
        // variable to be able to do so. Note that we hold a session variable for each sourcedId - this
        // ensures that multiple tool requests within a single session are supported.

        if (!empty($sourcedId) && !empty($outcomeUrl)) {

            $_SESSION[$sourcedId] = $this->launchParams;
            return true;
        }

        return false;
    }

    /**
     * Performs a grading callback using the previously registered session variable that holds information for the callback.
     * If no session information is available, the callback won't be performed.
     *
     * @return bool True if the callback was performed, false otherwise.
     *
     * @throws Exception Throws an exception when either the grade received is invalid or invalid information has been stored in session.
     */
    public function tryPerformGradingCallback()
    {
        $consumerKey = $this->launchParams["oauth_consumer_key"];
        $resourceLinkId = $this->launchParams["resource_link_id"];
        $sourcedId = $this->launchParams["lis_result_sourcedid"];
        $grade = $this->launchParams["ext_grade"];

        if (empty($sourcedId) || !$this->isValidGrade($grade))
            return false;

        // Check if we have enough information for the callback.

        if (empty($sourcedId) || empty($_SESSION[$sourcedId]))
            return false;

        // Check if the information we have is valid.

        if (!$this->isValidGrade($grade))
            throw new Exception("Invalid grade received from Qualtrics.");

        if (empty($_SESSION[$sourcedId]["lis_outcome_service_url"]))
            throw new Exception("Somehow the callback information was stored in session, but the outcome service url is (now) empty.");

        // There's session information available for the grading callback with this sourcedid.
        // Use it to perform the callback.

        // TODO: perform grading callback to Coursera.

        //$db_connector = new \IMSGlobal\LTI\ToolProvider\DataConnector\DataConnector(null);
        //
        //$consumer = new \IMSGlobal\LTI\ToolProvider\ToolConsumer($consumerKey, $db_connector);
        //$resource_link = \IMSGlobal\LTI\ToolProvider\ResourceLink::fromConsumer($consumer, $resourceLinkId);
        //
        //$user = new \IMSGlobal\LTI\ToolProvider\User();
        //
        //$user->resourceLink = null; // TODO RENS
        //$user->ltiResultSourcedId = $sourcedId;
        //
        //$outcome = new \IMSGlobal\LTI\ToolProvider\Outcome($grade);
        //$ok = $resource_link->doOutcomesService(\IMSGlobal\LTI\ToolProvider\ResourceLink::EXT_WRITE, $outcome, $user);

        // Unset the session variable to prevent multiple callbacks.

        unset($_SESSION[$sourcedId]);

        return true;
    }

    /**
     * Checks if the grade is valid according to the LTI specification.
     * This means it should be a floating point between 0.0 and 1.0.
     *
     * @param $grade
     *
     * @return bool
     */
    private function isValidGrade($grade)
    {
        return is_numeric($grade) && floatval($grade) >= 0 && floatval($grade) <= 1;
    }
}