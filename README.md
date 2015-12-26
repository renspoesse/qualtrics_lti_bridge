This Qualtrics LTI Bridge tool was developed by Rens Poesse at the University of Amsterdam.

It has been based on qualtrics_lti_bridge by Simon Wiles at Stanford University,
which is accessible at the link [tiny.cc/qualtricslti](http://tiny.cc/qualtricslti).

The tool currently follows the LTI 1.1 standard; this is documented as the LTI specification Coursera supports.
For the same reason OAuth 1.0a is used, in [Andy Smith's implementation](http://oauth.googlecode.com/svn/code/php/).

## Project goals and information

The main goal for developing this tool was to build a way by which Qualtrics surveys can be included
in a Coursera course for the purpose of A/B testing. However, the tool has more general LTI use cases
as can be read here:

* [Coursera documentation for using LTI](https://tech.coursera.org/app-platform/lti)
* [General information about LTI](https://www.imsglobal.org/activity/learning-tools-interoperability)

Another important goal of this project is to provide clear documentation and code comments to ensure
usefulness of the tool for anyone interested in using Qualtrics with other platforms in educational scenarios.

## Getting started

This section provides a short guide on how to get started using the Qualtrics LTI Bridge tool.

First, a quick introduction on the mechanisms at work. Learning Tools Interoperability (LTI) can be seen as an
interface contract for different learning tools to communicate. This means that LTI is not a piece of software but
a definition that states how learning tools should interact.

In our main use case those learning tools are Coursera and Qualtrics. Qualtrics supports A/B testing, but Coursera
does not. So we want to integrate Qualtrics with Coursera. Contrarily however, Coursera supports LTI integration but
Qualtrics does not (yet). This is where the Qualtrics LTI Bridge tool comes in. In LTI terms, our situation is as follows:

* Tool Consumer: Coursera (but can be anything that supports LTI)
* Tool Provider: Qualtrics LTI Bridge tool (this project) as a wrapper around Qualtrics

When the Tool Consumer wants to make an LTI defined request to Qualtrics, it needs to address this tool that in turn
addresses Qualtrics and returns a valid LTI response. The tool therefore needs to be accessible to the Tool Consumer,
which means that it should run at a publicly accessible endpoint (e.g., youruniversity.com/qualtrics-lti-bridge/tool).

So, to get started:

* Download a copy of all files in the `/tool` folder. The tool is written in PHP and therefore should be hosted on
a server that supports PHP execution. `/tool/public/index.php` is the default entry point for the tool and most PHP servers are
configured to automatically serve this file as the tool endpoint is requested (i.e., youruniversity.com/qualtrics-lti-bridge/tool
should point to youruniversity.com/qualtrics-lti-bridge/tool/public/index.php).
* Once the tool has been set up correctly, a target questionnaire should be made in Qualtrics. In the case of A/B testing,
a single survey should be created that contains several paths (A and B). Please note down the url and Id of the created survey.
* KORTE OMSCHRIJVING VAN PROCES IN TOOL
* KORTE OMSCHRIJVING VAN PROCES IN COURSERA

## Tool dependencies

TODO: PHP etc DEPENDENCIES

Session cookies need to be enabled (use_cookies = 1) for callbacks to work
Session lifetime determines how long a survey might take
POST must be enabled on public pages

## Setting up the Tool Provider (Qualtrics)

## Setting up the Tool Provider (tool)

* TODO: AUTHENTICATION DOCUMENTEN
* TODO: DEFAULT URL SETTING VOOR QUALTRICS: youruniversity.qualtrics.com bijv. Deze kan dan worden overriden via custom parameters in Coursera.

## Setting up the Tool Consumer (Coursera)

## Supported LTI operations

Currently supported LTI operations are:

* TODO

These are limited by the functionality that Qualtrics provides.

TODO: IETS ZEGGEN OVER QUERY PARAMETERSIN QUALTRICS

TODO: CALLBACK KAN GEIMPLEMENTEERD WORDEN VIA BIJV EEN SESSIE ID DAT NAAR QUALTRICS GAAT EN TERUGKOMT NAAR DE TOOL,
MAAR DIT KAN MAKKELIJK GESPOOFD WORDEN, DUS GRADES ZIJN NOOIT BETROUWBAAR.