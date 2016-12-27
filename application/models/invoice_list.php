<?php
class Invoice_List extends User {
	private $has_invoice_permission = FALSE;
	private $invoices = array ();
	private $d = 0;
	private $search;
	private $ismy = FALSE;
	public function getHas_invoice_permission() {
		return $this->has_invoice_permission;
	}
	public function getOn1() {
		return ! in_array ( intval ( $this->d ), array (
				2,
				3 
		), TRUE ) ? 'class="on"' : '';
	}
	public function getOn2() {
		return intval ( $this->d ) === 2 ? 'class="on"' : '';
	}
	public function getOn3() {
		return intval ( $this->d ) === 3 ? 'class="on"' : '';
	}
	public function __construct($fields, $ismy = FALSE) {
		parent::__construct ();
		foreach ( $this as $key => $value ) {
			if ($fields [$key] !== NULL && ! in_array ( $key, array (
					'has_invoice_permission',
					'ismy' 
			), TRUE )) {
				$this->$key = $fields [$key];
			}
		}
		
		if (in_array ( $this->getUsername (), $GLOBALS ['manager_finance_permission'], TRUE ) || intval ( $this->getBelong_dep () ) === 2) {
			$this->has_invoice_permission = TRUE;
		}
		$this->ismy = $ismy;
		$this->_get_invoice_data ();
	}
	private function _get_invoice_data($ismy = FALSE) {
		if ($this->ismy) {
			$where = 'WHERE user=' . $this->getUid ();
		} else {
			if (intval ( $this->d ) === 2) {
				$this_month_start = date ( 'Y-m-01', $_SERVER ['REQUEST_TIME'] );
				$this_month_end = date ( 'Y-m-d', strtotime ( $this_month_start . ' + 1 month -1 day' ) );
				$where = 'WHERE isok!=0 AND a.time>=' . strtotime ( $this_month_start . ' 00:00:00' ) . ' AND a.time<=' . strtotime ( $this_month_end . ' 23:59:59' );
			} else if (intval ( $this->d ) === 3) {
				$where = 'WHERE isok=0 AND print=1';
			} else {
				$where = 'WHERE isok=0 AND step=2 AND print=0';
			}
		}
		if ($this->search !== NULL && $this->search !== '' && intval ( $this->d ) === 2 && ! $this->ismy) {
			// 开票抬头或开票号码
			$where .= ' AND (a.title LIKE "%' . $this->search . '%" OR a.number LIKE "%' . $this->search . '%")';
		}
		$results = $this->db->get_results ( 'SELECT a.*,FROM_UNIXTIME(a.time) AS tt,b.username,b.realname,c.username AS gdusername,c.realname AS gdrealname FROM finance_invoice_list a LEFT JOIN users b ON a.user=b.uid LEFT JOIN users c ON a.gduser=c.uid ' . $where . ' ORDER BY a.time DESC' );
		$invoices = array ();
		if ($results !== NULL) {
			foreach ( $results as $result ) {
				$invoices [] = array (
						'id' => $result->id,
						'pids' => $result->pids,
						'amount' => $result->amount,
						'type' => $result->type,
						'company' => $result->company,
						'tt' => $result->tt,
						'user' => $result->realname . ' (' . $result->username . ')',
						'isok' => $result->isok,
						'print' => $result->print,
						'number' => $result->number,
						'd1' => $result->d1,
						'd2' => $result->d2,
						'd3' => $result->d3,
						'step' => $result->step,
						'auditmsg' => $result->auditmsg,
						'title' => $result->title,
						'gduser' => ($result->gdusername !== NULL && $result->gdrealname !== NULL ? ($result->gdrealname . ' (' . $result->gdusername . ')') : ''),
						'content' => $result->content,
						'remark' => $result->remark 
				);
			}
		}
		$this->invoices = $invoices;
	}
	public static function get_invoice_type($type, $isExport = FALSE) {
		if ($type === 1) {
			return '普票';
		}
		return $isExport ? '增票' : '<font color="#0000FF">增票</font>';
	}
	public static function _get_invoice_status($isok, $print, $step) {
		if ($isok === 0) {
			if ($step === 1) {
				return '<font color="#ff6600"><b>等待部门leader审核</b></font>';
			} else if ($step === 2) {
				if ($print === 0) {
					return '<font color="#ff6600"><b>等待财务部审核打印</b></font>';
				} else if ($print === 1) {
					return '<font color="#ff6600"><b>等待记录发票号码</b></font>';
				}
			}
		} else if ($isok === 1) {
			return '<font color="#66cc00"><b>生效</b></font>';
		} else {
			return '<font color="red"><b>驳回</b></font>';
		}
	}
	public static function get_my_invoice_action($id, $isok) {
		$s = '<a href="' . BASE_URL . 'finance/invoice/?o=myview&id=' . $id . '">查看</a>';
		if ($isok === - 1) {
			// 可修改
			$s .= '&nbsp;&nbsp;<a href="' . BASE_URL . 'finance/invoice/?o=myedit&id=' . $id . '">修改</a>';
		}
		return $s;
	}
	public static function get_invoice_action($id, $d, $print, $isok) {
		if ($d === 1 || $d === 3) {
			$s = '<a href="' . BASE_URL . 'finance/invoice/?o=audit&p=' . $print . '&id=' . $id . '">' . ($print === 1 ? '归档' : '审核') . '</a>';
			if ($d === 3) {
				$s .= '&nbsp;<a href="' . BASE_URL . 'finance/invoice/?o=print&id=' . $id . '" target="_blank">打印</a>&nbsp;<a href="javascript:simpleReject(\'' . $id . '\');">驳回</a>';
			}
			return $s;
		}
		$s = '<a href="' . BASE_URL . 'finance/invoice/?o=view&id=' . $id . '">查看</a>';
		if ($isok === 1) {
			$s .= '&nbsp;<a href="' . BASE_URL . 'finance/invoice/?o=print&id=' . $id . '" target="_blank">打印</a>&nbsp;<a href="' . BASE_URL . 'finance/invoice/?o=edit&id=' . $id . '">修改</a>';
		}
		return $s;
	}
	public static function get_invoice_type_dd($type, $d1, $d2, $d3) {
		$s = '';
		if ($type !== 1) {
			$s = sprintf ( "纳税人识别号： %s \n", $d1 );
			$s .= sprintf ( "地址、电话： %s \n", $d2 );
			$s .= sprintf ( "开户行及账号： %s", $d3 );
		}
		return $s;
	}
	private function _get_list_html() {
		$s = '';
		$invoices = $this->invoices;
		if (! empty ( $invoices )) {
			foreach ( $invoices as $key => $invoice ) {
				$s .= '<tr><td>' . ($key + 1) . '</td><td>' . $invoice ['tt'] . '</td><td>' . $invoice ['title'] . '</td><td><font color="#ff9933"><b>' . Format_Util::my_money_format ( '%.2n', $invoice ['amount'] ) . '</b></font></td><td ' . ($this->ismy ? 'title="' . self::get_invoice_type_dd ( intval ( $invoice ['type'] ), $invoice ['d1'], $invoice ['d2'], $invoice ['d3'] ) . '"' : '') . '>' . self::get_invoice_type ( intval ( $invoice ['type'] ) ) . '</td><td>' . $invoice ['company'] . '</td>';
				if ($this->ismy) {
					$s .= '<td>' . $invoice ['number'] . '</td><td>' . self::_get_invoice_status ( intval ( $invoice ['isok'] ), intval ( $invoice ['print'] ), intval ( $invoice ['step'] ) ) . '</td><td>' . $invoice ['auditmsg'] . '</td><td>' . self::get_my_invoice_action ( intval ( $invoice ['id'] ), intval ( $invoice ['isok'] ) ) . '</td></tr>';
				} else {
					$s .= '<td>' . $invoice ['user'] . '</td>' . (intval ( $this->d ) === 2 ? '<td>' . $invoice ['gduser'] . '</td>' : '') . '<td>' . self::_get_invoice_status ( intval ( $invoice ['isok'] ), intval ( $invoice ['print'] ), intval ( $invoice ['step'] ) ) . '</td><td>' . self::get_invoice_action ( intval ( $invoice ['id'] ), intval ( $this->d ), intval ( $invoice ['print'] ), intval ( $invoice ['isok'] ) ) . '</td></tr>';
				}
			}
		} else {
			$s = '<tr><td colspan="' . ($this->ismy || intval ( $this->d ) === 2 ? '10' : '9') . '"><font color="red">当前没有相应开票记录！</font></td></tr>';
		}
		unset ( $invoices );
		return $s;
	}
	private function _get_search_bar() {
		if (intval ( $this->d ) === 1) {
			// 可导出
			return '
			<form id="formID" method="post" action="' . BASE_URL . 'finance/action.php" target="post_frame">
			<table width="100%" class="tabin">
        	<tr>
            	<td>&nbsp;&nbsp;<input type="button" class="btn" value="导 出" id="expbtn"/><input type="hidden" name="action" value="exportUnAuditedInvoice"><input type="hidden" name="vcode" value="' . $this->get_vcode () . '"></td>
            </tr>
        	</table>
			</form>
    		<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>';
		} else if (intval ( $this->d ) === 2) {
			return '<table width="100%" class="tabin">
        	<tr>
            	<td>&nbsp;&nbsp;关键字：<input type="text" style="height:20px;" name="search" id="search" value="' . $this->search . '"/>&nbsp;&nbsp;<input type="button" class="btn" value="搜 索" id="searchbtn"/></td>
            </tr>
        </table>';
		}
		return '';
	}
	private static function _get_list_title($d) {
		$s = '<tr>
          <th width="50">编号</th>
          <th style="width:150px">申请时间</th>
          <th>开票抬头</th>
          <th>开票金额</th>
          <th>开票类型</th>
          <th>所属公司</th>
          <th>申请人</th>';
		if ($d === 2) {
			$s .= '<th>归档人</th>';
		}
		$s .= '<th>状态</th>
          <th>操作</th>
        </tr>';
		return $s;
	}
	public function get_invoice_list_html() {
		if ($this->getHas_invoice_permission ()) {
			$buf = file_get_contents ( TEMPLATE_PATH . 'finance/invoice/invoice_list.tpl' );
			return str_replace ( array (
					'[LEFT]',
					'[TOP]',
					'[VCODE]',
					'[INVOICELIST]',
					'[ON1]',
					'[ON2]',
					'[ON3]',
					'[SEARCHBAR]',
					'[LISTTITLE]',
					'[BASE_URL]' 
			), array (
					$this->get_left_html (),
					$this->get_top_html (),
					$this->get_vcode (),
					$this->_get_list_html (),
					$this->getOn1 (),
					$this->getOn2 (),
					$this->getOn3 (),
					$this->_get_search_bar (),
					self::_get_list_title ( intval ( $this->d ) ),
					BASE_URL 
			), $buf );
		} else {
			return User::no_permission ();
		}
	}
	public function get_invoice_mylist_html() {
		$buf = file_get_contents ( TEMPLATE_PATH . 'finance/invoice/invoice_mylist.tpl' );
		return str_replace ( array (
				'[LEFT]',
				'[TOP]',
				'[VCODE]',
				'[INVOICELIST]',
				'[INVOICETAB]',
				'[BASE_URL]' 
		), array (
				$this->get_left_html (),
				$this->get_top_html (),
				$this->get_vcode (),
				$this->_get_list_html (),
				$this->get_invoice_tab (),
				BASE_URL 
		), $buf );
	}
	private function _getInvoiceType($pids) {
		$s = '';
		if (! empty ( $pids )) {
			$pids = explode ( '|', $pids );
			$pids = reset ( $pids );
			
			$cid = explode ( '-', $pids );
			$cid = reset ( $cid );
			$billtype = $this->db->get_var ( 'SELECT billtype FROM contract_cus WHERE cid="' . $cid . '"' );
			switch (intval ( $billtype )) {
				case 1 :
					$s = '广告业';
					break;
				case 2 :
					$s = '服务业';
					break;
			}
		}
		return $s;
	}
	public function exportUnAuditedInvoice() {
		if ($this->has_invoice_permission) {
			$invoices = $this->invoices;
			
			if (! empty ( $invoices )) {
				// 开始生成excel
				$objPHPExcel = new PHPExcel ();
				PHPExcel_Settings::setCacheStorageMethod ( PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized );
				$objPHPExcel->setActiveSheetIndex ( 0 );
				$objPHPExcel->getActiveSheet ()->setTitle ( '待审核待打印发票' );
				
				$titles = array (
						'申请时间',
						'开票抬头',
						'开票金额',
						'发票类型',
						'开票类型',
						'',
						'开票内容',
						'备注',
						'申请人' 
				);
				
				// 标题
				foreach ( $titles as $k => $v ) {
					$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( $k, 1, $v );
				}
				
				foreach ( $invoices as $key => $value ) {
					$now_line = $key + 2;
					// 第一列 申请时间
					$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 0, $now_line, $value ['tt'] );
					
					// 第二列 开票抬头
					$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 1, $now_line, $value ['title'] );
					
					// 第三列 开票金额
					$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 2, $now_line, $value ['amount'] );
					
					// 第四列 发票类型
					$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 3, $now_line, $this->_getInvoiceType ( $value ['pids'] ) );
					
					// 第五、六列 开票类型
					$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 4, $now_line, self::get_invoice_type ( intval ( $value ['type'] ), TRUE ) );
					
					if (intval ( $value ['type'] ) === 2) {
						$d1 = $value ['d1'];
						$d2 = $value ['d2'];
						$d3 = $value ['d3'];
						$objPHPExcel->getActiveSheet ()->getStyleByColumnAndRow ( 5, $now_line )->getAlignment ()->setWrapText ( TRUE );
						$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 5, $now_line, "纳税人识别号码：$d1\n地址、电话：$d2\n开户行及账号：$d3" );
					}
					
					// 第七列 开票内容
					$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 6, $now_line, $value ['content'] );
					
					// 第八列 备注
					$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 7, $now_line, $value ['remark'] );
					
					// 第九列 申请人
					$objPHPExcel->getActiveSheet ()->setCellValueByColumnAndRow ( 8, $now_line, $value ['user'] );
				}
				
				$objPHPExcel->getActiveSheet ()->mergeCells ( 'E1:F1' );
				
				$ua = $_SERVER ['HTTP_USER_AGENT'];
				
				if (preg_match ( '/MSIE/', $ua ) || preg_match ( '/Trident/', $ua )) {
					$filename = urlencode ( '待审核待打印发票信息' ) . '.xls';
				} else {
					$filename = '待审核待打印发票信息.xls';
				}
				
				// 生成
				$objPHPExcel->setActiveSheetIndex ( 0 );
				header ( 'Content-Type: application/vnd.ms-excel' );
				header ( 'Content-Disposition: attachment;filename="' . $filename . '"' );
				header ( 'Cache-Control: max-age=0' );
				
				$objWriter = PHPExcel_IOFactory::createWriter ( $objPHPExcel, 'Excel5' );
				$objWriter->save ( 'php://output' );
				$objPHPExcel->disconnectWorksheets ();
				unset ( $objPHPExcel );
			} else {
				return User::no_object ( '没有符合要求的执行单' );
			}
		} else {
			return User::no_permission ();
		}
	}
}