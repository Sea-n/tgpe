<?php

function findSafeBrowsingThreats($url) {
	if (!defined('SAFE_BROWSING_API_KEY') || SAFE_BROWSING_API_KEY === '')
		return [];

	$payload = [
		'client' => [
			'clientId' => 'tg.pe',
			'clientVersion' => '1.0',
		],
		'threatInfo' => [
			'threatTypes' => [
				'SOCIAL_ENGINEERING',
				'MALWARE',
				'UNWANTED_SOFTWARE',
				'POTENTIALLY_HARMFUL_APPLICATION',
			],
			'platformTypes' => ['ANY_PLATFORM'],
			'threatEntryTypes' => ['URL'],
			'threatEntries' => [
				['url' => $url],
			],
		],
	];

	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=' . urlencode(SAFE_BROWSING_API_KEY),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => json_encode($payload),
		CURLOPT_HTTPHEADER => [
			'Content-Type: application/json',
		],
		CURLOPT_CONNECTTIMEOUT => 1,
		CURLOPT_TIMEOUT => 1,
	]);

	$response = curl_exec($curl);
	if ($response === false) {
		error_log('safe_browsing_lookup_failed: ' . curl_error($curl));
		curl_close($curl);
		return null;
	}

	$httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	if ($httpCode >= 400) {
		error_log("safe_browsing_lookup_failed: http_status={$httpCode}");
		return null;
	}

	$decoded = json_decode($response, true);
	if (!is_array($decoded)) {
		error_log('safe_browsing_lookup_failed: invalid_json_response');
		return null;
	}

	$matches = $decoded['matches'] ?? [];
	if (!is_array($matches) || count($matches) === 0)
		return [];

	$threatTypes = [];
	foreach ($matches as $match) {
		if (!empty($match['threatType']))
			$threatTypes[] = $match['threatType'];
	}
	return array_values(array_unique($threatTypes));
}

function formatSafeBrowsingReasons($threatTypes) {
	$typeMap = [
		'SOCIAL_ENGINEERING' => 'phishing/social engineering',
		'MALWARE' => 'malware',
		'UNWANTED_SOFTWARE' => 'unwanted software',
		'POTENTIALLY_HARMFUL_APPLICATION' => 'potentially harmful application',
	];

	$reasons = [];
	foreach ($threatTypes as $threatType)
		$reasons[] = $typeMap[$threatType] ?? strtolower(str_replace('_', ' ', $threatType));

	return array_values(array_unique($reasons));
}
