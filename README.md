[![DOI](https://zenodo.org/badge/21124/renspoesse/qualtrics_lti_bridge.svg)](https://zenodo.org/badge/latestdoi/21124/renspoesse/qualtrics_lti_bridge)

This Qualtrics LTI Bridge tool was developed by Rens Poesse at the University of Amsterdam.
For questions, please contact [Alexander Savi](http://www.alexandersavi.nl).

The project has been based on qualtrics_lti_bridge by Simon Wiles at Stanford University,
which is accessible at the link [tiny.cc/qualtricslti](http://tiny.cc/qualtricslti).

The tool currently follows the LTI 1.1 standard; this is documented as the LTI specification Coursera supports.
For the same reason OAuth 1.0a is used, in [Andy Smith's implementation](http://oauth.googlecode.com/svn/code/php/).

## Project goals and information

The main goal for developing this tool was to build a way by which Qualtrics surveys can be included
in a Coursera course for the purpose of A/B testing (i.e., test following different paths). However, the tool has
more general LTI use cases as can be read here:

* [Coursera documentation for using LTI](https://tech.coursera.org/app-platform/lti)
* [General information about LTI](https://www.imsglobal.org/activity/learning-tools-interoperability)

Another important goal of this project is to provide clear documentation and code comments to ensure
usefulness of the tool for anyone interested in using Qualtrics with other platforms in educational scenarios.

## Getting started

This section provides a short guide on how to get started using the Qualtrics LTI Bridge tool.

First, a quick introduction on the mechanisms at work. Learning Tools Interoperability (LTI) can be seen as an
interface contract for different learning tools to communicate. This means that LTI is not a piece of software but
a protocol definition that states how learning tools should interact.

In our example use case those learning tools are Coursera and Qualtrics. Qualtrics supports A/B testing, but Coursera
does not. So we want to integrate Qualtrics with Coursera. Contrarily however, Coursera supports LTI integration but
Qualtrics does not (yet). This is where the current project comes in. In LTI terms, our situation is as follows:

* Tool Consumer: Coursera (but can be anything that supports LTI)
* Tool Provider: Qualtrics LTI Bridge tool (this project) as a wrapper around Qualtrics

When the Tool Consumer wants to make an LTI defined request to Qualtrics, it needs to address this tool that in turn
addresses Qualtrics. The tool therefore needs to be accessible to the Tool Consumer, which means that it should run at
a publicly accessible endpoint (e.g., youruniversity.com/qualtrics-lti-bridge/tool).

So, to get started the following steps are required:

* Download a copy of all files in the `/tool` folder. The tool is written in PHP and therefore should be hosted on
a server that supports PHP execution (see dependencies below). `/tool/public/index.php` is the default entry point
for the tool and I recommend that you configure your server to automatically serve this file as the tool endpoint is
requested (i.e., youruniversity.com/qualtrics-lti-bridge/tool should point to youruniversity.com/qualtrics-lti-bridge/tool/public/index.php).
* Edit `/tool/Config.php` to suit your needs. This is your personal configuration and it should not be made public!
* Once the tool has been set up correctly, a target questionnaire should be made in Qualtrics. In the case of A/B testing,
a single survey should be created that contains several paths (A and B).
* Add an LTI Item to your lesson in Coursera. The launch url should correspond to the tool endpoint and the consumer
key and secret should be defined in the tool's configuration (step 2).

See below for a more detailed guide.

## Tool dependencies

The Qualtrics LTI Bridge tool only depends on PHP. It should work on a standard PHP 5.5+ installation with the following settings:

* Session cookies need to be enabled for callbacks to work (session.use_cookies = 1).
* Session lifetime needs to be longer than it takes a user to complete a Qualtrics survey for callbacks to work (session.cache_expire = value in minutes).

* The web server needs to be configured to allow POST requests on `/tool/public/index.php`.

## Setting up the Tool Provider (Qualtrics)

Qualtrics provides documentation for reading parameter values from the query string (a GET requests such as this tool provides):

[Qualtrics documentation on query string parameters](http://www.qualtrics.com/university/researchsuite/developer-tools/api-integration/passing-information-through-query-strings/)

For example, to retrieve the user id passed by the Tool Consumer in Qualtrics, an embedded data element can be added
to the survey flow with the field name `user_id`.

In order for grading callbacks to work, Qualtrics should read the `lis_result_sourcedid` from the query string and
pass it to this tool's endpoint after completing the survey together with an `custom_grade` parameter ranging from 0.0 to 1.0.
The latter can be done redirecting to `http://youruniversity.com/qualtrics-lti-bridge/tool/public/index.php?lis_result_sourcedid=${e://Field/lis_result_sourcedid}&custom_grade=1` on survey termination.
git 
## Setting up the Tool Provider (tool)

Once a survey has been created in Qualtrics, there are two ways to configure its use in the tool.

1. Open `/tool/Config.php` and specify the `custom_qualtrics_url` and `custom_survey_id` parameters to point to the survey.
Disallow url and id overrides to only allow this specific survey to be opened.
2. Open `/tool/Config.php` and set `custom_qualtrics_url` to the default url that you use for surveys.
`custom_survey_id` doesn't need to be set. Allow url and id overrides to allow Tool Consumers to specify custom custom_survey_url
and custom_survey_id parameters to the tool.

The second way is recommended as it allows for a variety of surveys to be handled with the tool. However,
it only works if your Tool Consumer supports sending additional parameters (Coursera does).

For each Tool Consumer or scenario (see external Coursera documentation above for some guidelines) a consumer key and secret
should be defined in the tool configuration. You can set these at random.

Another important setting concerns grading callbacks:

Though Qualtrics doesn't give us grading information, we can have it callback this tool after a survey
has been completed. It can then pass an identifier and grade that allows us to call the Tool Consumer
that was stored in a session variable. Though the communication between this tool and the consumer is OAuth
secured, the communication between Qualtrics and this tool is NOT. Therefore, grading may never be fully
trusted as anyone with basic knowledge of HTTP requests will be able to spoof his or her own grade.

To enable this type of grading callbacks, which can still be useful if you trust your users, set `provideGrading` to `true`.

## Setting up the Tool Consumer (Coursera)

When adding an LTI Item to a coursera lesson, a few things need to be set:

* The Launch URL should point to this tool's endpoint.
* The Consumer Key should be one defined in this tool's configuration.
* The Consumer Secret should match the secret for the key in this tool's configuration.
* Learner Privacy should optionally be changed such that various extra parameters will be passed to Qualtrics.
* Outcome Callback should be set to yes if you want grading callbacks.
* Depending on your tool configuration, at least two custom parameters need to be specified (for example):

1. `custom_qualtrics_url` = `https://youruniversity.qualtrics.com/SE`
2. `custom_survey_id` = `SV_7U4egQ3f78yO52B`
3. `custom_return_url` = `https://yourconsumer/return.php`

## Supported LTI operations

Currently supported LTI operations are:

* Launch requests passing all provided parameters to Qualtrics.
* Grading callbacks from Qualtrics to the Tool Consumer, provided a security concern (see above).

These are limited by the functionality that Qualtrics provides.
