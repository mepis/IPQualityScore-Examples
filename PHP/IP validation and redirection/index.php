<?php
// ###############################
// Start Configs
// ###############################

// Must Configure
$key = '';
$redirect_url = '';
// Default good settings, can be changed as needed
$fraud_score_threshold = 85;
$strictness = 1;
$allow_public_access_points = 'true';
$lighter_penalties = 'false';

// ###############################
// End Configs
// ###############################

$user_agent = $_SERVER['HTTP_USER_AGENT'];
$user_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];
$parameters = array(
	'user_agent' => $user_agent,
	'user_language' => $user_language,
	'strictness' => $strictness,
	'allow_public_access_points' => $allow_public_access_points,
	'lighter_penalties' => $lighter_penalties
);
$formatted_parameters = http_build_query($parameters);
$url = sprintf(
	'https://www.ipqualityscore.com/api/json/ip/%s/%s?%s',
	$key,
	$ip,
	$formatted_parameters
);
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
$json = curl_exec($curl);
curl_close($curl);
$result = json_decode($json, true);
if (isset($result['success']) && $result['success'] === true) {
	if (
		$result['fraud_score'] >= $fraud_score_threshold &&
		(($result['proxy'] === true && $result['is_crawler'] === false) ||
			$result['vpn'] === true ||
			$result['bot_status'] === true ||
			$result['tor'] === true
		)
	) {
		$url = "Location: " . $redirect_url;
		exit(header($url));
	}
}
