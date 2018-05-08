<?php

namespace Adyen\MockTest;

class CheckoutTest extends TestCaseMock
{
    const NO_CHECKOUT_KEY = "Please insert a valid Checkout API Key in your test.ini file";
    const MISSING_FIELDS = "Missing the following fields:";

    /**
     * @param $jsonFile Json file location
     * @param $httpStatus expected http status code
     *
     * @dataProvider successPaymentMethodsProvider
     */
    public function testPaymentMethodsSuccess($jsonFile, $httpStatus)
    {
        // create Checkout client
        $client = $this->createCheckoutMockClient($jsonFile, $httpStatus);

        // initialize service
        $service = new \Adyen\Service\Checkout($client);

        $params = array('merchantAccount' => "YourMerchantAccount");
        $result = $service->paymentMethods($params);

        $this->assertArrayHasKey('paymentMethods', $result);

    }

    public static function successPaymentMethodsProvider()
    {
        return array(
            array('tests/Resources/Checkout/payment-methods-success.json', 200),
        );
    }

    /**
     * @param $jsonFile
     * @param $httpStatus
     * @param $expectedExceptionMessage
     * @dataProvider failurePaymentMethodsProvider
     *
     */
    public function testPaymentMethodsFailure($jsonFile, $httpStatus, $expectedExceptionMessage)
    {
        // create Checkout client
        $client = $this->createCheckoutMockClient($jsonFile, $httpStatus);

        if ($expectedExceptionMessage == self::NO_CHECKOUT_KEY) {
            $client->setXApiKey("");
        }
        // initialize service
        $service = new \Adyen\Service\Checkout($client);

        $params = array('merchantAccount' => "YourMerchantAccount");
        try {
            $result = $service->paymentMethods($params);
        } catch (\Exception $e) {
        }

        $this->assertEquals('Adyen\AdyenException', get_class($e));
        $this->assertContains($expectedExceptionMessage, $e->getMessage());
        if ($httpStatus != null) {
            $this->assertEquals($httpStatus, $e->getStatus());
        }

    }

    public static function failurePaymentMethodsProvider()
    {
        return array(
            array('tests/Resources/Checkout/invalid-merchant-account.json', 403, "Invalid Merchant Account"),
            array('tests/Resources/Checkout/payment-methods-forbidden.json', null, "Forbidden"),
            array(null, null, self::NO_CHECKOUT_KEY)
        );
    }

    /**
     * @param $jsonFile
     * @param $httpStatus
     *
     * @dataProvider successPaymentsProvider
     *
     */
    public function testPaymentsSuccess($jsonFile, $httpStatus)
    {
        // create Checkout client
        $client = $this->createCheckoutMockClient($jsonFile, $httpStatus);

        // initialize service
        $service = new \Adyen\Service\Checkout($client);

        $params = array(
            'merchantAccount' => "YourMerchantAccount",
            'amount' => array('currency' => "EUR", 'value' => 1000),
            'paymentMethod' => array(
                'type' => "scheme",
                'number' => "4111111111111111",
                'expiryMonth' => "08",
                'expiryYear' => "2018",
                'holderName' => "John Smith",
                'cvc' => "737"
            ),
            'reference' => "Your order number",
            'returnUrl' => "https://your-company.com/...",
            'additionalData' => array(
                'executeThreeD' => true
            )
        );
        $result = $service->payments($params);

        $this->assertContains($result['resultCode'], array('Authorised', 'RedirectShopper'));

    }

    public static function successPaymentsProvider()
    {
        return array(
            array('tests/Resources/Checkout/payments-success.json', 200),
            array('tests/Resources/Checkout/payments-success-3D.json', 200)
        );
    }

    /**
     * @param $jsonFile
     * @param $httpStatus
     * @param $expectedExceptionMessage
     * @dataProvider failurePaymentsProvider
     *
     */
    public function testPaymentsFailure($jsonFile, $httpStatus, $expectedExceptionMessage)
    {
        // create Checkout client
        $client = $this->createCheckoutMockClient($jsonFile, $httpStatus);

        // initialize service
        $service = new \Adyen\Service\Checkout($client);

        $params = array(
            'merchantAccount' => "YourMerchantAccount",
            'amount' => array('currency' => "EUR", 'value' => 1000),
            'paymentMethod' => array(
                'type' => "scheme",
                'number' => "4111111111111111",
                'expiryMonth' => "08",
                'expiryYear' => "2018",
                'holderName' => "John Smith",
                'cvc' => "737"
            ),
            'returnUrl' => "https://your-company.com/..."
        );
        if ($expectedExceptionMessage != self::MISSING_FIELDS) {
            $params['reference'] = 'yourownreference';
        }

        try {
            $result = $service->payments($params);
        } catch (\Exception $e) {
        }
        $this->assertEquals('Adyen\AdyenException', get_class($e));
        $this->assertContains($expectedExceptionMessage, $e->getMessage());
        if ($httpStatus != null) {
            $this->assertEquals($httpStatus, $e->getStatus());
        }
    }


    public static function failurePaymentsProvider()
    {
        return array(
            array('tests/Resources/Checkout/invalid-merchant-account.json', 403, "Invalid Merchant Account"),
            array('tests/Resources/Checkout/payments-forbidden.json', null, "Forbidden"),
            array(null, null, self::MISSING_FIELDS)
        );
    }

    /**
     * @param $jsonFile
     * @param $httpStatus
     *
     * @dataProvider successPaymentsDetailsProvider
     *
     */
    public function testPaymentsDetailsSuccess($jsonFile, $httpStatus)
    {
        // create Checkout client
        $client = $this->createCheckoutMockClient($jsonFile, $httpStatus);

        // initialize service
        $service = new \Adyen\Service\Checkout($client);

        $params = array(
            'merchantAccount' => "YourMerchantAccount",
            'paymentData' => 'Ab02b4c0!BQABAgCJN1wRZuGJmq8dMncmypvknj9s7l5Tj...',
            'details' => array(
                'MD' => 'sdfsdfsdf...',
                'PaRes' => 'sdkfhskdjfsdf...'
            ),
        );

        $result = $service->paymentsDetails($params);

        $this->assertContains($result['resultCode'], array('Authorised'));
    }

    public static function successPaymentsDetailsProvider()
    {
        return array(
            array('tests/Resources/Checkout/payments-details-success.json', 200)
        );
    }

    /**
     * @param $jsonFile
     * @param $httpStatus
     *
     * @dataProvider successSetupProvider
     *
     */
    public function testSetupSuccess($jsonFile, $httpStatus)
    {
        // create Checkout client
        $client = $this->createCheckoutMockClient($jsonFile, $httpStatus);

        // initialize service
        $service = new \Adyen\Service\Checkout($client);

        $params = array(
            'merchantAccount' => "YourMerchantAccount",
            'amount' => array('currency' => "EUR", 'value' => 1000),
            'countryCode' => "NL",
            'reference' => "Your order number",
            'returnUrl' => "https://your-company.com/"
        );

        $result = $service->setup($params);

        $this->assertArrayHasKey("paymentData", $result);
        $this->assertArrayHasKey("paymentMethods", $result);
    }

    public static function successSetupProvider()
    {
        return array(
            array('tests/Resources/Checkout/setup-success.json', 200)
        );
    }

    /**
     * @param $jsonFile
     * @param $httpStatus
     *
     * @dataProvider successVerifyProvider
     *
     */
    public function testVerifySuccess($jsonFile, $httpStatus)
    {
        // create Checkout client
        $client = $this->createCheckoutMockClient($jsonFile, $httpStatus);

        // initialize service
        $service = new \Adyen\Service\Checkout($client);

        $params = array(
            'payload' => "YourPayload"
        );

        $result = $service->verify($params);

        $this->assertContains($result['resultCode'], array('Authorised'));
    }

    public static function successVerifyProvider()
    {
        return array(
            array('tests/Resources/Checkout/verify-success.json', 200)
        );
    }

}