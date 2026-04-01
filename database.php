<?php
class MyDB {
	public $pdo;

	public function __construct() {
		require_once('config.php');
		$this->pdo = new PDO(MYSQL_DSN, MYSQL_USER, MYSQL_PASS, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		]);
	}

	/* Return code (string) or false (bool) */
	public function findCodeByUrl(string $url) {
		/* Find existed one */
		$sql = "SELECT * FROM main WHERE url = :url AND deleted_at IS NULL";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':url' => $url
		]);

		/* Only return first result */
		if ($data = $stmt->fetch())
			return $data['code'];

		return false;
	}

	/* Return data or false */
	public function findByCode(string $code) {
		$sql = "SELECT * FROM main WHERE code = :code AND deleted_at IS NULL";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':code' => $code
		]);

		return $stmt->fetch();
	}

	/* Return data array, or empty array */
	public function findByAuthor(string $author) {
		$sql = "SELECT * FROM main WHERE author = :author AND deleted_at IS NULL";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':author' => $author
		]);

		$result = [];
		while ($data = $stmt->fetch())
			$result[] = $data;

		return $result;
	}

	/* Return an array: [normal, deleted] */
	public function getUserStatus(string $author) {
		$sql = "SELECT SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) AS not_deleted_count,
				       SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) AS deleted_count,
				       MIN(created_at) as earliest_date,
				       MAX(created_at) as latest_date
				FROM main WHERE author = :author";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':author' => $author
		]);

		return $stmt->fetch();
	}

	/* Find unused code */
	public function allocateCode(string $prefix = '', $minLen = 3): string {
		if (!preg_match('#^[A-Za-z0-9_-]{0,32}$#', $prefix))
			return false; // illegal prefix

		$base58 = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

		for ($len=$minLen; $len<=32; $len++) // Try length from min to 32
			for ($_=0; $_<10; $_++) { // Try 10 times
				$code = $prefix;
				while (strlen($code) < $len)
					$code .= $base58[rand(0, 57)];

				if (!$this->findByCode($code))
					return $code;
			}
	}

	/* Return error info or ['00000', null, null] on success */
	public function insertCode(string $code, string $url, string $author) {
		if (!preg_match('#^[A-Za-z0-9_-]{1,32}$#', $code))
			return ['SEAN', 0, 'illegal code'];

		if (!filter_var($url, FILTER_VALIDATE_URL))
			return ['SEAN', 0, 'illegal url'];

		$sql = "INSERT INTO main(code, url, author) VALUES (:code, :url, :author)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':code' => $code,
			':url' => $url,
			':author' => $author
		]);

		return $stmt->errorInfo();
	}

	/* Return banned date or false */
	public function isUserBanned(string $uid) {
		$sql = "SELECT * FROM banned_users WHERE uid = :uid";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':uid' => $uid
		]);

		if ($data = $stmt->fetch())
			return $data['created_at'];

		return false;
	}

	public function banUser(string $uid) {
		$sql = "INSERT IGNORE INTO banned_users(uid) VALUES (:uid)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':uid' => $uid
		]);

		$sql = "UPDATE main SET deleted_at = CURRENT_TIMESTAMP WHERE author = :uid AND deleted_at IS NULL";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':uid' => $uid
		]);

		return $stmt->rowCount();
	}

	public function unbanUser(string $uid) {
		$sql = "DELETE FROM banned_users WHERE uid = :uid";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':uid' => $uid
		]);

		return $stmt->rowCount();
	}

	public function incrementClickCount(string $code): void {
		$sql = "UPDATE main SET click_count = click_count + 1 WHERE code = :code";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([':code' => $code]);
	}

	public function updateLastSafetyCheckAt(string $code): void {
		$sql = "UPDATE main SET last_safety_check_at = CURRENT_TIMESTAMP(6) WHERE code = :code";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([':code' => $code]);
	}

	public function deleteBySafetyCheck(string $code): void {
		$sql = "UPDATE main SET last_safety_check_at = CURRENT_TIMESTAMP(6), deleted_at = CURRENT_TIMESTAMP(6) WHERE code = :code";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([':code' => $code]);
	}

	public function isDomainBlacklisted(string $domain): bool {
		$domain = strtolower($domain);
		$sql = "SELECT 1 FROM domain_blacklist WHERE :domain = domain OR :domain2 LIKE CONCAT('%.', domain) LIMIT 1";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([':domain' => $domain, ':domain2' => $domain]);
		return (bool) $stmt->fetch();
	}

	public function isDomainWarnlisted(string $domain): bool {
		$domain = strtolower($domain);
		$sql = "SELECT 1 FROM domain_warnlist WHERE :domain = domain OR :domain2 LIKE CONCAT('%.', domain) LIMIT 1";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([':domain' => $domain, ':domain2' => $domain]);
		return (bool) $stmt->fetch();
	}

	public function addToBlacklist(string $domain) {
		$sql = "INSERT INTO domain_blacklist(domain) VALUES (:domain)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':domain' => $domain
		]);

		return $stmt->errorInfo();
	}

	public function addToWarnlist(string $domain) {
		$sql = "INSERT INTO domain_warnlist(domain) VALUES (:domain)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':domain' => $domain
		]);

		return $stmt->errorInfo();
	}
}
