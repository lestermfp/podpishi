<?php

//	https://gist.github.com/nalgeon/affa3f9fc7b665ab7744573455abe18d

	class TooManyRequests extends Exception { }

	class dadata {
		private $base_url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs';
		private $token;
		private $handle;

		function __construct() {
			global $_CFG;
			$this->token = $_CFG['dadata']['token'];

			$this->handle = curl_init();
			curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->handle, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: Token ' . $this->token
			]);
			curl_setopt($this->handle, CURLOPT_POST, 1);
		}

		/**
		 * See for details:
		 *   - https://dadata.ru/api/find-address/
		 *   - https://dadata.ru/api/find-bank/
		 *   - https://dadata.ru/api/find-fias/
		 *   - https://dadata.ru/api/find-party/
		 */
		public function findById($type, $fields) {
			$url = $this->base_url . '/findById/' . $type;
			return $this->executeRequest($url, $fields);
		}

		/**
		 * See https://dadata.ru/api/geolocate/ for details.
		 */
		public function geolocate($lat, $lon, $count = 10, $radius_meters = 100) {
			$url = $this->base_url . '/geolocate/address';
			$fields = [
				'lat' => $lat,
				'lon' => $lon,
				'count' => $count,
				'radius_meters' => $radius_meters,
                //'from_bound' => ['value' => 'street',],
                //'to_bound' => ['value' => 'street',],
			];
			return $this->executeRequest($url, $fields);
		}

		/**
		 * See https://dadata.ru/api/iplocate/ for details.
		 */
		public function iplocate($ip) {
			$url = $this->base_url . '/iplocate/address?ip=' . $ip;
			return $this->executeRequest($url, $fields = null);
		}

		/**
		 * See https://dadata.ru/api/suggest/ for details.
		 */
		public function suggest($type, $fields) {
			$url = $this->base_url . '/suggest/' . $type;
			return $this->executeRequest($url, $fields);
		}

		private function executeRequest($url, $fields) {
			curl_setopt($this->handle, CURLOPT_URL, $url);
			if ($fields != null) {
				curl_setopt($this->handle, CURLOPT_POST, 1);
				curl_setopt($this->handle, CURLOPT_POSTFIELDS, json_encode($fields));
			} else {
				curl_setopt($this->handle, CURLOPT_POST, 0);
			}
			$result = $this->exec();
			$result = json_decode($result, true);
			return $result;
		}

		private function exec() {
			$result = curl_exec($this->handle);
			$info = curl_getinfo($this->handle);
			if ($info['http_code'] == 429) {
				throw new TooManyRequests();
			} elseif ($info['http_code'] != 200) {
				throw new Exception('Request failed with http code ' . $info['http_code'] . ': ' . $result);
			}
			return $result;
		}

		public function __destruct() {
			curl_close($this->handle);
		}
	}
