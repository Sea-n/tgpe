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

$asn_blacklist = [
	'AS14061' => 'DigitalOcean',
	'AS16276' => 'OVH',
];

$domain_blacklist = [
	'cf',
	'ga',
	'gq',
	'ml',
	'tk',

	'tg.pe',
];
