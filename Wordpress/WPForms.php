<?php

// ######################################################
// For WPForms plugin only
// ######################################################

// Due to the nature of WordPress, phone validation needs to be performed per contact-form plugin. 
// This code snippet is written for WPForms ( https://wpforms.com/ )
// If you are using a different form plugin, re-write this script accordingly or reach out to
// ipqs support for further assistance. 

// Copy everything after this line. The '<?php' line should not be copied. Paste this snippet into the functions.php file in your theme. 

// WPForms documentation: https://wpforms.com/developers/wpforms_process/

// This validation script captures the email address, IP address of the person submitting the contact form information,
// and the website address this script is configured on. 
// https://www.ipqualityscore.com/user/settings#variables

// Make sure to add the following custom variables to your IPQS account to attach this information to each validation request:
// - contactFormIP
// - email
// - websiteURL

// IPQualityScore will be happy to help customize this script as needed with any paid subscription

// ######################################################
// Start IPQS Phone Validation validation - start copying
// ######################################################

add_action('wpforms_process', 'IPQS_validation_controller', 10, 3);

function IPQS_validation_controller($fields, $entry, $form_data)
{
    $ipqs_api_key = "";
    ipqs_validation_phone1($fields, $entry, $form_data, $ipqs_api_key);
    ipqs_validation_email1($fields, $entry, $form_data, $ipqs_api_key);
    ipqs_validation_ip1($fields, $entry, $form_data, $ipqs_api_key);
}

function ipqs_validation_phone1($fields, $entry, $form_data, $ipqs_api_key)
{
    $validator_phone1 = new ipqsValidater;
    // #############################
    // Configs
    // #############################

    if ( absint( $form_data[ 'id' ] ) === 2770 ||  absint( $form_data[ 'id' ] ) === 4218 ) {
        return $fields;
    }

    // Validation Configs
    $phone_form_id = 24;
    $email_form_id = 5;
    $phone_error_message = "Invalid Phone Number";

    // Script configs
    $validator_phone1->ipqs_api_key = $ipqs_api_key;
    $validator_phone1->enable_phone_validation = true;
    $validator_phone1->enable_email_validation = false;
    $validator_phone1->enable_ip_validation = false;
    // #############################
    // End Configs
    // #############################

    // Phone Validation
    $validator_phone1->phone = $fields[$phone_form_id]['value'];
    $validator_phone1->email = $fields[$email_form_id]['value'];
    $phone_did_pass = $validator_phone1->validate_data();
    if (!$phone_did_pass) {
        wpforms()->process->errors[$form_data['id']][$phone_form_id] = esc_html__($phone_error_message, 'plugin-domain');
    }
}

function ipqs_validation_email1($fields, $entry, $form_data, $ipqs_api_key)
{
    $validator_email1 = new ipqsValidater;
    // #############################
    // Configs
    // #############################

    // Validation Configs
    $phone_form_id = 24;
    $email_form_id = 5;
    $email_error_message = "Invalid Email Address";

    // Script configs
    $validator_email1->ipqs_api_key = $ipqs_api_key;
    $validator_email1->enable_phone_validation = false;
    $validator_email1->enable_email_validation = true;
    $validator_email1->enable_ip_validation = false;

    // Email Validation
    $validator_email1->email = $fields[$email_form_id]['value'];
    $validator_email1->phone = $fields[$phone_form_id]['value'];
    $email_did_pass = $validator_email1->validate_data();
    if (!$email_did_pass) {
        wpforms()->process->errors[$form_data['id']][$email_form_id] = esc_html__($email_error_message, 'plugin-domain');
    }
}

function ipqs_validation_ip1($fields, $entry, $form_data, $ipqs_api_key)
{
    $validator_ip1 = new ipqsValidater;
    // #############################
    // Configs
    // #############################

    // Validation Configs
    $email_form_id = 5;
    $phone_form_id = 24;
    $ip_error_message = "We cannot accept this information at this time.";

    // Script configs
    $validator_ip1->ipqs_api_key = $ipqs_api_key;
    $validator_ip1->enable_phone_validation = false;
    $validator_ip1->enable_email_validation = false;
    $validator_ip1->enable_ip_validation = true;

    // Email Validation
    $validator_ip1->email = $fields[$email_form_id]['value'];
    $validator_ip1->phone = $fields[$phone_form_id]['value'];
    $ip_did_pass = $validator_ip1->validate_data();
    if (!$ip_did_pass) {
        wpforms()->process->errors[$form_data['id']]['header'] = esc_html__($ip_error_messag, 'plugin-domain');
    }
}

class ipqsValidater
{
    public $phone;
    public $email;

    // #############################
    // Configs
    // #############################

    public $enable_phone_validation = false; //change to false to not perform phone checks
    public $enable_email_validation = false; //change to false to not perform email checks
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
    private $website;

    function validate_data()
    {
        $this->ip = $this->get_ip();
        $this->website = $this->getURL();

        if (isset($this->phone)) {
            $this->phone = str_replace("(", "+1",  $this->phone);
            $this->phone = str_replace(")", "",  $this->phone);
            $this->phone = str_replace("-", "",  $this->phone);
            $this->phone = str_replace(" ", "",  $this->phone);
        } else {
            $this->phone = "";
        }

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
            "websiteURL" => $this->website,
            "email" => $this->email,
        );
        $ip_formatted_parameters = http_build_query($ip_parameters);

        $email_parameters = array(
            "strictness" => $this->email_strictness,
            "timeout" => $this->timeout,
            "fast" => $this->fast,
            "contactFormIP" => $this->ip,
            "websiteURL" => $this->website,
            "email" => $this->email,
        );
        $email_formatted_parameters = http_build_query($email_parameters);
        $phone_parameters = array(
            "contactFormIP" => $this->ip,
            "websiteURL" => $this->website,
            "email" => $this->email,
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

    private function getURL()
    {
        $website = "";
        if (isset($_SERVER["HTTP_HOST"]) && isset($_SERVER["REQUEST_URI"])) {
            $website = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }
        return $website;
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
