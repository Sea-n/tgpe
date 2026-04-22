<?php
if (!isset($code) || !isset($data))
	exit('Invalid call');

$url = $data['url'];

$h = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex,nosnippet">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Share: tg.pe/<?= $h($code) ?></title>
<link rel="icon" type="image/png" href="/logo.png">
<script>
(function () {
	try {
		var t = localStorage.getItem('theme');
		if (t === 'light' || t === 'dark') document.documentElement.classList.add('theme-' + t);
	} catch (e) {}
})();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600&family=Roboto+Mono:wght@400&display=swap">
<style>
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; height: 100%; }

:root {
	color-scheme: light dark;
	--bg:             light-dark(#EBF5FB, #0f1520);
	--surface:        light-dark(#fff,    #1a2233);
	--surface-2:      light-dark(#eef3f8, #243147);
	--surface-hover:  light-dark(#e6f4fb, #243147);
	--text:           light-dark(#222,    #e4e8ef);
	--text-muted:     light-dark(#6b7786, #8a95a5);
	--text-label:     light-dark(#555,    #9fb0c0);
	--border:         light-dark(#dbe4ec, #2a3446);
	--accent:         light-dark(#0088CC, #4da8e6);
	--accent-hover:   light-dark(#007ab8, #6ab8ee);
	--accent-btn-text:light-dark(#fff,    #0f1520);
	--input-bg:       light-dark(#f7fafc, #252f42);
	--input-border:   light-dark(#cfd7df, #3a4558);
	--original:       light-dark(#4a5568, #a8b5c8);
	--big-text:       light-dark(#0f1419, #f0f4fa);
	--scheme-dim:     light-dark(#a4aebe, #6a7888);
	--track:          light-dark(#c8d2dc, #3a4558);
	--shadow:         light-dark(rgba(0,0,0,.08), rgba(0,0,0,.4));
	--title-color:    #77B55A;  /* same hue in both themes (user preference) */
	--mono: 'Roboto Mono', ui-monospace, 'SF Mono', Menlo, Monaco, 'Courier New', monospace;
	--sans: 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;
	--ease: cubic-bezier(.4,0,.2,1);
	--dur: .32s;
	--theme-dur: .3s;
}
:root.theme-light { color-scheme: light; }
:root.theme-dark  { color-scheme: dark; }

html {
	background: var(--bg);
	transition: background-color var(--theme-dur) ease;
}
body {
	font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
	background: var(--bg);
	color: var(--text);
	display: flex;
	min-height: 100vh;
	line-height: 1.4;
	transition: background-color var(--theme-dur) ease, color var(--theme-dur) ease;
}

/* ---------- Sidebar ---------- */
#sidebar {
	width: 280px;
	flex-shrink: 0;
	background: var(--surface);
	border-right: 1px solid var(--border);
	overflow: hidden;
	display: flex;
	flex-direction: column;
	transition:
		width var(--dur) var(--ease),
		border-right-width var(--dur),
		background-color var(--theme-dur) ease,
		border-color var(--theme-dur) ease;
}
#sidebar-inner {
	width: 280px;
	flex: 1;
	padding: 16px 16px 72px;
	display: flex;
	flex-direction: column;
	gap: 16px;
}
body.sidebar-collapsed #sidebar { width: 0; border-right-width: 0; }

/* Header now only holds the collapse button (logo lives in #top-logo overlay).
   40px height keeps spacing parity with the floating logo at top: 16px. */
.sidebar-header {
	display: flex;
	align-items: center;
	justify-content: flex-end;
	min-height: 40px;
}

.icon-btn {
	background: none;
	border: none;
	cursor: pointer;
	color: var(--text-label);
	padding: 6px;
	border-radius: 6px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	line-height: 0;
	transition: background-color var(--theme-dur) ease, color var(--theme-dur) ease;
}
.icon-btn:hover { background: var(--surface-hover); color: var(--text); }
.icon-btn svg { width: 22px; height: 22px; }

/* ---------- Sliding mode toggle ---------- */
.seg-slide {
	position: relative;
	display: grid;
	grid-template-columns: 1fr 1fr;
	background: var(--surface-2);
	border-radius: 10px;
	padding: 4px;
	isolation: isolate;
	transition: background-color var(--theme-dur) ease;
}
.seg-slide::before {
	content: '';
	position: absolute;
	top: 4px;
	bottom: 4px;
	left: 4px;
	width: calc(50% - 4px);
	background: var(--accent);
	border-radius: 8px;
	box-shadow: 0 1px 4px var(--shadow);
	transition: transform .25s var(--ease), background-color var(--theme-dur) ease;
	z-index: -1;
}
.seg-slide[data-mode="big"]::before { transform: translateX(100%); }
.seg-slide button {
	font: inherit;
	font-size: 13.5px;
	padding: 8px 6px;
	border: none;
	background: transparent;
	color: var(--text-label);
	cursor: pointer;
	font-weight: 500;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 6px;
	transition: color var(--theme-dur) ease;
}
.seg-slide button svg { width: 16px; height: 16px; stroke-width: 2; }
.seg-slide button.active { color: var(--accent-btn-text); font-weight: 600; }

/* ---------- Labels / inputs / link rows ---------- */
.label {
	font-size: 13px;
	color: var(--text-muted);
	margin-bottom: 4px;
	font-weight: 500;
	transition: color var(--theme-dur) ease;
}
.label.clickable { cursor: pointer; user-select: none; }
.label.clickable:hover { color: var(--text); }

.input-row input {
	width: 100%;
	font: inherit;
	font-size: 14px;
	padding: 8px 10px;
	border: 1px solid var(--input-border);
	border-radius: 6px;
	background: var(--input-bg);
	color: var(--text);
	transition:
		background-color var(--theme-dur) ease,
		color var(--theme-dur) ease,
		border-color var(--theme-dur) ease;
}
.input-row input::placeholder { color: var(--text-muted); opacity: .7; }

.link-row { display: flex; gap: 6px; }
.link-row input {
	flex: 1;
	min-width: 0;
	font: inherit;
	font-size: 14px;
	padding: 8px 10px;
	border: 1px solid var(--input-border);
	border-radius: 6px;
	background: var(--input-bg);
	color: var(--text);
	cursor: pointer;
	transition:
		background-color var(--theme-dur) ease,
		color var(--theme-dur) ease,
		border-color var(--theme-dur) ease;
}
.link-row button {
	font: inherit;
	font-size: 14px;
	padding: 8px 12px;
	border: 1px solid var(--accent);
	background: var(--accent);
	color: var(--accent-btn-text);
	border-radius: 6px;
	cursor: pointer;
	white-space: nowrap;
	transition:
		background-color var(--theme-dur) ease,
		color var(--theme-dur) ease,
		border-color var(--theme-dur) ease;
}
.link-row button:hover { background: var(--accent-hover); border-color: var(--accent-hover); }

.original {
	font-size: 13px;
	line-height: 1.4;
	color: var(--original);
	word-break: break-all;
	display: block;
	max-height: 14em;
	overflow-y: auto;
	transition: color var(--theme-dur) ease;
}

.sidebar-footer {
	margin-top: auto;
	font-size: 12px;
	color: var(--text-muted);
	padding-top: 4px;
	transition: color var(--theme-dur) ease;
}
.sidebar-footer a { color: var(--accent); text-decoration: none; transition: color var(--theme-dur) ease; }
.sidebar-footer a:hover { color: var(--accent-hover); text-decoration: none; }

/* ---------- Floating buttons (desktop) ---------- */
.floating-btn {
	position: fixed;
	z-index: 10;
	width: 40px;
	height: 40px;
	background: var(--surface);
	border: 1px solid var(--border);
	border-radius: 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	box-shadow: 0 2px 8px var(--shadow);
	color: var(--text-label);
	padding: 0;
	transition:
		background-color var(--theme-dur) ease,
		color var(--theme-dur) ease,
		border-color var(--theme-dur) ease,
		box-shadow var(--theme-dur) ease;
}
.floating-btn:hover { color: var(--accent); border-color: var(--accent); }
.floating-btn svg { width: 22px; height: 22px; stroke-width: 2; }

/* Hamburger sits to the right of #top-logo when sidebar is collapsed */
#expand-btn      { display: none; top: 16px; left: 72px; }
body.sidebar-collapsed #expand-btn { display: flex; }
#fullscreen-btn  { bottom: 16px; left: 16px; }
#theme-btn       { bottom: 16px; left: 64px; }

#fullscreen-btn .icon-exit { display: none; }
#fullscreen-btn[data-fs="on"] .icon-enter { display: none; }
#fullscreen-btn[data-fs="on"] .icon-exit  { display: block; }

#theme-btn .icon-sun { display: none; }
#theme-btn[data-theme="dark"] .icon-sun { display: block; }
#theme-btn[data-theme="dark"] .icon-moon { display: none; }

/* ---------- Mobile top bar (hidden on desktop) ---------- */
#mobile-bar { display: none; }

/* ---------- Main area ---------- */
main {
	flex: 1;
	position: relative;
	overflow: hidden;
	cursor: pointer;
	user-select: none;
	-webkit-user-select: none;
}
/* Force pointer everywhere inside main — single click switches mode, double-click
   toggles fullscreen, so the entire surface is interactive. Also prevents child
   defaults (e.g. text I-beam on text content) from leaking through. */
main, main * { cursor: pointer; }

#stage {
	position: absolute;
	inset: 0;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 28px;
	padding: 24px;
}

.title-slot {
	font-family: var(--sans);
	font-size: clamp(22px, 4vh, 40px);
	font-weight: 600;
	color: var(--title-color);
	line-height: 1.2;
	min-height: 1.2em;
	max-width: 92%;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	text-align: center;
	flex-shrink: 0;
}

.qr-box {
	background: #fff;
	flex-shrink: 0;
	/* 28px stage gap - 4px pull = 24px visual gap from QR to URL text */
	margin-bottom: -4px;
	transition:
		width var(--dur) var(--ease),
		height var(--dur) var(--ease),
		padding var(--dur) var(--ease),
		border-radius var(--dur) var(--ease),
		box-shadow var(--dur) var(--ease),
		margin-bottom var(--dur) var(--ease);
}
.qr-box > div { width: 100%; height: 100%; }
.qr-box canvas, .qr-box img { width: 100%; height: 100%; max-width: 100%; max-height: 100%; }

#stage.mode-qr .qr-box {
	width: min(68vh, 72vw, 520px);
	height: min(68vh, 72vw, 520px);
	padding: 16px;
	border-radius: 12px;
	box-shadow: 0 2px 12px var(--shadow);
}
#stage.mode-big .qr-box {
	width: min(38vh, 40vw, 320px);
	height: min(38vh, 40vw, 320px);
	padding: 10px;
	border-radius: 10px;
	box-shadow: 0 2px 10px var(--shadow);
}

/* text-box fills remaining space, but text sits at TOP (flex-start) so URL
   hugs the QR at 24px; bottom empty space is acceptable (Big Text mode also
   benefits — the URL ends up nearer to stage center than the lower half) */
.text-box {
	flex: 1 1 auto;
	min-height: 0;
	width: 100%;
	display: flex;
	align-items: flex-start;
	justify-content: center;
	overflow: hidden;
	text-align: center;
}

#body-wrap {
	font-family: var(--mono);
	color: var(--big-text);
	font-size: 100px;
	font-weight: normal;
	letter-spacing: 2px;
	line-height: 1;
	white-space: nowrap;
	display: inline-block;
	transform: scale(1);
	/* Scale grows downward from the top so text never gets clipped above
	   when text-box uses align-items: flex-start */
	transform-origin: center top;
	will-change: transform;
	transition: transform var(--dur) var(--ease), color var(--theme-dur) ease;
}
#stage.mode-qr #body-wrap {
	transform: scale(calc(clamp(28px, 5.5vh, 56px) / 100px));
	letter-spacing: 1px;
	transition:
		transform var(--dur) var(--ease),
		letter-spacing var(--dur) var(--ease),
		color var(--theme-dur) ease;
}

.scheme-prefix {
	color: var(--scheme-dim);
	font-family: var(--sans);
	font-weight: 400;
	font-size: 0.5em;
	letter-spacing: normal;
	display: inline-block;
	overflow: hidden;
	vertical-align: baseline;
	max-width: 12ch;
	opacity: 1;
	/* Same duration as max-width so scheme fades in sync with mode change */
	transition:
		max-width var(--dur) var(--ease),
		opacity var(--dur) var(--ease),
		color var(--theme-dur) ease;
}
#stage.hide-scheme .scheme-prefix { max-width: 0; opacity: 0; }

/* ---------- Top-left logo (always visible — the sidebar's interior no longer
   carries its own logo, so this single fixed-position element looks the same
   whether the sidebar is open, collapsed, or in fullscreen) ---------- */
#top-logo {
	display: block;
	position: fixed;
	top: 16px;
	left: 16px;
	z-index: 10;
	text-decoration: none;
	line-height: 0;
}
#top-logo img { height: 40px; display: block; }

/* ---------- Double-click hint (both modes, hidden in fullscreen) ---------- */
.fs-hint {
	position: absolute;
	bottom: 48px;
	left: 0;
	right: 0;
	text-align: center;
	font-size: 16px;
	color: var(--text-muted);
	letter-spacing: .3px;
	pointer-events: none;
	transition: color var(--theme-dur) ease;
}

/* ---------- Fullscreen ----------
   Split into separate rules per pseudo-class: CSS drops the entire rule if any
   selector in a comma-list is unrecognised, and Firefox doesn't know
   :-webkit-full-screen (and vice versa). body.is-fullscreen is the reliable
   JS-driven fallback. */
:fullscreen { background: var(--bg); }
:-webkit-full-screen { background: var(--bg); }

:fullscreen #sidebar,
:fullscreen #expand-btn,
:fullscreen #theme-btn,
:fullscreen #mobile-bar,
:fullscreen .fs-hint { display: none !important; }

:-webkit-full-screen #sidebar,
:-webkit-full-screen #expand-btn,
:-webkit-full-screen #theme-btn,
:-webkit-full-screen #mobile-bar,
:-webkit-full-screen .fs-hint { display: none !important; }

body.is-fullscreen #sidebar,
body.is-fullscreen #expand-btn,
body.is-fullscreen #theme-btn,
body.is-fullscreen #mobile-bar,
body.is-fullscreen .fs-hint { display: none !important; }

/* #top-logo is always block — no fullscreen-specific override needed */

/* ---------- Mobile (<=720px): QR + URL + top bar (logo / Copy / Done) ---------- */
@media (max-width: 720px) {
	body { flex-direction: column; }

	/* Hide desktop chrome */
	#sidebar, #expand-btn, #fullscreen-btn, #theme-btn, #top-logo { display: none !important; }
	.title-slot, .scheme-prefix, .fs-hint { display: none !important; }

	/* Mobile top bar */
	#mobile-bar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
		padding: 10px 14px;
		background: var(--surface);
		border-bottom: 1px solid var(--border);
		flex-shrink: 0;
		transition:
			background-color var(--theme-dur) ease,
			border-color var(--theme-dur) ease;
	}
	.mobile-logo { display: block; line-height: 0; }
	.mobile-logo img { height: 34px; display: block; }
	.mobile-actions { display: flex; gap: 8px; align-items: center; }
	.mobile-btn {
		font: inherit;
		font-size: 14px;
		padding: 8px 16px;
		border-radius: 8px;
		cursor: pointer;
		text-decoration: none;
		white-space: nowrap;
		line-height: 1.2;
		border: 1px solid transparent;
		transition:
			background-color var(--theme-dur) ease,
			color var(--theme-dur) ease,
			border-color var(--theme-dur) ease;
	}
	.mobile-btn-primary {
		background: var(--accent);
		color: var(--accent-btn-text);
		border-color: var(--accent);
	}
	.mobile-btn-ghost {
		background: transparent;
		color: var(--text);
		border-color: var(--border);
	}

	/* Main + QR sizing */
	main { flex: 1; }
	#stage { gap: 12px; padding: 16px; }
	#stage.mode-qr .qr-box {
		width: min(92vw, 65vh);
		height: min(92vw, 65vh);
		margin-bottom: 0;
	}
	#stage.mode-big .qr-box {
		width: min(80vw, 45vh);
		height: min(80vw, 45vh);
		margin-bottom: 0;
	}
}
</style>
</head>
<body>

<header id="mobile-bar">
	<a href="/" class="mobile-logo"><img src="/logo.png" alt="tg.pe"></a>
	<div class="mobile-actions">
		<button id="mobile-copy-btn" class="mobile-btn mobile-btn-primary">Copy</button>
	</div>
</header>

<aside id="sidebar">
	<div id="sidebar-inner">
		<div class="sidebar-header">
			<button class="icon-btn" id="collapse-btn" aria-label="Collapse sidebar" title="Collapse">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="15 18 9 12 15 6"></polyline>
				</svg>
			</button>
		</div>

		<div class="seg-slide" id="mode-seg" data-mode="qr" role="tablist">
			<button class="mode-btn active" data-mode="qr" aria-label="QR code mode">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
					<rect x="3" y="3" width="7" height="7" rx="1"></rect>
					<rect x="14" y="3" width="7" height="7" rx="1"></rect>
					<rect x="3" y="14" width="7" height="7" rx="1"></rect>
					<path d="M14 14h3v3M20 14v3M14 20h3M20 20v0.01"></path>
				</svg>
				QR code
			</button>
			<button class="mode-btn" data-mode="big" aria-label="Big Text mode">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="4 7 4 4 20 4 20 7"></polyline>
					<line x1="9" y1="20" x2="15" y2="20"></line>
					<line x1="12" y1="4" x2="12" y2="20"></line>
				</svg>
				Big Text
			</button>
		</div>

		<div>
			<div class="label clickable" id="short-label">Short Link</div>
			<div class="link-row">
				<input id="short-link" value="https://tg.pe/<?= $h($code) ?>" readonly>
				<button id="copy-btn" data-state="idle">Copy</button>
			</div>
		</div>

		<div>
			<div class="label">Original URL</div>
			<div class="original"><?= $h($url) ?></div>
		</div>

		<div>
			<div class="label">Title</div>
			<div class="input-row">
				<input type="text" id="title-input" placeholder="Add a title for your audience…" autocomplete="off">
			</div>
		</div>

		<div class="sidebar-footer">
			Powered by <a href="https://sean.cat/about" target="_blank" rel="noopener">Sean Wei</a>.
		</div>
	</div>
</aside>

<button id="expand-btn" class="floating-btn" aria-label="Open sidebar" title="Open sidebar">
	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
		<line x1="3" y1="6" x2="21" y2="6"></line>
		<line x1="3" y1="12" x2="21" y2="12"></line>
		<line x1="3" y1="18" x2="21" y2="18"></line>
	</svg>
</button>

<button id="fullscreen-btn" class="floating-btn" data-fs="off" aria-label="Fullscreen" title="Fullscreen">
	<svg class="icon-enter" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
		<polyline points="15 3 21 3 21 9"></polyline>
		<polyline points="9 21 3 21 3 15"></polyline>
		<line x1="21" y1="3" x2="14" y2="10"></line>
		<line x1="3" y1="21" x2="10" y2="14"></line>
	</svg>
	<svg class="icon-exit" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
		<polyline points="4 14 10 14 10 20"></polyline>
		<polyline points="20 10 14 10 14 4"></polyline>
		<line x1="14" y1="10" x2="21" y2="3"></line>
		<line x1="3" y1="21" x2="10" y2="14"></line>
	</svg>
</button>

<button id="theme-btn" class="floating-btn" aria-label="Toggle theme" title="Toggle theme">
	<svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
		<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
	</svg>
	<svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
		<circle cx="12" cy="12" r="4"></circle>
		<path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path>
	</svg>
</button>

<a id="top-logo" href="/" title="tg.pe"><img src="/logo.png" alt="tg.pe"></a>

<main id="main">
	<div id="stage" class="mode-qr">
		<div class="title-slot" id="title-slot"><span id="title-text"></span></div>
		<div class="qr-box"><div id="qr-inner"></div></div>
		<div class="text-box">
			<span id="body-wrap"><span class="scheme-prefix">https://</span><span class="short-code">tg.pe/<?= $h($code) ?></span></span>
		</div>
	</div>
	<div class="fs-hint">Double-click anywhere to go fullscreen</div>
</main>

<script src="/qrcode.min.js"></script>
<script>
(function () {
	var CODE = <?= json_encode($code) ?>;
	var FULL_URL = 'https://tg.pe/' + CODE;

	var mode = 'qr';
	var hideScheme = false;
	var rafId = null;

	new QRCode(document.getElementById('qr-inner'), {
		text: FULL_URL, width: 1024, height: 1024, correctLevel: QRCode.CorrectLevel.M
	});
	// QRCode lib auto-sets title=URL on its container — drop it so the browser
	// doesn't show a distracting tooltip on hover (the user's Title stays visible).
	document.getElementById('qr-inner').removeAttribute('title');

	var stageEl       = document.getElementById('stage');
	var textBox       = document.querySelector('.text-box');
	var bodyWrap      = document.getElementById('body-wrap');
	var copyBtn       = document.getElementById('copy-btn');
	var shortInput    = document.getElementById('short-link');
	var shortLabel    = document.getElementById('short-label');
	var modeSeg       = document.getElementById('mode-seg');
	var mainEl        = document.getElementById('main');
	var themeBtn      = document.getElementById('theme-btn');
	var fsBtn         = document.getElementById('fullscreen-btn');
	var titleInput    = document.getElementById('title-input');
	var titleText     = document.getElementById('title-text');
	var mobileCopyBtn = document.getElementById('mobile-copy-btn');

	// --------- Scale fit via transform ---------
	function applyScale() {
		if (mode !== 'big') return;
		var w  = textBox.clientWidth;
		var h  = textBox.clientHeight;
		var sw = bodyWrap.scrollWidth;
		var sh = bodyWrap.scrollHeight;
		if (sw <= 0 || w <= 0) return;
		var scale = Math.min(w / sw, h / sh, 20);
		bodyWrap.style.transform = 'scale(' + scale + ')';
	}
	function runAnim(duration) {
		if (rafId) cancelAnimationFrame(rafId);
		if (mode !== 'big') return;
		bodyWrap.style.transition = 'none';
		var start = performance.now();
		function loop() {
			applyScale();
			if (performance.now() - start < duration) {
				rafId = requestAnimationFrame(loop);
			} else {
				bodyWrap.style.transition = '';
				rafId = null;
			}
		}
		rafId = requestAnimationFrame(loop);
	}
	function stopAnim() {
		if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
		bodyWrap.style.transition = '';
	}

	// --------- Mode switch ---------
	// For qr→big: class toggle alone would briefly let the base `transform: scale(1)`
	// take effect between paint and the first rAF, causing a two-stage animation.
	// We disable the transform transition, toggle classes, and set the target scale
	// synchronously so the first paint lands on the right value. The rAF then tracks
	// the container-size transition without CSS transition fighting.
	function setMode(next) {
		if (mode === next) return;

		if (next === 'big') bodyWrap.style.transition = 'none';
		mode = next;
		stageEl.classList.toggle('mode-qr',  next === 'qr');
		stageEl.classList.toggle('mode-big', next === 'big');
		modeSeg.dataset.mode = next;
		document.querySelectorAll('.mode-btn').forEach(function (b) {
			b.classList.toggle('active', b.dataset.mode === next);
		});
		hideScheme = (next === 'big');
		stageEl.classList.toggle('hide-scheme', hideScheme);

		if (next === 'big') {
			applyScale();     // snap to target scale synchronously (no brief scale(1))
			runAnim(440);     // continue tracking while qr-box / text-box finish transitioning
		} else {
			stopAnim();       // clears inline transition so stylesheet transition takes over
			bodyWrap.style.transform = '';   // let mode-qr CSS rule animate down to caption size
		}
	}
	document.querySelectorAll('.mode-btn').forEach(function (b) {
		b.addEventListener('click', function (e) { e.stopPropagation(); setMode(b.dataset.mode); });
	});

	// --------- Sidebar collapse/expand ---------
	function setCollapsed(v) {
		document.body.classList.toggle('sidebar-collapsed', v);
		if (mode === 'big') runAnim(440);
	}
	document.getElementById('collapse-btn').addEventListener('click', function (e) {
		e.stopPropagation(); setCollapsed(true);
	});
	document.getElementById('expand-btn').addEventListener('click', function (e) {
		e.stopPropagation(); setCollapsed(false);
	});

	// --------- Fullscreen ---------
	function enterFullscreen() {
		if (!document.fullscreenElement && document.documentElement.requestFullscreen)
			document.documentElement.requestFullscreen();
	}
	function toggleFullscreen() {
		if (document.fullscreenElement) {
			if (document.exitFullscreen) document.exitFullscreen();
		} else {
			enterFullscreen();
		}
	}
	fsBtn.addEventListener('click', function (e) { e.stopPropagation(); toggleFullscreen(); });

	// Main area: single click switches mode; double-click anywhere enters fullscreen.
	// Hold the single-click action briefly so a dblclick can cancel it — otherwise
	// the first click of a dblclick would flip the mode before going fullscreen.
	var pendingClickTimer = null;
	mainEl.addEventListener('click', function (e) {
		if (pendingClickTimer) { clearTimeout(pendingClickTimer); pendingClickTimer = null; }
		var target = e.target;
		pendingClickTimer = setTimeout(function () {
			pendingClickTimer = null;
			if (target.closest('.qr-box'))   { setMode('qr');  return; }
			if (target.closest('.text-box')) { setMode('big'); return; }
			/* blank: no-op */
		}, 240);
	});
	mainEl.addEventListener('dblclick', function (e) {
		e.preventDefault();
		if (pendingClickTimer) { clearTimeout(pendingClickTimer); pendingClickTimer = null; }
		toggleFullscreen();                 // dblclick toggles: enters if not in FS, exits if in FS
	});

	document.addEventListener('fullscreenchange', function () {
		var fs = !!document.fullscreenElement;
		fsBtn.dataset.fs = fs ? 'on' : 'off';
		fsBtn.setAttribute('aria-label', fs ? 'Exit fullscreen' : 'Fullscreen');
		fsBtn.setAttribute('title',      fs ? 'Exit fullscreen' : 'Fullscreen');
		document.body.classList.toggle('is-fullscreen', fs);  // reliable hook for CSS when :fullscreen is fussy
		setCollapsed(fs);
	});

	// --------- Theme toggle ---------
	function currentTheme() {
		if (document.documentElement.classList.contains('theme-dark')) return 'dark';
		if (document.documentElement.classList.contains('theme-light')) return 'light';
		return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
	}
	function applyTheme(t) {
		document.documentElement.classList.remove('theme-light', 'theme-dark');
		document.documentElement.classList.add('theme-' + t);
		try { localStorage.setItem('theme', t); } catch (e) {}
		themeBtn.dataset.theme = t;
	}
	themeBtn.dataset.theme = currentTheme();
	themeBtn.addEventListener('click', function (e) {
		e.stopPropagation();
		applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
	});
	if (window.matchMedia) {
		var mq = window.matchMedia('(prefers-color-scheme: dark)');
		var mqListener = function () {
			var hasExplicit = document.documentElement.classList.contains('theme-light') || document.documentElement.classList.contains('theme-dark');
			if (!hasExplicit) themeBtn.dataset.theme = mq.matches ? 'dark' : 'light';
		};
		if (mq.addEventListener) mq.addEventListener('change', mqListener);
		else if (mq.addListener) mq.addListener(mqListener);
	}

	// --------- Title input ---------
	titleInput.addEventListener('input', function () { titleText.textContent = titleInput.value; });
	titleInput.addEventListener('click', function (e) { e.stopPropagation(); });

	// --------- Copy helper (re-usable across feedback buttons) ---------
	function performCopy(feedbackBtn) {
		if (!feedbackBtn._origHTML) feedbackBtn._origHTML = feedbackBtn.innerHTML;
		var done = function () {
			feedbackBtn.dataset.state = 'copied';
			feedbackBtn.textContent = 'Copied!';
			clearTimeout(feedbackBtn._copyTimer);
			feedbackBtn._copyTimer = setTimeout(function () {
				feedbackBtn.dataset.state = 'idle';
				feedbackBtn.innerHTML = feedbackBtn._origHTML;
			}, 2000);
		};
		shortInput.focus();
		shortInput.select();
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(shortInput.value).then(done, function () {
				document.execCommand('copy'); done();
			});
		} else {
			document.execCommand('copy'); done();
		}
	}
	copyBtn    .addEventListener('click', function (e) { e.stopPropagation(); performCopy(copyBtn); });
	shortLabel .addEventListener('click', function (e) { e.stopPropagation(); performCopy(copyBtn); });
	shortInput .addEventListener('click', function (e) { e.stopPropagation(); performCopy(copyBtn); });
	shortInput .addEventListener('focus', function () { shortInput.select(); });
	if (mobileCopyBtn) {
		mobileCopyBtn.addEventListener('click', function (e) { e.stopPropagation(); performCopy(mobileCopyBtn); });
	}

	if (document.fonts && document.fonts.ready) {
		document.fonts.ready.then(function () { if (mode === 'big') applyScale(); });
	}

	var resizeRaf = null;
	window.addEventListener('resize', function () {
		if (resizeRaf) return;
		resizeRaf = requestAnimationFrame(function () {
			resizeRaf = null;
			if (mode !== 'big') return;
			bodyWrap.style.transition = 'none';
			applyScale();
			void bodyWrap.offsetWidth;
			bodyWrap.style.transition = '';
		});
	});
})();
</script>
</body>
</html>
