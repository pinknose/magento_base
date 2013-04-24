<?php
/**
 * Copyright ePay | Dit Online Betalingssystem, (c) 2010.
 * 
 * This program is free software. You are allowed to use the software but NOT allowed to modify the software. 
 * It is also not legal to do any changes to the software and distribute it in your own name / brand. 
 */
 
class Mage_Epay_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
	//
    //changing the payment to different from cc payment type and epay payment type
    //
    const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
    const PAYMENT_TYPE_SALE = 'SALE';

    protected $_code  = 'epay_standard';
    protected $_formBlockType = 'epay/standard_form';
	protected $_infoBlockType = 'epay/standard_email';
    
    protected $_isGateway               = true;
    protected $_canAuthorize            = false;// NO! Authorization is not done by webservices! (PCI)
    protected $_canCapture              = true;
	protected $_canCapturePartial 		= true;
    protected $_canRefund               = true;
	protected $_canOrder 				= true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;	// If an internal order is created (phone / mail order) payment must be done using webpay and not an internal checkout method!
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc 				= false;// NO CC is never saved. (PCI)
    
	
    //
    // Allowed currency types
    //
    protected $_allowCurrencyCode = array(
      'ADP','AED','AFA','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZM','BAM','BBD','BDT','BGL','BGN','BHD','BIF','BMD','BND','BOB',
      'BOV','BRL','BSD','BTN','BWP','BYR','BZD','CAD','CDF','CHF','CLF','CLP','CNY','COP','CRC','CUP','CVE','CYP','CZK','DJF','DKK',
      'DOP','DZD','ECS','ECV','EEK','EGP','ERN','ETB','EUR','FJD','FKP','GBP','GEL','GHC','GIP','GMD','GNF','GTQ','GWP','GYD','HKD',
      'HNL','HRK','HTG','HUF','IDR','ILS','INR','IQD','IRR','ISK','JMD','JOD','JPY','KES','KGS','KHR','KMF','KPW','KRW','KWD','KYD',
      'KZT','LAK','LBP','LKR','LRD','LSL','LTL','LVL','LYD','MAD','MDL','MGF','MKD','MMK','MNT','MOP','MRO','MTL','MUR','MVR','MWK',
      'MXN','MXV','MYR','MZM','NAD','NGN','NIO','NOK','NPR','NZD','OMR','PAB','PEN','PGK','PHP','PKR','PLN','PYG','QAR','ROL','RUB',
      'RUR','RWF','SAR','SBD','SCR','SDD','SEK','SGD','SHP','SIT','SKK','SLL','SOS','SRG','STD','SVC','SYP','SZL','THB','TJS','TMM',
      'TND','TOP','TPE','TRL','TRY','TTD','TWD','TZS','UAH','UGX','USD','UYU','UZS','VEB','VND','VUV','XAF','XCD','XOF','XPF','YER',
      'YUM','ZAR','ZMK','ZWD'
    );
    
    //
    // Default constructor
    //
    public function __construct()
    {
		// Nothing to do
    }
	
	protected function _canDoCapture($order)
	{
		if (((int)$this->getConfigData('remoteinterface', $order ? $order->getStoreId() : null)) != 1) {
    		return false;
    	}

		try
		{
			// Read info directly from the database
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$row = $read->fetchRow("select * from epay_order_status where orderid = '" . $order->getIncrementId() . "'");
			
			if($row["status"] == '1')
			{
				$epayamount = ($amount * 100);
				$tid = $row["tid"];
				$param = array
				(
					'merchantnumber' => $this->getConfigData('merchantnumber', $order ? $order->getStoreId() : null),
					'transactionid' => $tid,
					'epayresponse' => 0,
					'pwd' => $this->getConfigData('remoteinterfacepassword', $order ? $order->getStoreId() : null)
				);
				$client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL');
				$result = $client->gettransaction($param);
				
				if($result->gettransactionResult == 1)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			Mage::throwException("Error in connection to ePay: " . $e->getMessage());
		}

		return true;
		
	}

    public function getSession()
    {
        return Mage::getSingleton('epay/session');
    }

    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

	public function canEdit()
	{
		return true;
	}
	
	public function canCapture()
	{
		$Captureorder = $this->_data["info_instance"]->getOrder();
		
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
	    $row = $read->fetchRow("select * from epay_order_status where orderid = '" . $Captureorder->getIncrementId() . "'");
		if($row["status"] == '1')
		{	
			return true;
		}
		
		return false;
		
	}
	
	public function canVoid(Varien_Object $payment)
	{
		$Voidorder = $this->_data["info_instance"]->getOrder();
		
		if (((int)$this->getConfigData('remoteinterface', $Voidorder ? $Voidorder->getStoreId() : null)) != 1) {
    		return false;
    	}
		
		// Read info directly from the database   	
    	$read = Mage::getSingleton('core/resource')->getConnection('core_read');
    	$row = $read->fetchRow("select * from epay_order_status where orderid = '" . $Voidorder->getIncrementId() . "'");
		if ($row['status'] == '1') 
		{
			return $this->_canVoid;
		} 
		else
		{
			return false;
		}
			
		return $this->_canVoid;

	}
	   
	public function canRefund()
    {
		$Creditorder = $this->_data["info_instance"]->getOrder();
		
		if (((int)$this->getConfigData('remoteinterface', $Creditorder ? $Creditorder->getStoreId() : null)) != 1) {
    		return false;
    	}
		
		// Read info directly from the database   	
    	$read = Mage::getSingleton('core/resource')->getConnection('core_read');
    	$row = $read->fetchRow("select * from epay_order_status where orderid = '" . $Creditorder->getIncrementId() . "'");
		if ($row['status'] == '1') 
		{
			return $this->_canRefund;
		} 
		else
		{
			return false;
		}
			
		return $this->_canRefund;
    }

    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('epay/standard_form', $name)->setMethod('epay_standard')->setPayment($this->getPayment())->setTemplate('epay/standard/form.phtml');

        return $block;
    }

    public function validate()
    {
        parent::validate();
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (isset($currency_code)) {
	        if (!in_array($currency_code,$this->_allowCurrencyCode)) {
	            Mage::throwException(Mage::helper('epay')->__('Selected currency code ('.$currency_code.') is not compatabile with ePay'));
	        }
	      }
        return $this;
    }

    public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment)
    {

    }

    public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment)
    {

    }
	
    public function getInfoBlockType()
    {
        return $this->_infoBlockType;
    }
	
    public function processCreditmemo($creditmemo, $payment)
    {
        return $this;
    }
	
    public function processBeforeRefund($invoice, $payment)
    {
        return $this;
    }
	
	//
	// Calculates if integrated layout is used or not
	// If integrated then redirect by use of the ePay V2 relay-script
	// If not just redirect to a page where the standard window is opened
	//
    public function getOrderPlaceRedirectUrl()
    {
    	return Mage::getUrl('epay/standard/redirect');
    }
    
    //
    // Convert from Magento currency to ePay currency
    //
    public function convertToEpayCurrency($cur)
	{
		switch(strtoupper($cur))
		{
			case "ADP":
				return "020";
			case "AED":
				return "784";
			case "AFA":
				return "004";
			case "ALL":
				return "008";
			case "AMD":
				return "051";
			case "ANG":
				return "532";
			case "AOA":
				return "973";
			case "ARS":
				return "032";
			case "AUD":
				return "036";
			case "AWG":
				return "533";
			case "AZM":
				return "031";
			case "BAM":
				return "977";
			case "BBD":
				return "052";
			case "BDT":
				return "050";
			case "BGL":
				return "100";
			case "BGN":
				return "975";
			case "BHD":
				return "048";
			case "BIF":
				return "108";
			case "BMD":
				return "060";
			case "BND":
				return "096";
			case "BOB":
				return "068";
			case "BOV":
				return "984";
			case "BRL":
				return "986";
			case "BSD":
				return "044";
			case "BTN":
				return "064";
			case "BWP":
				return "072";
			case "BYR":
				return "974";
			case "BZD":
				return "084";
			case "CAD":
				return "124";
			case "CDF":
				return "976";
			case "CHF":
				return "756";
			case "CLF":
				return "990";
			case "CLP":
				return "152";
			case "CNY":
				return "156";
			case "COP":
				return "170";
			case "CRC":
				return "188";
			case "CUP":
				return "192";
			case "CVE":
				return "132";
			case "CYP":
				return "196";
			case "CZK":
				return "203";
			case "DJF":
				return "262";
			case "DKK":
				return "208";
			case "DOP":
				return "214";
			case "DZD":
				return "012";
			case "ECS":
				return "218";
			case "ECV":
				return "983";
			case "EEK":
				return "233";
			case "EGP":
				return "818";
			case "ERN":
				return "232";
			case "ETB":
				return "230";
			case "EUR":
				return "978";
			case "FJD":
				return "242";
			case "FKP":
				return "238";
			case "GBP":
				return "826";
			case "GEL":
				return "981";
			case "GHC":
				return "288";
			case "GIP":
				return "292";
			case "GMD":
				return "270";
			case "GNF":
				return "324";
			case "GTQ":
				return "320";
			case "GWP":
				return "624";
			case "GYD":
				return "328";
			case "HKD":
				return "344";
			case "HNL":
				return "340";
			case "HRK":
				return "191";
			case "HTG":
				return "332";
			case "HUF":
				return "348";
			case "IDR":
				return "360";
			case "ILS":
				return "376";
			case "INR":
				return "356";
			case "IQD":
				return "368";
			case "IRR":
				return "364";
			case "ISK":
				return "352";
			case "JMD":
				return "388";
			case "JOD":
				return "400";
			case "JPY":
				return "392";
			case "KES":
				return "404";
			case "KGS":
				return "417";
			case "KHR":
				return "116";
			case "KMF":
				return "174";
			case "KPW":
				return "408";
			case "KRW":
				return "410";
			case "KWD":
				return "414";
			case "KYD":
				return "136";
			case "KZT":
				return "398";
			case "LAK":
				return "418";
			case "LBP":
				return "422";
			case "LKR":
				return "144";
			case "LRD":
				return "430";
			case "LSL":
				return "426";
			case "LTL":
				return "440";
			case "LVL":
				return "428";
			case "LYD":
				return "434";
			case "MAD":
				return "504";
			case "MDL":
				return "498";
			case "MGF":
				return "450";
			case "MKD":
				return "807";
			case "MMK":
				return "104";
			case "MNT":
				return "496";
			case "MOP":
				return "446";
			case "MRO":
				return "478";
			case "MTL":
				return "470";
			case "MUR":
				return "480";
			case "MVR":
				return "462";
			case "MWK":
				return "454";
			case "MXN":
				return "484";
			case "MXV":
				return "979";
			case "MYR":
				return "458";
			case "MZM":
				return "508";
			case "NAD":
				return "516";
			case "NGN":
				return "566";
			case "NIO":
				return "558";
			case "NOK":
				return "578";
			case "NPR":
				return "524";
			case "NZD":
				return "554";
			case "OMR":
				return "512";
			case "PAB":
				return "590";
			case "PEN":
				return "604";
			case "PGK":
				return "598";
			case "PHP":
				return "608";
			case "PKR":
				return "586";
			case "PLN":
				return "985";
			case "PYG":
				return "600";
			case "QAR":
				return "634";
			case "ROL":
				return "642";
			case "RUB":
				return "643";
			case "RUR":
				return "810";
			case "RWF":
				return "646";
			case "RSD":
				return "941";
			case "SAR":
				return "682";
			case "SBD":
				return "090";
			case "SCR":
				return "690";
			case "SDD":
				return "736";
			case "SEK":
				return "752";
			case "SGD":
				return "702";
			case "SHP":
				return "654";
			case "SIT":
				return "705";
			case "SKK":
				return "703";
			case "SLL":
				return "694";
			case "SOS":
				return "706";
			case "SRG":
				return "740";
			case "STD":
				return "678";
			case "SVC":
				return "222";
			case "SYP":
				return "760";
			case "SZL":
				return "748";
			case "THB":
				return "764";
			case "TJS":
				return "972";
			case "TMM":
				return "795";
			case "TND":
				return "788";
			case "TOP":
				return "776";
			case "TPE":
				return "626";
			case "TRL":
				return "792";
			case "TRY":
				return "949";
			case "TTD":
				return "780";
			case "TWD":
				return "901";
			case "TZS":
				return "834";
			case "UAH":
				return "980";
			case "UGX":
				return "800";
			case "USD":
				return "840";
			case "UYU":
				return "858";
			case "UZS":
				return "860";
			case "VEB":
				return "862";
			case "VND":
				return "704";
			case "VUV":
				return "548";
			case "XAF":
				return "950";
			case "XCD":
				return "951";
			case "XOF":
				return "952";
			case "XPF":
				return "953";
			case "YER":
				return "886";
			case "YUM":
				return "891";
			case "ZAR":
				return "710";
			case "ZMK":
				return "894";
			case "ZWD":
				return "716";
		}
		
		return "ERROR - CURRENCY COULD NOT BE TRANSLATED TO EPAY: " . $cur;
	}
	
	public function calcCardtype($cardid)
	{
		switch($cardid)
		{
			case 1:
				return 'Dankort / VISA/Dankort';
			case 2:
				return 'eDankort';
			case 3:
				return 'VISA / VISA Electron';
			case 4:
				return 'MasterCard';
			case 6:
				return 'JCB';
			case 7:
				return 'Maestro';
			case 8:
				return 'Diners Club';
			case 9:
				return 'American Express';
			case 10:
				return 'ewire';
			case 12:
				return 'Nordea e-betaling';
			case 13:
				return 'Danske Netbetalinger';
			case 14:
				return 'PayPal';
			case 16:
				return 'MobilPenge';
		}
	}
    
    //
    // Calculate inbound MD5 key to ePay
	//
    public function calcMd5Key($order, $accepturl, $declineurl, $callbackurl)
    {
		$md5stamp = md5(
					"UTF-8" .
					"magento" .
					$this->getConfigData('windowstate', $order ? $order->getStoreId() : null) .
					$this->getConfigData('merchantnumber', $order ? $order->getStoreId() : null) .
					(((float)$order->getBaseTotalDue()) * 100) .
					$this->convertToEpayCurrency($order->getBaseCurrency()->getCode()) .
					$this->getCheckout()->getLastRealOrderId() .
					$accepturl .
					$declineurl .
					$callbackurl .
					$this->getConfigData('authmail', $order ? $order->getStoreId() : null) .
					$this->getConfigData('authsms', $order ? $order->getStoreId() : null) .
					$this->getConfigData('instantcapture', $order ? $order->getStoreId() : null) .
					$this->getConfigData('group', $order ? $order->getStoreId() : null) .
					$this->calcLanguage(Mage::app()->getLocale()->getLocaleCode()) .
					$this->getConfigData('ownreceipt', $order ? $order->getStoreId() : null) .
					$this->getConfigData('md5key', $order ? $order->getStoreId() : null)
					);
		
		return $md5stamp;
		
    }
    
    //
    // Hmm - magento has no support for greenland
    // and iceland
    //
    function calcLanguage($lan)
	{
		$res = "";
		switch($lan)
		{
			case "da_DK":
				return "1";
			case "de_CH":
				return "7";
			case "de_DE":
				return "7";
			case "en_AU":
				return "2";
			case "en_GB":
				return "2";
			case "en_NZ":
				return "2";
			case "en_US":
				return "2";
			case "sv_SE":
				return "3";
			case "nn_NO":
				return "4";
		}
		return "1";
	}

    function getEpayErrorText($errorcode)
    {
		$res = "Unable to lookup errorcode";
		$client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL');
		$param = array
		(
			'merchantnumber' => $this->getConfigData('merchantnumber', $this->getOrder() ? $this->getOrder()->getStoreId() : null),
			'language' => $this->calcLanguage(Mage::app()->getLocale()->getLocaleCode()),
			'epayresponsecode' => $errorcode,
			'epayresponsestring' => 0,
			'epayresponse' => 0,
			'pwd' => $this->getConfigData('remoteinterfacepassword', $this->getOrder() ? $this->getOrder()->getStoreId() : null)
		);
	    $client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL');
	    $result = $client->getEpayError($param);
		
		if($result->getEpayErrorResult == 1)
		{
			$res = $result->epayresponsestring;
		}
	    return $res;
    }
    
    function getPbsErrorText($errorcode)
    {
    	$res = "Unable to lookup errorcode";
		try
		{
			$client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL');
			$param = array
			(
				'merchantnumber' => $this->getConfigData('merchantnumber', $this->getOrder() ? $this->getOrder()->getStoreId() : null),
				'language' => $this->calcLanguage(Mage::app()->getLocale()->getLocaleCode()),
				'pbsresponsecode' => $errorcode,
				'epayresponsestring' => 0,
				'epayresponse' => 0,
				'pwd' => $this->getConfigData('remoteinterfacepassword', $this->getOrder() ? $this->getOrder()->getStoreId() : null)
			);
			$client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL');
			$result = $client->getPbsError($param);
			if($result->getPbsErrorResult == 1)
			{
				$res = $result->pbsresponsestring;
			}
		}
		catch (Exception $e)
		{
			return $res;
		}
	    return $res;
    }

    public function capture(Varien_Object $payment, $amount)
    {
		//
		// Verify if remote interface is enabled
		//
		if(!$this->_canDoCapture($payment->getOrder()))
		{
			return $this;
		}
		
		if(((int)$this->getConfigData('remoteinterface', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)) != 1)
		{
			$this->addOrderComment($payment->getOrder(), Mage::helper('epay')->__('EPAY_LABEL_73'));
			return $this;
		}
		
		try
		{
			//
			// Read info directly from the database
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$row = $read->fetchRow("select * from epay_order_status where orderid = '" . $payment->getOrder()->getIncrementId() . "'");
			if($row["status"] == '1')
			{
				$epayamount = ($amount * 100);
				
				$tid = $row["tid"];
				$param = array
				(
					'merchantnumber' => $this->getConfigData('merchantnumber', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null),
					'transactionid' => $tid,
					'amount' => $epayamount,
					'group' => '',
					'pbsResponse' => 0,
					'epayresponse' => 0,
					'pwd' => $this->getConfigData('remoteinterfacepassword', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)
				);
				$client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL');
				$result = $client->capture($param);
				if($result->captureResult == 1)
				{
					//
					// Success - transaction captured!
					//
					$this->addOrderComment($payment->getOrder(), "Transaction with id: " . $tid . " has been captured by amount: " . number_format($amount, 2, ",", "."));
					if(!$payment->getParentTransactionId() || $tid != $payment->getParentTransactionId())
					{
						$payment->setTransactionId($tid);
					}
					$payment->setIsTransactionClosed(0);
				}
				else
				{
					if($result->epayresponse !=  - 1)
					{
						if($result->epayresponse ==  - 1002)
						{
							$this->addOrderComment($payment->getOrder(), "Transaction could not be deleted by ePay: " . $result->epayresponse . ". Forretningsnummeret findes ikke.");
							Mage::throwException("Transaction could not be captured by ePay: " . $result->epayresponse . ". Forretningsnummeret findes ikke.");
						}
						else
						{
							if($result->epayresponse ==  - 1003 || $result->epayresponse ==  - 1006)
							{
								$this->addOrderComment($payment->getOrder(), "Transaction could not be captured by ePay: " . $result->epayresponse . ". Der er ikke adgang til denne funktion (API / Remote Interface).");
								Mage::throwException("Transaction could not be captured by ePay: " . $result->epayresponse . ". Der er ikke adgang til denne funktion (API / Remote Interface).");
							}
							else
							{
								$this->addOrderComment($payment->getOrder(), 'Transaction could not be captured by ePay: ' . $result->epayresponse . '. ' . $this->getEpayErrorText($result->epayresponse));
								Mage::throwException('Transaction could not be captured by ePay: ' . $result->epayresponse . '. ' . $this->getEpayErrorText($result->epayresponse));
							}
						}
					}
					else
					{
						Mage::throwException("Transaction could not be captured by ePay: " . $result->pbsResponse . '. ' . $this->getPbsErrorText($result->pbsResponse));
					}
				}
			}
			else
			{
				//
				// Somehow the order was not found - this must be an error!
				//
				Mage::throwException("Order not found - please check the epay_order_status table!");
			}
		}
		catch (Exception $e)
		{
			Mage::throwException("Error in connection to ePay: " . $e->getMessage());
		}
			
		return $this;

    }

    public function refund(Varien_Object $payment, $amount)
    {
    	$session = Mage::getSingleton('adminhtml/session');
		//
    	// Verify if remote interface is enabled
    	// 
		try
		{
			
	    	if (((int)$this->getConfigData('remoteinterface', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)) != 1)
			{
				$this->addOrderComment($payment->getOrder(), Mage::helper('epay')->__('EPAY_LABEL_74'));
				throw new Exception(Mage::helper('epay')->__('EPAY_LABEL_74'));
			}
	    	
	    	//
			// Read info directly from the database  

			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$row = $read->fetchRow("select * from epay_order_status where orderid = '" . $payment->getOrder()->getIncrementId() . "'");
			if($row["status"] == '1')
			{
				$epayamount = ($amount * 100);
				$tid = $row["tid"];
				$param = array
				(
					'merchantnumber' => $this->getConfigData('merchantnumber', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null),
					'transactionid' => $tid,
					'amount' => $epayamount,
					'group' => '',
					'pbsresponse' => 0,
					'epayresponse' => 0,
					'pwd' => $this->getConfigData('remoteinterfacepassword', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)
				);
				$client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL');
				$result = $client->credit($param);
				if($result->creditResult == 1)
				{
					//f
					// Success - transaction credited!
					//
					$this->addOrderComment($payment->getOrder(), "Transaction with id: " . $tid . " has been credited by amount: " . number_format($amount, 2, ",", "."));
				}
				else
				{
		    		if ($result->epayresponse == -1002)
					{
		    			$this->addOrderComment($payment->getOrder(), "An error (" . $result->epayresponse . ") occured in the communication to ePay: The merchantnumber you are using does not exists or is disabled. Please log into your ePay account to verify your merchantnumber. This can be done from the menu: SETTINGS -> PAYMENT SYSTEM.");
		    			throw new Exception("An error (" . $result->epayresponse . ") occured in the communication to ePay: The merchantnumber you are using does not exists or is disabled. Please log into your ePay account to verify your merchantnumber. This can be done from the menu: SETTINGS -> PAYMENT SYSTEM.");
					} 
					elseif ($result->epayresponse == -1003)
					{
		    			$this->addOrderComment($payment->getOrder(), "An error (" . $result->epayresponse . ") occured in the communication to ePay: The IP address your system calls ePay from is UNKNOWN. Please log into your ePay account to verify enter the IP address your system calls ePay from. This can be done from the menu: API / WEBSERVICES -> ACCESS.");
		    			throw new Exception("An error (" . $result->epayresponse . ") occured in the communication to ePay: The IP address your system calls ePay from is UNKNOWN. Please log into your ePay account to verify enter the IP address your system calls ePay from. This can be done from the menu: API / WEBSERVICES -> ACCESS.");
		    		} 
					elseif($result->epayresponse ==  -1006)
					{
						$this->addOrderComment($payment->getOrder(), "An error (" . $result->epayresponse . ") occured in the communication to ePay: Your ePay account has not access to API / Remote Interface. This is only for ePay BUSINESS accounts. Please contact ePay to upgrade your ePay account.");
						throw new Exception("An error (" . $result->epayresponse . ") occured in the communication to ePay: Your ePay account has not access to API / Remote Interface. This is only for ePay BUSINESS accounts. Please contact ePay to upgrade your ePay account.");
					}
					elseif($result->epayresponse == -1021)
					{
						$this->addOrderComment($payment->getOrder(), "An error (" . $result->epayresponse . ") occured in the communication to ePay: An operation every 15 minutes can be performed on a transaction. Please wait 15 minutes and try again.");
						throw new Exception("An error (" . $result->epayresponse . ") An operation every 15 minutes can be performed on a transaction. Please wait 15 minutes and try again.");
					}
					else
					{
		    			$this->addOrderComment($payment->getOrder(), "An error (" . $result->epayresponse . ") occured in the communication to ePay: " . $this->getEpayErrorText($result->epayresponse));
		    			throw new Exception("An error (" . $result->epayresponse . ") occured in the communication to ePay: " . $this->getEpayErrorText($result->epayresponse));
		    		}
				
				}
			}
			else
			{
				//
				// Somehow the order was not found - this must be an error!
				//
				throw new Exception("Order not found - please check the epay_order_status table!");
			}
		}
		catch (Exception $e)
		{
			$session->addException($e, $e->getMessage() . " - Go to the ePay administration to credit the payment manually.");
		}
		
        return $this;
			
    }

    public function void (Varien_Object $payment)
	{
		$this->cancel($payment);
		return $this;
	}
	
    
    public function cancel(Varien_Object $payment)
    {
		if(Mage::app()->getRequest()->getActionName() == 'save')
		{
			return;
		}

    	//
    	// Verify if remote interface is enabled
    	// 
    	if (((int)$this->getConfigData('remoteinterface', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)) != 1) {
    		$this->addOrderComment($payment->getOrder(), Mage::helper('epay')->__('EPAY_LABEL_75'));
    		return;
    	}
		
		try
		{
			//
			// Read info directly from the database
			//
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$row = $read->fetchRow("select * from epay_order_status where orderid = '" . $payment->getOrder()->getIncrementId() . "'");
			
			if($row["status"] == '1')
			{
				$tid = $row["tid"];
				$param = array
				(
					'merchantnumber' => $this->getConfigData('merchantnumber', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null),
					'transactionid' => $tid,
					'group' => '',
					'epayresponse' => 0,
					'pwd' => $this->getConfigData('remoteinterfacepassword', $payment->getOrder() ? $payment->getOrder()->getStoreId() : null)
				);
				
				$client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL');
				$result = $client->delete($param);
				if($result->deleteResult == 1)
				{
					//
					// Success - transaction deleted!
					//
					$this->addOrderComment($payment->getOrder(), "Transaction deleted with transaction id: " . $tid);
					$payment->getOrder()->save();
				}
				else
				{
					if($result->epayresponse !=  - 1)
					{
						if($result->epayresponse ==  - 1002)
						{
							$this->addOrderComment($payment->getOrder(), "Transaction could not be deleted by ePay: " . $result->epayresponse . ". Forretningsnummeret findes ikke.");
							Mage::throwException("Transaction could not be deleted by ePay: " . $result->epayresponse . ". Forretningsnummeret findes ikke.");
						}
						elseif($result->epayresponse ==  - 1003 || $result->epayresponse ==  - 1006)
						{
							$this->addOrderComment($payment->getOrder(), "Transaction could not be captured by ePay: " . $result->epayresponse . ". Der er ikke adgang til denne funktion (API / Remote Interface).");
							Mage::throwException("Transaction could not be deleted by ePay: " . $result->epayresponse . ". Der er ikke adgang til denne funktion (API / Remote Interface).");
						}
						else
						{
							$this->addOrderComment($payment->getOrder(), 'Transaction could not be deleted by ePay: ' . $result->epayresponse . '. ' . $this->getEpayErrorText($result->epayresponse));
							Mage::throwException('Transaction could not be deleted by ePay: ' . $result->epayresponse . '. ' . $this->getEpayErrorText($result->epayresponse));
						}
					}
					else
					{
						Mage::throwException('Unknown response from ePay: ' . $result->epayresponse);
					}
				}
			}
			elseif($row["status"] == '0')
			{
				//
				// Do nothing - the order is to be canceled without any communication to ePay
				//
			}
			else
			{
				//
				// Somehow the order was not found - this must be an error!
				//
				Mage::throwException("Order not found - please check the epay_order_status table!");
			}
		}
		catch (Exception $e)
		{
			Mage::throwException("Error in connection to ePay: " . $e->getMessage());
		}
    }
    
    public function addOrderComment($order, $comment)
    {
    	$order->addStatusToHistory($order->getStatus(), $comment);
		$order->save();
    }
}