<?php

/**
 * A Trivial memory-based store - no support for tokens
 */
class ConsumerSecrets extends OAuthDataStore
{
    private $consumers = array();

    function add_consumer($consumer_key, $consumer_secret)
    {
        $this->consumers[$consumer_key] = $consumer_secret;
    }

    function lookup_consumer($consumer_key)
    {
        if (strpos($consumer_key, "http://") === 0) {
            $consumer = new OAuthConsumer($consumer_key, "secret", NULL);

            return $consumer;
        }
        if ($this->consumers[$consumer_key]) {
            $consumer = new OAuthConsumer($consumer_key, $this->consumers[$consumer_key], NULL);

            return $consumer;
        }

        return NULL;
    }

    function lookup_token($consumer, $token_type, $token)
    {
        return new OAuthToken($consumer, "");
    }

    // Return NULL if the nonce has not been used
    // Return $nonce if the nonce was previously used
    function lookup_nonce($consumer, $token, $nonce, $timestamp)
    {
        // Should add some clever logic to keep nonces from
        // being reused - for now we are really trusting
        // that the timestamp will save us
        return NULL;
    }

    function new_request_token($consumer)
    {
        return NULL;
    }

    function new_access_token($token, $consumer)
    {
        return NULL;
    }

    /*
    function lookup_consumer($consumer_key) {
        // implement me
    }

    function lookup_token($consumer, $token_type, $token) {
        // implement me
    }

    function lookup_nonce($consumer, $token, $nonce, $timestamp) {
        // implement me
    }

    function new_request_token($consumer) {
        // return a new token attached to this consumer
    }

    function new_access_token($token, $consumer) {
        // return a new access token attached to this consumer
        // for the user associated with this token if the request token
        // is authorized
        // should also invalidate the request token
    }
    */
}