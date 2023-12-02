<?php

// ######################################################
// For Fluent Forms plugin only
// ######################################################

// Due to the nature of WordPress, phone validation needs to be performed per contact-form plugin. 
// This code snippet is written for Fluent Forms ( https://fluentforms.com/ )
// If you are using a different form plugin, re-write this script accordingly or reach out to
// ipqs support for further assistance. 

// Copy everything after this line. The '<?php' line should not be copied. Paste this snippet into the functions.php file in your theme. 

// Fluent Forms documentation: https://fluentforms.com/docs/fluentform_validate_input_item_input_text/

// ######################################################
// Start IPQS Phone Validation validation - start copying
// ######################################################

// function ipqs_phone_validation($fields, $entry, $form_data)
add_filter("is_email", "ipqs_validation", 10, 3);

function ipqs_validation($is_email, $email, $context)
{
    $validator = new ipqsValidater;
    // #############################
    // Configs
    // #############################

    $error_message = "Invalid Data";
    $validator->ipqs_api_key = "";
    // #############################
    // Configs
    // #############################
    $validator->email = $email;
    $did_pass = $validator->validate_data();
    if (!$did_pass) {
        $errorMessage = $error_message;
    }
    return [$errorMessage];
}


class ipqsValidater
{
    public $phone;
    public $email;

    // #############################
    // Configs
    // #############################

    public $enable_phone_validation = false; //change to false to not perform phone checks
    public $enable_email_validation = true; //change to false to not perform email checks
    public $enable_ip_validation = false; //change to false to not perform ip checks

    public $ipqs_api_key = ""; // enter IPQS API key between quotes
    public $fraud_threshold = 90; // max fraud score without evaluating other data points
    public $secondary_fraud_threshold = 85; // max fraud score when considering other data points

    // IP validation settings
    // Ignore if disabled with setting above
    public $ip_strictness = 1; // Value of 0, 1, 2, or 3 (Value of 0 or 1 recommended)
    public $fast = false; // Set to true to enabled faster lookups at the cost of some accuracy
    public $mobile = false; // Set to true to penalize mobile IPs less
    public $allow_public_access_points = true; // Set to true to penalize public access points less
    public $lighter_penalties = false; // Set to true to penalize mixed quality IPs less
    public $is_geo_restricted = false; // set to false if you do not want to geo-restrict by IP location, edit $allowed_country_codes below to whitelist countries
    public $is_bot_restricted = true; // set to false if you do not want to block IPs with the bot flag tripped, blocks if secondary fraud threshold is met
    public $is_vpn_restricted = true; // set to false if you do not want to block IPs with the VPN flag tripped, blocks if secondary fraud threshold is met
    public $is_proxy_restricted = true; // set to false if you do not want to block IPs with the proxy flag tripped, blocks if secondary fraud threshold is met
    public $is_tor_restricted = true; // set to false if you do not want to block IPs with the TOR flag tripped, blocks if secondary fraud threshold is met

    // Email validation settings
    // Ignore if disabled with setting above
    public $timeout = 5; //sets the timeout per email check in seconds (does not set the timeout for the entire API call)
    public $email_strictness = 1; // Value of 0, 1, 2, or 3 (Value of 0 or 1 recommended)
    public $allow_disposable_email = true;
    public $allow_spam_trap = true;

    // Phone validation settings
    // Ignore if disabled with setting above
    public $allow_risky_numbers = true;
    public $allow_spammer_numbers = true;

    // geo-restricted country codes
    // add country codes for the countries that you want to allow
    public $allowed_country_codes = array(
        "US",
    );

    // automatically allow any IPs from this list
    public $ip_white_list = array(
        "192.168.1.1",
    );

    // automatically block any IPs on this list
    public $ip_blacklist_list = array(
        "192.168.1.2",
    );


    // #############################
    // End Configs
    // #############################
    private $ip_results;
    private $email_results;
    private $phone_results;
    private $ip;

    function validate_data()
    {
        $this->ip = $this->get_ip();

        if ($this->is_white_listed()) {
            return true;
        }


        if ($this->is_blacklisted()) {
            return false;
        }

        $ip_parameters = array(
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            "strictness" => $this->ip_strictness,
            "fast" => $this->fast,
            "mobile" => $this->mobile,
            "allow_public_access_points" => $this->allow_public_access_points,
            "lighter_penalties" => $this->lighter_penalties,
            "contactFormIP" => $this->ip,
        );
        $ip_formatted_parameters = http_build_query($ip_parameters);

        $email_parameters = array(
            "strictness" => $this->email_strictness,
            "timeout" => $this->timeout,
            "fast" => $this->fast,
            "contactFormIP" => $this->ip,
        );
        $email_formatted_parameters = http_build_query($email_parameters);
        $phone_parameters = array(
            "contactFormIP" => $this->ip,
        );
        $phone_formatted_parameters = http_build_query($phone_parameters);

        $ipURL = "https://ipqualityscore.com/api/json/ip/{$this->ipqs_api_key}/{$this->ip}?{$ip_formatted_parameters}";
        $emailURL = "https://ipqualityscore.com/api/json/email/{$this->ipqs_api_key}/{$this->email}?{$email_formatted_parameters}";
        $phoneURL = "https://ipqualityscore.com/api/json/phone/{$this->ipqs_api_key}/{$this->phone}?{$phone_formatted_parameters}";

        if ($this->enable_ip_validation) {
            $this->ip_results = $this->call_ipqs($ipURL);
        }

        if ($this->enable_email_validation) {

            $this->email_results = $this->call_ipqs($emailURL);
        }
        if ($this->enable_phone_validation) {
            $this->phone_results = $this->call_ipqs($phoneURL);
        }

        if ($this->enable_phone_validation && $this->enable_email_validation && $this->enable_ip_validation) {
            if ($this->does_phone_pass() && $this->does_email_pass() && $this->does_IP_pass()) {
                return true;
            } else {
                return false;
            }
        }

        if ($this->enable_email_validation && $this->enable_ip_validation && !$this->enable_phone_validation) {
            if ($this->does_email_pass() && $this->does_IP_pass()) {
                return true;
            } else {
                return false;
            }
        }

        if ($this->enable_phone_validation && $this->enable_email_validation && !$this->enable_ip_validation) {
            if ($this->does_phone_pass() && $this->does_email_pass()) {
                return true;
            } else {
                return false;
            }
        }

        if ($this->enable_phone_validation && $this->enable_ip_validation && !$this->enable_email_validation) {
            if ($this->does_phone_pass() && $this->does_IP_pass()) {
                return true;
            } else {
                return false;
            }
        }

        if ($this->enable_ip_validation && !$this->enable_email_validation && !$this->enable_phone_validation) {
            if ($this->does_IP_pass()) {
                return true;
            } else {
                return false;
            }
        }

        if ($this->enable_email_validation && !$this->enable_phone_validation && !$this->enable_ip_validation) {
            if ($this->does_email_pass()) {
                return true;
            } else {
                return false;
            }
        }

        if ($this->enable_phone_validation && !$this->enable_email_validation && !$this->enable_ip_validation) {
            if ($this->does_phone_pass()) {
                return true;
            } else {
                return false;
            }
        }
    }

    private function does_IP_pass()
    {
        $doesPass = true;
        if ($this->ip_results->fraud_score >= $this->fraud_threshold) {
            $doesPass = false;
        }
        if ($this->is_geo_restricted) {
            if (!in_array($this->ip_results->country_code, $this->allowed_country_codes)) {
                $doesPass = false;
            }
        }
        if ($this->ip_results->operating_system == "N/A") {
            $doesPass = false;
        }

        if ($this->ip_results->fraud_score >= $this->secondary_fraud_threshold) {
            if ($this->is_bot_restricted) {
                if ($this->ip_results->bot_status) {
                    $doesPass = false;
                }
            }
            if ($this->is_vpn_restricted) {
                if ($this->ip_results->vpn) {
                    $doesPass = false;
                }
            }
            if ($this->is_proxy_restricted) {
                if ($this->ip_results->proxy) {
                    $doesPass = false;
                }
            }
            if ($this->is_tor_restricted) {
                if ($this->ip_results->tor) {
                    $doesPass = false;
                }
            }
        }
        return $doesPass;
    }

    private function does_phone_pass()
    {
        $doesPass = true;
        if ($this->phone_results->fraud_score >= $this->fraud_threshold) {
            $doesPass = false;
        }
        if (!$this->phone_results->valid) {
            $doesPass = false;
        }
        if (!$this->phone_results->active) {
            $doesPass = false;
        }
        if (!$this->allow_risky_numbers) {
            if ($this->phone_results->risky) {
                $doesPass = false;
            }
        }
        if (!$this->allow_spammer_numbers) {
            if ($this->phone_results->spammer) {
                $doesPass = false;
            }
        }
        return $doesPass;
    }

    private function does_email_pass()
    {

        $doesPass = true;

        if ($this->email_results->fraud_score >= $this->fraud_threshold) {
            $doesPass = false;
        }
        if (!$this->email_results->valid) {
            $doesPass = false;
        }
        if (!$this->allow_disposable_email) {
            if ($this->email_results->disposable) {
                $doesPass = false;
            }
        }
        if (!$this->allow_spam_trap) {
            if ($this->email_results->spam_trap_score === "high" || $this->email_results->spam_trap_score === "medium") {
                $doesPass = false;
            }
        }
        if ($this->email_results->recent_abuse && $this->email_results->fraud_score >= $this->secondary_fraud_threshold) {
            $doesPass = false;
        }
        return $doesPass;
    }

    private function is_white_listed()
    {
        if (in_array($this->ip, $this->ip_white_list)) {
            return true;
        }
    }

    private function is_blacklisted()
    {
        if (in_array($this->ip, $this->ip_blacklist_list)) {
            return true;
        }
    }

    private static function get_ip()
    {
        switch (true) {
            case (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && !empty($_SERVER['HTTP_CF_CONNECTING_IP']) && $_SERVER['HTTP_CF_CONNECTING_IP'] !== '0.0.0.0'):
                return $_SERVER['HTTP_CF_CONNECTING_IP'];
            case (isset($_SERVER['HTTP_IPQS_CONNECTING_IP']) && !empty($_SERVER['HTTP_IPQS_CONNECTING_IP']) && $_SERVER['HTTP_IPQS_CONNECTING_IP'] !== '0.0.0.0'):
                return $_SERVER['HTTP_IPQS_CONNECTING_IP'];
            case (isset($_SERVER['HTTP_X_REAL_IP']) && !empty($_SERVER['HTTP_X_REAL_IP']) && $_SERVER['HTTP_X_REAL_IP'] !== '0.0.0.0'):
                return $_SERVER['HTTP_X_REAL_IP'];
            case (isset($_SERVER['HTTP_X_PC_IP']) && !empty($_SERVER['HTTP_X_PC_IP']) && $_SERVER['HTTP_X_PC_IP'] !== '0.0.0.0'):
                return $_SERVER['HTTP_X_PC_IP'];
            case (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '0.0.0.0'):
                return $_SERVER['REMOTE_ADDR'];
            case (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] !== '0.0.0.0'):
                return $_SERVER['HTTP_CLIENT_IP'];
            case (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] !== '0.0.0.0'):
                return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
    }


    private function call_ipqs($url)
    {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        // curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $response = json_decode($response);
        curl_close($ch);

        return $response;
    }

    // #############################
    // End IPQS email validation - Stop copying
    // #############################
}
