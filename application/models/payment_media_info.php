<?php
class Payment_Media_Info extends Dao_Impl {
	private $q;
	private $media_name;
	private $media_bank;
	public function __construct($fields = array()) {
		parent::__construct();
		if (!empty($fields)) {
			foreach ($this as $key => $value) {
				if ($fields[$key] !== NULL) {
					$this->$key = $fields[$key];
				}
			}
		}
	}

	public static function get_media_list($media_name = NULL) {
		$s = '';
		$medias = self::getInstance();
		$medias = $medias['name'];
		if ($medias !== NULL) {
			foreach ($medias as $media) {
				$s .= '<option value="' . $media . '" '
						. ($media_name !== NULL && $media_name === $media ? 'selected'
								: '') . '>' . $media . '</option>';
			}
		}
		return $s;
	}

	public static function get_bank_list($media_name, $bankname = NULL) {
		$s = '';
		$banks = self::getInstance();
		$banks = $banks['bank'][$media_name];
		if ($banks !== NULL) {
			foreach ($banks as $bank) {
				$s .= '<option value="' . $bank . '" '
						. ($bankname !== NULL && $bankname === $bank ? 'selected'
								: '') . '>' . $bank . '</option>';
			}
		}
		return $s;
	}

	public static function get_bank_acount_list($media_name, $bank_name,
			$account_name = NULL) {
		$s = '';
		$accounts = self::getInstance();
		$accounts = $accounts['account'][$media_name][$bank_name];
		if ($accounts !== NULL) {
			foreach ($accounts as $account) {
				$s .= '<option value="' . $account . '" '
						. ($account_name !== NULL && $account_name === $account ? 'selected'
								: '') . '>' . $account . '</option>';
			}
		}
		return $s;
	}

	public static function getInstance($force_flush = FALSE) {
		$payment_media_cache_filename = md5('payment_media_cache_filename');
		$payment_media_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$payment_media_cache_file = $payment_media_cache
				->get($payment_media_cache_filename);

		if ($force_flush || $payment_media_cache_file === FALSE) {
			//读取数据库
			$dao = new Dao_Impl();
			$medias = $dao->db
					->get_results(
							'SELECT media_name,account_bank,account FROM finance_payment_media_info WHERE isok=1');
			if ($medias !== NULL) {
				$datas = array();
				foreach ($medias as $media) {
					if (!in_array($media->media_name, $datas['name'])) {
						$datas['name'][] = $media->media_name;
					}
					if (!in_array($media->account_bank,
									$datas['bank'][$media->media_name])) {
						$datas['bank'][$media->media_name][] = $media
								->account_bank;
					}
					if (!in_array($media->account,
									$datas['account'][$media->media_name][$media
											->account_bank])) {
						$datas['account'][$media->media_name][$media
								->account_bank][] = $media->account;
					}
				}
				$payment_media_cache
						->set($payment_media_cache_filename, $datas);
			}
		}
		return $payment_media_cache->get($payment_media_cache_filename);
	}

	public function get_media_info($type = 'bank') {
		$s = '';
		if (in_array($type, array('bank', 'account'), TRUE)) {
			$medias = self::getInstance();
			$datas = $type === 'bank' ? $medias['bank'][$this->media_name]
					: $medias['account'][$this->media_name][$this->media_bank];
			if ($datas !== NULL) {
				$s .= implode(',', $datas);
			}
		}
		return $s;
	}
}
