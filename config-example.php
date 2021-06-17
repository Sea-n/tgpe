<?php
define('TG_ADMINS', [
	109780439  # Sean
]);

define('HTTP_API_TOKENS', [
	'guest' => 'guest',
	'Base64TokenOrAnySecretStringOnlyMeKnows' => 'admin'
]);

define('ABUSEIPDB_KEY', '');

$tg_blacklist = [
	777000,
];

$domain_blacklist = [
	'tg.pe',
];

$ipv4_blacklist = [
	['54.39.0.0',   '54.39.255.255'  ],  # AS16276 (OVH)
	['157.230.0.0', '157.230.255.255'],  # AS14061 (DigitalOcean)
	['157.245.0.0', '157.245.255.255'],  # AS14061 (DigitalOcean)
	['206.189.0.0', '206.189.255.255'],  # AS14061 (DigitalOcean)
];
