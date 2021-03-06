<?php

$xmlStr = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<getHostedPaymentPageRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
    <merchantAuthentication></merchantAuthentication>
    <transactionRequest>
        <transactionType>authCaptureTransaction</transactionType>
        <amount>0.50</amount>
        <order>
            <invoiceNumber>INV-12345</invoiceNumber>
            <description>Product Description</description>
        </order>
        <lineItems>
            <lineItem>
                <itemId>1</itemId>
                <name>vase</name>
                <description>Cannes logo </description>
                <quantity>18</quantity>
                <unitPrice>45.00</unitPrice>
            </lineItem>
        </lineItems>
        <tax>
            <amount>4.26</amount>
            <name>level2 tax name</name>
            <description>level2 tax</description>
        </tax>
        <duty>
            <amount>8.55</amount>
            <name>duty name</name>
            <description>duty description</description>
        </duty>
        <shipping>
            <amount>4.26</amount>
            <name>level2 tax name</name>
            <description>level2 tax</description>
        </shipping>
        <poNumber>456654</poNumber>
        <customer>
            <id>99999456654</id>
            <email>my@email.com</email>
        </customer>
        <billTo>
            <firstName>Ellen</firstName>
            <lastName>Johnson</lastName>
            <company>Souveniropolis</company>
            <address>14 Main Street</address>
            <city>Pecan Springs</city>
            <state>TX</state>
            <zip>44628</zip>
            <country>USA</country>
            <phoneNumber>1231231234</phoneNumber>
        </billTo>
        <customerIP>192.168.1.1</customerIP>
	<userFields>
          <userField>
             <name>sessionID</name>
             <value>ABC123</value>
          </userField>
        </userFields>
    </transactionRequest>
    <hostedPaymentSettings>
        <setting>
            <settingName>hostedPaymentIFrameCommunicatorUrl</settingName>
        </setting>
        <setting>
            <settingName>hostedPaymentButtonOptions</settingName>
            <settingValue>{"text": "Pay"}</settingValue>
        </setting>
        <setting>
            <settingName>hostedPaymentReturnOptions</settingName>
        </setting>
        <setting>
            <settingName>hostedPaymentOrderOptions</settingName>
            <settingValue>{"show": false}</settingValue>
        </setting>
        <setting>
            <settingName>hostedPaymentPaymentOptions</settingName>
            <settingValue>{"cardCodeRequired": true}</settingValue>
        </setting>
        <setting>
            <settingName>hostedPaymentShippingAddressOptions</settingName>
            <settingValue>{"show": false, "required":true}</settingValue>
        </setting>
        <setting>
            <settingName>hostedPaymentBillingAddressOptions</settingName>
            <settingValue>{"show": true, "required":true}</settingValue>
        </setting>
        <setting>
            <settingName>hostedPaymentSecurityOptions</settingName>
            <settingValue>{"captcha": false}</settingValue>
        </setting>
        <setting>
            <settingName>hostedPaymentStyleOptions</settingName>
            <settingValue>{"bgColor": "green"}</settingValue>
        </setting>

        <setting>
            <settingName>hostedPaymentCustomerOptions</settingName>
            <settingValue>{"showEmail": true, "requiredEmail":true}</settingValue>
        </setting>
    </hostedPaymentSettings>
</getHostedPaymentPageRequest>
XML;
$xml = new SimpleXMLElement($xmlStr);
$xml->merchantAuthentication->addChild('name',getenv('api_login_id'));
$xml->merchantAuthentication->addChild('transactionKey',getenv('transaction_key'));

$commUrl = json_encode(array('url' => thisPageURL()."iCommunicator.html" ),JSON_UNESCAPED_SLASHES);
$xml->hostedPaymentSettings->setting[0]->addChild('settingValue',$commUrl);

$retUrl = json_encode(array("showReceipt" => false ,'url' => thisPageURL()."return.html","urlText"=>"Continue to site", "cancelUrl" => curPageURL()."return.html", "cancelUrlText" => "Cancel" ),JSON_UNESCAPED_SLASHES);
$xml->hostedPaymentSettings->setting[2]->addChild('settingValue',$retUrl);

$url = "https://apitest.authorize.net/xml/v1/request.api";

    try{	//setting the curl parameters.
        $ch = curl_init();
        if (FALSE === $ch)
        	throw new Exception('failed to initialize');
        curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml->asXML());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
        //curl_setopt($ch, CURLOPT_PROXY, 'userproxy.visa.com:80');
        $content = curl_exec($ch);
        $hostedPaymentResponse = new SimpleXMLElement($content);
        if (FALSE === $content)
        	throw new Exception(curl_error($ch), curl_errno($ch));
        curl_close($ch);

    }catch(Exception $e) {
    	trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
	}

    function thisPageURL() {
     $pageURL = 'http';
     if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
     $pageURL .= "://";
     if ($_SERVER["SERVER_PORT"] != "80") {
      $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
     } else {
      $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
     }

     $pageLocation = str_replace('index.php', '', $pageURL);

     return $pageLocation;
    }

?>
