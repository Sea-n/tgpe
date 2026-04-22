<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Report Abuse - tg.pe</title>
	<link rel="icon" type="image/png" href="/logo.png">
	<link rel="stylesheet" href="style.css" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta name="description" content="Report malicious or abusive short links on tg.pe.">
	<meta property="og:title" content="Report Abuse - tg.pe">
	<meta property="og:url" content="https://tg.pe/abuse">
	<meta property="og:image" content="/logo.png">
	<meta property="og:image:secure_url" content="/logo.png">
	<meta property="og:image:type" content="image/png">
	<meta property="og:type" content="website">
	<meta property="og:description" content="Report malicious or abusive short links on tg.pe.">
	<style>
		.abuse-wrap {
			width: min(760px, calc(100% - 28px));
			margin: 24px auto 0;
			padding: 0 0 8px;
		}

		.notice-zh {
			margin: 0;
			padding: 14px 16px;
			border-radius: 12px;
			background: linear-gradient(120deg, #0088cc, #37b9ef);
			color: #ffffff;
			font-size: 0.95rem;
			line-height: 1.5;
		}

		.abuse-card {
			margin-top: 16px;
			padding: 24px 20px;
			background: #ffffff;
			border: 1px solid #d7e3ee;
			border-radius: 14px;
			box-shadow: 0 10px 24px rgba(8, 44, 71, 0.08);
			text-align: left;
		}

		.abuse-card h3 {
			margin: 0;
			font-size: 1.8rem;
			font-weight: 700;
			color: #1f2f3f;
		}

		.abuse-card p {
			margin: 14px 0 0;
			line-height: 1.7;
			color: #3e556d;
		}

		.abuse-mail {
			display: inline-block;
			margin-top: 16px;
			font-size: 1.25rem;
			font-weight: 700;
			color: #0088cc;
			text-decoration: none;
		}

		.abuse-mail:hover {
			color: #006fa7;
		}

		@media (max-width: 640px) {
			.abuse-wrap {
				margin-top: 16px;
			}

			.abuse-card {
				padding: 20px 16px;
			}

			.abuse-card h3 {
				font-size: 1.5rem;
			}
		}
	</style>
</head>
<body>
<center>
<div class="content">
	<a href="/" aria-label="Back to homepage">
		<img src="logo_boderless.png" style="height: 24vh; margin-top: 40px;" alt="tg.pe logo">
	</a>
	<h1>URL Shortener</h1>
	<h2>Abuse Reporting</h2>

	<div class="abuse-wrap">
		<p class="notice-zh">我們非常重視每一則惡意濫用通報，系統已主動追蹤與過濾；若有漏網之魚，歡迎來信。</p>
		<section class="abuse-card">
			<h3>Report Abuse</h3>
			<p>We take abuse and malicious activity on tg.pe very seriously. Our system actively tracks and filters risky links, but if anything slips through, please report it by email.</p>
			<a class="abuse-mail" href="mailto:abuse@tg.pe">abuse@tg.pe</a>
			<p>We will review every valid report and remove abusive links within 12 hours, then block the related user and domain.</p>
		</section>
	</div>
	<br>
</div>
<div class="footer">
	<footer id="footer">
		<p>Source Code: <a href="https://github.com/Sea-n/tgpe">Sea-n/tgpe</a>
		| Developed by <a href="https://sean.cat/about">Sean</a>.</p>
	</footer>
</div>
</center>
</body>
</html>
