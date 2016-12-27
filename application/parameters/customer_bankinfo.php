<?php
class Customer_Bankinfo extends User {
	public static function getInstance($force_flush = FALSE) {
		$customer_bankinfo_cache_filename = md5(
				'customer_bankinfo_cache_filename');
		$customer_bankinfo_cache = new FileCache(CACHE_TIME, CACHE_PATH);
		$customer_bankinfo_cache_file = $customer_bankinfo_cache
				->get($customer_bankinfo_cache_filename);

		if ($force_flush || $customer_bankinfo_cache_file === FALSE) {
			//读取数据库
			$dao = new Dao_Impl();
			$banks = $dao->db
					->get_results(
							'SELECT id,customer_name,bank_name,bank_account FROM finance_customer_bankinfo');
			if ($banks !== NULL) {
				$datas = array();
				foreach ($banks as $bank) {
					if (!in_array($bank->customer_name,
							$datas['customer_name'], TRUE)) {
						$datas['customer_name'][] = $bank->customer_name;
					}

					if (!in_array($bank->bank_name,
							$datas['bank'][$bank->customer_name], TRUE)) {
						$datas['bank'][$bank->customer_name][] = $bank
								->bank_name;
					}

					if (!in_array($bank->bank_account,
							$datas['account'][$bank->customer_name][$bank
									->bank_name], TRUE)) {
						$datas['account'][$bank->customer_name][$bank
								->bank_name][] = $bank->bank_account;
					}
				}
				$customer_bankinfo_cache
						->set($customer_bankinfo_cache_filename, $datas);
			}
		}
		return $customer_bankinfo_cache->get($customer_bankinfo_cache_filename);
	}

	public static function get_customer_list($customer = NULL) {
		$s = '';
		$customerbanks = self::getInstance();
		$customerbanks = $customerbanks['customer_name'];
		if ($customerbanks !== NULL) {
			foreach ($customerbanks as $customerbank) {
				$s .= '<option value="' . $customerbank . '" '
						. ($customer !== NULL && $customer === $customerbank ? 'selected'
								: '') . '>' . $customerbank . '</option>';
			}
		}
		return $s;
	}

	public static function get_bank_list($customer, $bankname = NULL) {
		$s = '';
		$customerbanks = self::getInstance();
		$customerbanks = $customerbanks['bank'][$customer];
		if ($customerbanks !== NULL) {
			foreach ($customerbanks as $customerbank) {
				$s .= '<option value="' . $customerbank . '" '
						. ($bankname !== NULL && $bankname === $customerbank ? 'selected'
								: '') . '>' . $customerbank . '</option>';
			}
		}
		return $s;
	}

	public static function get_bank_acount_list($customer, $bank_name,
			$account_name = NULL) {
		$s = '';
		$customerbanks = self::getInstance();
		$customerbanks = $customerbanks['account'][$customer][$bank_name];
		if ($customerbanks !== NULL) {
			foreach ($customerbanks as $customerbank) {
				$s .= '<option value="' . $customerbank . '" '
						. ($account_name !== NULL
								&& $account_name === $customerbank ? 'selected'
								: '') . '>' . $customerbank . '</option>';
			}
		}
		return $s;
	}
}
