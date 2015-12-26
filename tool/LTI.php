<?php

require_once "lib/OAuth.php"; // OAuth library code.

// TODO: check against LTI 1.1 specification.

/**
 * Basic LTI class that does the setup and provides utility functions.
 */
class LTI
{
    /*
    public $valid = false;
    public $complete = false;
    public $message = false;
    public $basestring = false;
    public $info = false;
    public $row = false;
    public $context_id = false;  // Override context_id
    */

    private $launchParams = array();
    private $consumerSecrets = null;

    private $isValidated = false;
    private $isAuthenticated = false;

    /**
     * Creates a new instance of the LTI class.
     *
     * @param array               $launchParams    The LTI launch parameters for the request.
     * @param OAuthDataStore|null $consumerSecrets The OAuthDataStore that holds consumer secrets for authentication. Null to disable authentication.
     * @param bool|true           $useSession      Whether launch parameters should be retrieved from and stored in a session variable.
     */
    public function __construct($launchParams, $consumerSecrets = null, $useSession = true)
    {
        // Check if launch parameters have been specified.

        if (empty($launchParams)) {

            // Launch parameters haven't been specified. If session is enabled, we can try retrieving previous
            // parameters from the session context.

            if ($useSession && isset($_SESSION["launchParams"])) {

                $launchParams = $_SESSION["launchParams"];
            }
            else
                throw new InvalidArgumentException("launchParams should be a non-empty array.");
        }

        $this->launchParams = $launchParams;

        // Store the launch parameters in a session variable if requested. If not, clear the session variable that might have been set before.

        if ($useSession)
            $_SESSION["launchParams"] = $launchParams;
        else
            unset($_SESSION["launchParams"]);
    }

    /**
     * Checks if this is an LTI 1.1 message with minimum values to meet the protocol.
     * Required parameters have been taken from http://www.imsglobal.org/specs/ltiv1p1/implementation-guide
     *
     * @return bool True if validated, otherwise false.
     */
    public function validate()
    {
        // This indicates that this is a basic launch message. This allows a TP to accept a number of different LTI
        // message types at the same launch URL. This parameter is required.

        if (!array_key_exists("lti_message_type", $this->launchParams) || $this->launchParams["lti_message_type"] != "basic-lti-launch-request")
            $this->isValidated = false;

        // This indicates which version of the specification is being used for this particular message. Since launches
        // for version 1.1 are upwards compatible with 1.0 launches, this value is not advanced for LTI 1.1. This
        // parameter is required.

        else if (!array_key_exists("lti_version", $this->launchParams) || $this->launchParams["lti_version"] != "LTI-1p0")
            $this->isValidated = false;

        // This is an opaque unique identifier that the TC guarantees will be unique within the TC for every placement
        // of the link. If the tool / activity is placed multiple times in the same context, each of those placements
        // will be distinct. This value will also change if the item is exported from one system or context and imported
        // into another system or context. This parameter is required.

        else if (!array_key_exists("resource_link_id", $this->launchParams) || empty($this->launchParams["resource_link_id"]))
            $this->isValidated = false;

        // We have some custom parameters that apply to Qualtrics requests only.

        else if (!array_key_exists("qualtricsUrl", $this->launchParams) || empty($this->launchParams["qualtricsUrl"]))
            $this->isValidated = false;

        else if (!array_key_exists("surveyId", $this->launchParams) || empty($this->launchParams["surveyId"]))
            $this->isValidated = false;

        return $this->isValidated;
    }

    /**
     * Tries to authenticate the LTI launch request based on the provided launch parameters.
     *
     * @return bool True if authenticated, otherwise false.
     */
    public function authenticate()
    {
        // Check if a consumer key was provided. If not, we have nothing to authenticate and therefore return false.

        if (!empty($this->launchParams["oauth_consumer_key"])) {

            // Check if a data store of consumer secrets has been set. If not, authentication has been disabled.

            if (!isset($this->consumerSecrets)) {

                $this->isAuthenticated = true;
            }
            else {

                // Perform OAuth verification on the launch parameters.

                $server = new OAuthServer($this->consumerSecrets);
                $server->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());

                $request = OAuthRequest::from_request(null, null, $this->launchParams);

                try {

                    $server->verify_request($request);
                    $this->isAuthenticated = true;

                } catch (Exception $ex) {

                    $this->isAuthenticated = false;
                }
            }
        }
        else {

            $this->isAuthenticated = false;
        }

        return $this->isAuthenticated;
    }

    public function launch($performRedirect = true)
    {
        if (!$this->isValidated)
            throw new Exception("LTI launch request needs to be validated first.");

        if (!$this->isAuthenticated)
            throw new Exception("LTI launch request needs to be authenticated first.");

        // TODO: in hoeverre bestaan http_ functies nog in pecl? Goed documenteren in readme.

        // Any custom (non LTI) parameters that have been specified by the Tool Consumer should be passed to
        // Qualtrics to allow for customization. As per the LTI specification, these are prefixed with ext_.

        $urlParams = array(

            "query" => "SID=" . $this->launchParams["surveyId"]
        );

        foreach ($this->launchParams as $key => $val) {

            if (strpos($key, "ext_") !== 0)
                $urlParams[$key] = $val;
        }

        // Build the url to the Qualtrics endpoint.

        $url = http_build_url($this->launchParams["qualtricsUrl"], $urlParams);

        // Perform the GET request.

        if ($performRedirect) {

            // Redirect the request to a new context.

            header("Location:" . $url);
        }
        else {

            // Perform the request within the current context.

            $request = new HTTPRequest($url, HTTP_METH_POST);
            $request->send();

            return $request->getResponseBody();
        }
    }

    function addSession($location)
    {
        if (ini_get('session.use_cookies') == 0) {
            if (strpos($location, '?') > 0) {
                $location = $location . '&';
            }
            else {
                $location = $location . '?';
            }
            $location = $location . session_name() . '=' . session_id();
        }

        return $location;
    }

    // TODO: Add javasript version if headers are already sent?
    function redirect()
    {
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['PHP_SELF'];
        $location = $_SERVER['HTTPS'] ? 'https://' : 'http://';
        $location = $location . $host . $uri;
        $location = $this->addSession($location);
        header("Location: $location");
    }

    function isInstructor()
    {
        $roles = $this->info['roles'];
        $roles = strtolower($roles);
        if (!(strpos($roles, "instructor") === false)) return true;
        if (!(strpos($roles, "administrator") === false)) return true;

        return false;
    }

    function getUserEmail()
    {
        $email = $this->info['lis_person_contact_email_primary'];
        if (strlen($email) > 0) return $email;
        # Sakai Hack
        $email = $this->info['lis_person_contact_emailprimary'];
        if (strlen($email) > 0) return $email;

        return false;
    }

    function getUserShortName()
    {
        $email = $this->getUserEmail();
        $givenname = $this->info['lis_person_name_given'];
        $familyname = $this->info['lis_person_name_family'];
        $fullname = $this->info['lis_person_name_full'];
        if (strlen($email) > 0) return $email;
        if (strlen($givenname) > 0) return $givenname;
        if (strlen($familyname) > 0) return $familyname;

        return $this->getUserName();
    }

    function getUserName()
    {
        $givenname = $this->info['lis_person_name_given'];
        $familyname = $this->info['lis_person_name_family'];
        $fullname = $this->info['lis_person_name_full'];
        if (strlen($fullname) > 0) return $fullname;
        if (strlen($familyname) > 0 and strlen($givenname) > 0) return $givenname + $familyname;
        if (strlen($givenname) > 0) return $givenname;
        if (strlen($familyname) > 0) return $familyname;

        return $this->getUserEmail();
    }

    function getUserKey()
    {
        $oauth = $this->info['oauth_consumer_key'];
        $id = $this->info['user_id'];
        if (strlen($id) > 0 and strlen($oauth) > 0) return $oauth . ':' . $id;

        return false;
    }

    function getUserImage()
    {
        $image = $this->info['user_image'];
        if (strlen($image) > 0) return $image;
        $email = $this->getUserEmail();
        if ($email === false) return false;
        $size = 40;
        $grav_url = $_SERVER['HTTPS'] ? 'https://' : 'http://';
        $grav_url = $grav_url . "www.gravatar.com/avatar.php?gravatar_id=" . md5(strtolower($email)) . "&size=" . $size;

        return $grav_url;
    }

    function getResourceKey()
    {
        $oauth = $this->info['oauth_consumer_key'];
        $id = $this->info['resource_link_id'];
        if (strlen($id) > 0 and strlen($oauth) > 0) return $oauth . ':' . $id;

        return false;
    }

    function getResourceTitle()
    {
        $title = $this->info['resource_link_title'];
        if (strlen($title) > 0) return $title;

        return false;
    }

    function getConsumerKey()
    {
        $oauth = $this->info['oauth_consumer_key'];

        return $oauth;
    }

    function getCourseKey()
    {
        if ($this->context_id) return $this->context_id;
        $oauth = $this->info['oauth_consumer_key'];
        $id = $this->info['context_id'];
        if (strlen($id) > 0 and strlen($oauth) > 0) return $oauth . ':' . $id;

        return false;
    }

    function getCourseName()
    {
        $label = $this->info['context_label'];
        $title = $this->info['context_title'];
        $id = $this->info['context_id'];
        if (strlen($label) > 0) return $label;
        if (strlen($title) > 0) return $title;
        if (strlen($id) > 0) return $id;

        return false;
    }

    function dump()
    {
        if (!$this->valid or $this->info == false) return "Context not valid\n";
        $ret = "";
        if ($this->isInstructor()) {
            $ret .= "isInstructor() = true\n";
        }
        else {
            $ret .= "isInstructor() = false\n";
        }
        $ret .= "getUserKey() = " . $this->getUserKey() . "\n";
        $ret .= "getUserEmail() = " . $this->getUserEmail() . "\n";
        $ret .= "getUserShortName() = " . $this->getUserShortName() . "\n";
        $ret .= "getUserName() = " . $this->getUserName() . "\n";
        $ret .= "getUserImage() = " . $this->getUserImage() . "\n";
        $ret .= "getResourceKey() = " . $this->getResourceKey() . "\n";
        $ret .= "getResourceTitle() = " . $this->getResourceTitle() . "\n";
        $ret .= "getCourseName() = " . $this->getCourseName() . "\n";
        $ret .= "getCourseKey() = " . $this->getCourseKey() . "\n";
        $ret .= "getConsumerKey() = " . $this->getConsumerKey() . "\n";

        return $ret;
    }
}
