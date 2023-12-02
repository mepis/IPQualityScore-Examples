<?php

// #############################
// Configs
// #############################

$endpoint_URL = 'http://192.168.0.59/testValidationForward.php'; // The URL where the form info should be sent to if all info passes IPQS validation
$redirection_URL = 'http://192.168.0.59/failedValidation.php'; // Where the person should be redirected if data does not pass validation
$phone_form_name = 'phone_home'; // form name for phone entry
$email_form_name = 'email_address'; // form name for email entry

$origin_url = "http://this.website.com"; // The website address this script is being used with

$enable_phone_validation = false; //change to false to not perform phone checks
$enable_email_validation = false; //change to false to not perform email checks
$enable_ip_validation = true; //change to false to not perform ip checks

$ipqs_api_key = ""; // enter IPQS API key between quotes
$fraud_threshold = 90; // max fraud score without evaluating other data points
$secondary_fraud_threshold = 85; // max fraud score when considering other data points

// IP validation settings
// Ignore if disabled with setting above
$ip_strictness = 1; // Value of 0, 1, 2, or 3 (Value of 0 or 1 recommended)
$fast = false; // Set to true to enabled faster lookups at the cost of some accuracy
$mobile = false; // Set to true to penalize mobile IPs less
$allow_public_access_points = true; // Set to true to penalize public access points less
$lighter_penalties = false; // Set to true to penalize mixed quality IPs less
$is_geo_restricted = false; // set to false if you do not want to geo-restrict by IP location, edit $allowed_country_codes below to whitelist countries
$is_bot_restricted = true; // set to false if you do not want to block IPs with the bot flag tripped, blocks if secondary fraud threshold is met
$is_vpn_restricted = true; // set to false if you do not want to block IPs with the VPN flag tripped, blocks if secondary fraud threshold is met
$is_proxy_restricted = true; // set to false if you do not want to block IPs with the proxy flag tripped, blocks if secondary fraud threshold is met
$is_tor_restricted = true; // set to false if you do not want to block IPs with the TOR flag tripped, blocks if secondary fraud threshold is met

// Email validation settings
// Ignore if disabled with setting above
$fast = false; // use cached data
$timeout = 5; //sets the timeout per email check in seconds (does not set the timeout for the entire API call)
$email_strictness = 1; // Value of 0, 1, 2, or 3 (Value of 0 or 1 recommended)
$allow_disposable_email = true;
$allow_spam_trap = true;

// Phone validation settings
// Ignore if disabled with setting above
$allow_risky_numbers = true;
$allow_spammer_numbers = true;

// geo-restricted country codes
// add country codes for the countries that you want to allow
$allowed_country_codes = array(
    "US",
);

// automatically allow any IPs from this list
$ip_white_list = array(
    "192.168.1.1",
);

// automatically block any IPs on this list
$ip_blacklist_list = array(
    "192.168.1.2",
);

$display_results = false; // DO NOT ENABLE UNLESS TESTING, this will prevent redirection and display IPQS results if set to 'true'

// #############################
// End Configs
// #############################

$ip = getIP();
$email = $_POST[$email_form_name];
$phone = $_POST[$phone_form_name];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

$settings = [
    'is_geo_restricted' => $is_geo_restricted,
    'is_bot_restricted' => $is_bot_restricted,
    'is_vpn_restricted' => $is_vpn_restricted,
    'is_proxy_restricted' => $is_proxy_restricted,
    'is_tor_restricted' => $is_tor_restricted,
    'is_geo_restricted' => $is_geo_restricted,
    'secondary_fraud_threshold' => $secondary_fraud_threshold,
    'allow_risky_numbers' => $allow_risky_numbers,
    'allow_spammer_numbers' => $allow_spammer_numbers,
    'allow_disposable_email' => $allow_disposable_email,
    'allow_spam_trap' => $allow_spam_trap,
    'fraud_threshold' => $fraud_threshold,
];

if (isWhiteListed($ip, $ip_white_list)) {
    forwardFormData($endpoint_URL, $display_results);
}

if (isBlacklisted($ip, $ip_blacklist_list)) {
    redirect($redirection_URL, $display_results);
}

$ip_parameters = array(
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    "strictness" => $ip_strictness,
    "fast" => $fast,
    "mobile" => $mobile,
    "allow_public_access_points" => $allow_public_access_points,
    "lighter_penalties" => $lighter_penalties,
    "websiteURL" => $origin_url,
);
$ip_formatted_parameters = http_build_query($ip_parameters);

$email_parameters = array(
    "strictness" => $email_strictness,
    "timeout" => $timeout,
    "fast" => $fast,
);
$email_formatted_parameters = http_build_query($email_parameters);

$ipURL = "https://ipqualityscore.com/api/json/ip/{$ipqs_api_key}/{$ip}?{$ip_formatted_parameters}";
$emailURL = "https://ipqualityscore.com/api/json/email/{$ipqs_api_key}/{$email}?{$email_formatted_parameters}";
$phoneURL = "https://ipqualityscore.com/api/json/phone/{$ipqs_api_key}/{$phone}";

if ($enable_ip_validation) {
    $ipResults = callIPQS($ipURL);
}
if ($enable_email_validation) {
    $emailResults = callIPQS($emailURL);
}
if ($enable_phone_validation) {
    $phoneResults = callIPQS($phoneURL);
}

if ($display_results) {
    if ($enable_ip_validation) {
        echo $ip . "\n";
        foreach ($ipResults as $key => $value) {
            echo "Field " . htmlspecialchars($key) . " is " . htmlspecialchars($value) . "<br>";
        }
    }
    if ($enable_phone_validation) {
        foreach ($phoneResults as $key => $value) {
            echo "Field " . htmlspecialchars($key) . " is " . htmlspecialchars($value) . "<br>";
        }
    }
    if ($enable_email_validation) {
        foreach ($emailResults as $key => $value) {
            echo "Field " . htmlspecialchars($key) . " is " . htmlspecialchars($value) . "<br>";
        }
    }
}

if ($enable_phone_validation && $enable_email_validation && $enable_ip_validation) {
    if (does_phone_pass($emailResults, $settings) && does_email_pass($phoneResults, $settings) && does_IP_pass($ipResults, $allowed_country_codes, $settings)) {
        forwardFormData($endpoint_URL, $display_results);
    } else {
        redirect($redirection_URL, $display_results);
    }
}

if ($enable_email_validation && $enable_ip_validation && !$enable_phone_validation) {
    if (does_email_pass($phoneResults, $settings) && does_IP_pass($ipResults, $allowed_country_codes, $settings)) {
        forwardFormData($endpoint_URL, $display_results);
    } else {
        redirect($redirection_URL, $display_results);
    }
}

if ($enable_phone_validation && $enable_email_validation && !$enable_ip_validation) {
    if (does_phone_pass($emailResults, $settings) && does_email_pass($phoneResults, $settings)) {
        forwardFormData($endpoint_URL, $display_results);
    } else {
        redirect($redirection_URL, $display_results);
    }
}

if ($enable_phone_validation && $enable_ip_validation && !$enable_email_validation) {
    if (does_phone_pass($emailResults, $settings) && does_IP_pass($ipResults, $allowed_country_codes, $settings)) {
        forwardFormData($endpoint_URL, $display_results);
    } else {
        redirect($redirection_URL, $display_results);
    }
}

if ($enable_ip_validation && !$enable_email_validation && !$enable_phone_validation) {
    if (does_IP_pass($ipResults, $allowed_country_codes, $settings)) {
        forwardFormData($endpoint_URL, $display_results);
    } else {
        redirect($redirection_URL, $display_results);
    }
}

if ($enable_email_validation && !$enable_phone_validation && !$enable_ip_validation) {
    if (does_email_pass($phoneResults, $settings)) {
        forwardFormData($endpoint_URL, $display_results);
    } else {
        redirect($redirection_URL, $display_results);
    }
}

if ($enable_phone_validation && !$enable_email_validation && !$enable_ip_validation) {
    if (does_phone_pass($emailResults, $settings)) {
        forwardFormData($endpoint_URL, $display_results);
    } else {
        redirect($redirection_URL, $display_results);
    }
}

function does_IP_pass($ipResults, $allowed_country_codes, $settings)
{
    $fraud_threshold = $settings['fraud_threshold'];
    $is_geo_restricted = $settings['is_geo_restricted'];
    $secondary_fraud_threshold = $settings['secondary_fraud_threshold'];
    $is_bot_restricted = $settings['is_bot_restricted'];
    $is_vpn_restricted = $settings['is_vpn_restricted'];
    $is_proxy_restricted = $settings['is_proxy_restricted'];
    $is_tor_restricted = $settings['is_tor_restricted'];


    $doesPass = true;
    if ($ipResults->fraud_score >= $fraud_threshold) {
        $doesPass = false;
    }
    if ($is_geo_restricted) {
        if (!in_array($ipResults->country_code, $allowed_country_codes)) {
            $doesPass = false;
        }
    }
    if ($ipResults->operating_system == "N/A") {
        $doesPass = false;
    }

    if ($ipResults->fraud_score >= $secondary_fraud_threshold) {
        if ($is_bot_restricted) {
            if ($ipResults->bot_status) {
                $doesPass = false;
            }
        }
        if ($is_vpn_restricted) {
            if ($ipResults->vpn) {
                $doesPass = false;
            }
        }
        if ($is_proxy_restricted) {
            if ($ipResults->proxy) {
                $doesPass = false;
            }
        }
        if ($is_tor_restricted) {
            if ($ipResults->tor) {
                $doesPass = false;
            }
        }
    }
    return $doesPass;
}

function does_phone_pass($phoneResults, $settings)
{
    $doesPass = true;
    $fraud_threshold = $settings['fraud_threshold'];
    $allow_risky_numbers = $settings['allow_risky_numbers'];
    $allow_spammer_numbers = $settings['allow_spammer_numbers'];
    if ($phoneResults->fraud_score >= $fraud_threshold) {
        $doesPass = false;
    }
    if (!$phoneResults->valid) {
        $doesPass = false;
    }
    if (!$phoneResults->active) {
        $doesPass = false;
    }
    if (!$allow_risky_numbers) {
        if ($phoneResults->risky) {
            $doesPass = false;
        }
    }
    if (!$allow_spammer_numbers) {
        if ($phoneResults->spammer) {
            $doesPass = false;
        }
    }
    return $doesPass;
}

function does_email_pass($emailResults, $settings)
{
    $doesPass = true;
    $fraud_threshold = $settings['fraud_threshold'];
    $allow_disposable_email = $settings['allow_disposable_email'];
    $allow_spam_trap = $settings['allow_spam_trap'];
    $secondary_fraud_threshold = $settings['secondary_fraud_threshold'];
    if ($emailResults->fraud_score >= $fraud_threshold) {
        $doesPass = false;
    }
    if (!$emailResults->valid) {
        $doesPass = false;
    }
    if (!$allow_disposable_email) {
        if ($emailResults->disposable) {
            $doesPass = false;
        }
    }
    if (!$allow_spam_trap) {
        if ($emailResults->spam_trap_score === "high" || $emailResults->spam_trap_score === "medium") {
            $doesPass = false;
        }
    }
    if ($emailResults->recent_abuse && $emailResults->fraud_score >= $secondary_fraud_threshold) {
        $doesPass = false;
    }
    return $doesPass;
}

function isWhiteListed($ip, $ip_white_list)
{
    if (in_array($ip, $ip_white_list)) {
        return true;
    }
}

function isBlacklisted($ip, $ip_blacklist_list)
{
    if (in_array($ip, $ip_blacklist_list)) {
        return true;
    }
}

function redirect($redirect_link, $display_results)
{
    if (!$display_results) {
        header("Location: " . $redirect_link);
    }
}

function forwardFormData($endpoint_URL, $display_results)
{
    if (!$display_results) {
        $url = $endpoint_URL;
        $fields = [];
        foreach ($_POST as $key => $value) {
            $fields[$key] = $value;
        }
        $fields_string = http_build_query($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        redirect($result, $display_results);
    }
}

function getIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function callIPQS($url)
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
