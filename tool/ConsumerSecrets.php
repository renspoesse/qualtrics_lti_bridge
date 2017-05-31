<?php

namespace QualtricsLTIBridge;

/**
 * A "trivial" memory-based store that holds consumer credentials.
 *
 * OAuth tokens are not implemented.
 *
 * Requires: OAuth.php by Andy Smith.
 *
 * Coursera recommends the following for defining Tool Consumer credentials:
 *
 * As a Tool Provider, you are responsible for generating and storing a unique OAuth consumer key and secret for
 * each Tool Consumer accessing your learning tool. In the case of Coursera, each class session will likely be granted
 * a separate OAuth consumer key/secret. This process is described in more detail in section 4.1 of the LTI specification.
 *
 * In order to secure your final, production-ready learning tool, we recommend provisioning and storing a separate
 * consumer key/secret for each Tool Consumer. By doing so, you can easily invalidate individual Tool Consumers in
 * the event the key/secret are compromised and re-issue a new key without disrupting other access from other Tool
 * Consumer. Also, having separate keys/secrets for each consumer makes it harder for an attacker to compromise all
 * the credentials in use.
 *
 * Note: that if a consumer secret is compromised, an attacker could forge launch requests (or worse!) grading/outcome
 * requests to Coursera, which can effectively be used to impersonate a student and/or cheat on graded, LTI-based quizzes.
 */
class ConsumerSecrets extends \OAuthDataStore
{
    /**
     * Private array that holds the credentials. Defaults are defined in Config.php in the form of key => secret.
     */
    private $consumers = array();

    /**
     * Adds new or overwrites existing Tool Consumer credentials in the data store.
     *
     * @param string $consumer_key
     * @param string $consumer_secret
     */
    function set_consumer($consumer_key, $consumer_secret)
    {
        $this->consumers[$consumer_key] = $consumer_secret;
    }

    /**
     * Looks up the secret for the given consumer by key.
     * Returns null if the consumer wasn't found.
     *
     * @param $consumer_key
     *
     * @return null|\OAuthConsumer
     */
    function lookup_consumer($consumer_key)
    {
        // Check if the key exists in the array of consumers.

        if ($this->consumers[$consumer_key]) {

            // Return an OAuthConsumer object with the specifiek consumer key and secret.

            return new \OAuthConsumer($consumer_key, $this->consumers[$consumer_key], NULL);
        }

        // Consumer not found. Return null to indicate the error.

        return null;
    }

    /**
     * Looks up the consumer token.
     *
     * @param $consumer
     * @param $token_type
     * @param $token
     *
     * @return null
     */
    function lookup_token($consumer, $token_type, $token)
    {
        return new \OAuthToken($consumer, ""); // Tokens are not supported, but this method needs to return an object.
    }

    /**
     * Checks if the nonce has been used before.
     *
     * Returns NULL if the nonce has not been used.
     * Returns $nonce if the nonce was previously used.
     *
     * @param $consumer
     * @param $token
     * @param $nonce
     * @param $timestamp
     *
     * @return null
     */
    function lookup_nonce($consumer, $token, $nonce, $timestamp)
    {
        /*
         * Using nonces can be very costly for Service Providers as they demand persistent storage of all nonce
         * values received, ever. To make implementations easier, OAuth adds a timestamp value to each request
         * which allows the Service Provider to only keep nonce values for a limited time. When a request comes
         * in with a timestamp that is older than the retained time frame, it is rejected as the Service Provider
         * no longer has nonces from that time period. It is safe to assume that a request sent after the allowed
         * time limit is a replay attack.
         *
         * Information taken from: http://hueniverse.com/2008/10/03/beginners-guide-to-oauth-part-iii-security-architecture/
         */

        // Andy Smith's OAuth.php implements the timestamp check. The extra nonce check should be implemented here.
        // However, we need a database dependency to store all the nonces which might be too much for our use case.

        return null;
    }

    /**
     * Returns a new token attached to this consumer.
     *
     * @param $consumer
     * @param $callback
     *
     * @return null
     */
    function new_request_token($consumer, $callback = null)
    {
        return null; // Tokens are not supported.
    }

    /**
     * Returns a new access token attached to this consumer.
     *
     * @param $token
     * @param $consumer
     * @param $verifier
     *
     * @return null
     */
    function new_access_token($token, $consumer, $verifier = null)
    {
        return null; // Tokens are not supported.
    }
}