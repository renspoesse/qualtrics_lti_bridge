<?php

/**
 * This is the default entry point for the tool. It should be specified as the LTI endpoint for the Tool Consumer.
 *
 * TODO: supported parameters hier noteren.
 */

require_once "../Config.php";           // Tool settings.
require_once "../ConsumerSecrets.php";  // Authentication class for Tool Consumers.
require_once "../LTI.php";              // LTI class; contains the main logic for the tool.

// Create the OAuth data store holding consumer secrets. All secrets defined in the configuration are added to the data store.

$secrets = new ConsumerSecrets();

foreach (Config::get("consumerSecrets") as $key => $value)
    $secrets->set_consumer($key, $value);

// Create an instance of the LTI class.

$lti = new LTI(

    $consumerSecrets = $secrets         // Pass the collection of consumers that can be authenticated.
// TODO: overige parameters hierbij zetten.
);

// 1. Validate the launch request and optional grading callback.

if (!$lti->isValidRequest()) {

    // The Tool Consumer made an invalid LTI request.

    // TODO: WAT VERWACHT LTI BIJ FOUTEN?
}

// 2. Identify the user.

if (!$lti->authenticate()) {

    // The request didn't pass OAuth authentication.

    // TODO: WAT VERWACHT LTI NU?
}

// 3. Launch the learning tool.

// 4. Pass back the grade if requested and enabled.

// TODO: er moet ook een grading callback zijn ivm Coursera required opdrachten.
// TODO: iets met Context Roles doen?

/*
$context = new BLTI('moocs_are_great', false, false);
if ( ! $context->valid ) {
    print "<p style=\"color:red\">Could not establish context: ".$context->message."<p>\n";
    die();
}

$qualtrix_survey_id = $_GET['SID'];
$user_token = $_POST['user_id']; // note: in Novoed this is an "anonymized" 320bit hash
$survey_version = $_GET['version'];
$link_label = $_GET['l'];

$get_array = array(
    'SID' => $qualtrix_survey_id,
    'a' => $user_token,
    'version' => $survey_version,
);


//it seems like any non-www subdomain will redirect to appropriate survey/brand
//still we probably want to explicitly call out subdomain at some point.
$redirect_url = 'https://anything.qualtrics.com/SE/';
// $redirect_url = 'https://'
// $redirect_url .= $qualtrix_subdomain;
// $redirect_url .= '.qualtrics.com/SE/';
// header("Location: $redirect_url");
// die();
?>
<html>
<head>
    <style>
        body { background-color:#aaa;height:130px;border:0;margin:0;padding:0;overflow:hidden; }
        input#submit_button { display:block;width:98%;height:108px;margin:10px 1%;padding:10px; }
    </style>
</head>
<body>
    <form method="GET" action="<?php echo $redirect_url; ?>" id="lti-launch">
        <?php
            foreach ($get_array as $key => $val) {
                echo "<input type=\"hidden\" name=\"$key\" value=\"$val\">";
            }
            foreach ($_POST as $key => $val) {
                if (strpos($key, "custom_") !== false) {
                echo "<input type=\"hidden\" name=\"$key\" value=\"$val\">";
                }
            }
        ?>
        <input id="submit_button" type="submit" value="<?php echo $link_label; ?>">
    </form>
    <script>
    var ltiform = document.getElementById("lti-launch");
        ltiform.submit();
        window.parent.document.getElementById("basicltiLaunchFrame").height = "130px";

    </script>
</body>
</html>
*/