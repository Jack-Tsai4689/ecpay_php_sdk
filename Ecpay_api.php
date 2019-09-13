<?php
namespace jackcc;

class Ecpay_api {

	/**
	*    Credit信用卡付款產生訂單範例
		$data = array(
			'payment_status'=>$payment_status,
			'HashKey'=>$HashKey,
			'HashIV'=>$HashIV,
			'MerchantID'=>$MerchantID,
			'amount'=>$order['total']+$order['cost'],
			'order_id'=>$order['order_id'],
		);
	*/
	private $use_HashKey;
	private $use_HashIV;
	private $use_MerchantID;
	private $use_url;

	public function __construct($setting)
	{
		$this->use_HashKey = $setting['HashKey'];
		$this->use_HashIV = $setting['HashIV'];
		$this->use_MerchantID = $setting['MerchantID'];
		$this->use_url = $setting['url'];
	}
	public function setting_check()
	{
		$error = false;
		if (empty($this->use_MerchantID)) $error = true;
		if (empty($this->use_HashKey)) $error = true;
		if (empty($this->use_HashIV)) $error = true;
		if (empty($this->use_url)) $error = true;
		if ($error) return false;
		return true;
	}
	public function bank($order)
	{
		if (!$this->setting_check()) {
			throw new Exception("支付系統異常，請聯繫系統管理員");
			exit;
		}
		include_once('ECPay.Payment.Integration.php');
		$obj = new ECPay_AllInOne();
		try {
			//服務參數
				//服務位置 (正式)
				$obj->ServiceURL  = $this->use_url;
				//測試用Hashkey，請自行帶入ECPay提供的HashKey
				$obj->HashKey     = $this->use_HashKey;
				//測試用HashIV，請自行帶入ECPay提供的HashIV
				$obj->HashIV      = $this->use_HashIV;
				//測試用MerchantID，請自行帶入ECPay提供的MerchantID
				$obj->MerchantID  = $this->use_MerchantID;
				//CheckMacValue加密類型，請固定填入1，使用SHA256加密
				$obj->EncryptType = '1';


			//基本參數(請依系統規劃自行調整)
			$obj->Send['ReturnURL']         = 'ec_confirm.php?order_id='.$order['order_id'];    //付款完成通知回傳的網址
			$obj->Send['OrderResultURL']    = 'ec_card_ok.php?order_id='.$order['order_id']; //前景導回商店的網址 
			$obj->Send['MerchantTradeNo']   = $order['order_id']; //訂單編號
			$obj->Send['MerchantTradeDate'] = date('Y/m/d H:i:s'); //交易時間
			$obj->Send['TotalAmount']       = $order['amt']; //交易金額
			$obj->Send['TradeDesc']         = $order['itemdesc']; //交易描述
			$obj->Send['ChoosePayment']     = ECPay_PaymentMethod::Credit ; //付款方式:Credit

			//訂單的商品資料
			$obj->Send['Items'] = $order['items'];

			//Credit信用卡分期付款延伸參數(可依系統需求選擇是否代入)
			//以下參數不可以跟信用卡定期定額參數一起設定
			// $obj->SendExtend['CreditInstallment'] = '' ;    //分期期數，預設0(不分期)，信用卡分期可用參數為:3,6,12,18,24
			// $obj->SendExtend['InstallmentAmount'] = 0 ;    //使用刷卡分期的付款金額，預設0(不分期)
			// $obj->SendExtend['Redeem'] = false ;           //是否使用紅利折抵，預設false
			// $obj->SendExtend['UnionPay'] = false;          //是否為聯營卡，預設false;

			//Credit信用卡定期定額付款延伸參數(可依系統需求選擇是否代入)
			//以下參數不可以跟信用卡分期付款參數一起設定
			// $obj->SendExtend['PeriodAmount'] = '' ;    //每次授權金額，預設空字串
			// $obj->SendExtend['PeriodType']   = '' ;    //週期種類，預設空字串
			// $obj->SendExtend['Frequency']    = '' ;    //執行頻率，預設空字串
			// $obj->SendExtend['ExecTimes']    = '' ;    //執行次數，預設空字串
			
			# 電子發票參數
			/*
			$obj->Send['InvoiceMark'] = ECPay_InvoiceState::Yes;
			$obj->SendExtend['RelateNumber'] = "Test".time();
			$obj->SendExtend['CustomerEmail'] = 'test@ecpay.com.tw';
			$obj->SendExtend['CustomerPhone'] = '0911222333';
			$obj->SendExtend['TaxType'] = ECPay_TaxType::Dutiable;
			$obj->SendExtend['CustomerAddr'] = '台北市南港區三重路19-2號5樓D棟';
			$obj->SendExtend['InvoiceItems'] = array();
			// 將商品加入電子發票商品列表陣列
			foreach ($obj->Send['Items'] as $info)
			{
				array_push($obj->SendExtend['InvoiceItems'],array('Name' => $info['Name'],'Count' =>
					$info['Quantity'],'Word' => '個','Price' => $info['Price'],'TaxType' => ECPay_TaxType::Dutiable));
			}
			$obj->SendExtend['InvoiceRemark'] = '測試發票備註';
			$obj->SendExtend['DelayDay'] = '0';
			$obj->SendExtend['InvType'] = ECPay_InvType::General;
			*/
			//產生訂單(auto submit至ECPay)
			$obj->CheckOut();
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
	public function confirm()
	{
		if (!$this->setting_check()) {
			throw new Exception("支付系統異常，請聯繫系統管理員");
			exit;
		}
		include_once('ECPay.Payment.Integration.php');
		$obj = new ECPay_AllInOne;
		try {
			//服務位置 (正式)
			$obj->HashKey     = $this->use_HashKey;
			//測試用HashIV，請自行帶入ECPay提供的HashIV
			$obj->HashIV      = $this->use_HashIV;
			//測試用MerchantID，請自行帶入ECPay提供的MerchantID
			$obj->MerchantID  = $this->use_MerchantID;
			//CheckMacValue加密類型，請固定填入1，使用SHA256加密
			echo '1|OK';
			$obj->EncryptType = ECPay_EncryptType::ENC_SHA256; // SHA256
			return $obj->CheckOutFeedback();
		} catch (Exception $e) {
			echo '0|'.$e->getMessage();
			return false;
		}
	}
	// 電子地圖
	public function cvs_map($type)
	{
		include_once('ECPayLogistics.php');
		try{
			$AL = new ECPayLogistics();
			switch ($type) {
				case '7-11': 
					$name = '7-11';
					$LogisticsSubType = LogisticsSubType::UNIMART_C2C; 
					break;
				
				case 'family': 
					$name = '全家';
					$LogisticsSubType = LogisticsSubType::FAMILY_C2C; 
					break;
				
				case 'hilife': 
					$LogisticsSubType = LogisticsSubType::HILIFE_C2C; 
					break;
			} 
			$AL->Send = array(
				'MerchantID' => $this->use_MerchantID,
				'MerchantTradeNo' => 'no' . date('YmdHis'),
				'LogisticsSubType' => $LogisticsSubType,
				'IsCollection' => IsCollection::NO,
				'ServerReplyURL' => 'cart_3.php',
				// 'ExtraData' => '測試額外資訊',
				'Device' => Device::PC
			);
			// CvsMap(Button名稱, Form target)
			return $AL->CvsMap('門市選擇('.$name.')');
		} catch(Exception $e) {
			echo $e->getMessage();
		}
	}
	public function ship_order($order, $type = '')
	{
		include_once('ECPayLogistics.php');
		$AL = new EcpayLogistics();
		try {
			$AL->HashKey = $this->use_HashKey;
        	$AL->HashIV = $this->use_HashIV;
			$AL->Send = array(
				'MerchantID' => $this->use_MerchantID,
				'MerchantTradeNo' => $order['order_id'],
				'MerchantTradeDate' => date('Y/m/d H:i:s'),
				'LogisticsType' => LogisticsType::CVS,
				'LogisticsSubType' => $order['cvs_type'],
				'GoodsAmount' => $order['amt'],
				'CollectionAmount' => $order['amt'],
				'IsCollection' => IsCollection::YES,
				'GoodsName' => $order['itemdesc'],
				'SenderName' => '測試寄件者',
				'SenderCellPhone' => '0911222333',
				'ReceiverName' => $order['receive_name'],
				'ReceiverCellPhone' => $order['receive_mobile'],
				'ReceiverEmail' => $order['receive_email'],
				'TradeDesc' => '測試交易敘述',
				'ServerReplyURL' => 'payment/freight/'.$order['order_id'],
				'LogisticsC2CReplyURL' => 'cart/store_change/'.$order['order_id'],
				'Remark' => '測試備註',
				'PlatformID' => '',
			);
			// 幕後建訂單不需代收，已用信用卡刷卡
			if ($type == 'backend') {
				$AL->Send['IsCollection'] = IsCollection::NO;
			} else {
				// 一般訂單，導回頁面
				$AL->Send['ClientReplyURL'] = 'payment/card_ok/'.$order['order_id']; //前景導回商店的網址 
			}
			// ReturnStoreID 有指定則退至指定，否則退回原門市
			$AL->SendExtend = array(
				'ReceiverStoreID' => $order['cvs_id'], // 取件門市
				// 'ReturnStoreID' => '991182' // 退貨門市
			);
			
			if ($type == 'backend') {
				return $AL->BGCreateShippingOrder();
			} else {
				return $AL->CreateShippingOrder();
			}
		} catch(Exception $e) {
			echo $e->getMessage();
		}
	}
	// 建幕後物流單
	public function ship_create_backend($order)
	{
		return $this->ship_order($order, 'backend');
	}
	// 一般物流單
	public function ship_create($order)
	{
		return $this->ship_order($order);
	}
	// 物流狀態回傳
	public function freight()
	{
		// ServerReplyLogisticsStatus
		if (!$this->setting_check()) {
			throw new Exception("支付系統異常，請聯繫系統管理員");
			exit;
		}
		include_once('ECPayLogistics.php');
		$obj = new EcpayLogistics;
		try {
			// 收到綠界科技的物流狀態，並判斷檢查碼是否相符
			$obj->HashKey     = $this->use_HashKey;
			//測試用HashIV，請自行帶入ECPay提供的HashIV
			$obj->HashIV      = $this->use_HashIV;
			//測試用MerchantID，請自行帶入ECPay提供的MerchantID
			$obj->CheckOutFeedback($_POST);
			// 以物流狀態進行相對應的處理
			/** 
			回傳的綠界科技的物流狀態如下:
			Array
			(
				[AllPayLogisticsID] =>
				[BookingNote] =>
				[CheckMacValue] =>
				[CVSPaymentNo] =>
				[CVSValidationNo] =>
				[GoodsAmount] =>
				[LogisticsSubType] =>
				[LogisticsType] =>
				[MerchantID] =>
				[MerchantTradeNo] =>
				[ReceiverAddress] =>
				[ReceiverCellPhone] =>
				[ReceiverEmail] =>
				[ReceiverName] =>
				[ReceiverPhone] =>
				[RtnCode] =>
				[RtnMsg] =>
				[UpdateStatusDate] =>
			)
			*/
			// 在網頁端回應 1|OK
			echo '1|OK';
			return true;
		} catch(Exception $e) {
			echo '0|' . $e->getMessage();
			return false;
		}
	}
}