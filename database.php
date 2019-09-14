<?php
class MyDB {
	public $pdo;

	public function __construct() {
		$this->pdo = new PDO('sqlite:/usr/share/nginx/tg.pe/sqlite.db');
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
}
