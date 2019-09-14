<?php
class MyDB {
	public $pdo;

	public function __construct() {
		$this->pdo = new PDO('sqlite:/usr/share/nginx/tg.pe/sqlite.db');
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/* Return code (string) or false (bool) */
	public function findCodeByUrl(string $url) {
		/* Find existed one */
		$sql = "SELECT * FROM main WHERE url = :url";
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
		$sql = "SELECT * FROM main WHERE code = :code";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':code' => $code
		]);

		return $stmt->fetch();
	}

	/* Return data array, or empty array */
	public function findByAuthor(string $author) {
		$sql = "SELECT * FROM main WHERE author = :author";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':author' => $author
		]);

		$result = [];
		while ($data = $stmt->fetch())
			$result[] = $data;

		return $result;
	}

	/* Find unused code */
	public function allocateCode(): string {
		$base58 = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
		for ($r=30; $r<100; $r++) { // Try length from 3 to 9, each try 10 times
			$code = '';
			for ($i=0; $i<($r/10); $i++)
				$code .= $base58[rand(0, 57)];

			if (!$this->findByCode($code))
				return $code;
		}
	}

	/* Return error info or ['00000', null, null] on success */
	public function insert(string $code, string $url, string $author) {
		$sql = "INSERT INTO main(code, url, author) VALUES (:code, :url, :author)";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([
			':code' => $code,
			':url' => $url,
			':author' => $author
		]);

		return $stmt->errorInfo();
	}
}
