<?php

namespace Adyen\Service\ResourceModel\Checkout;

class PaymentsDetails extends \Adyen\Service\AbstractResource
{

    protected $_requiredFields = array(
        'paymentData',
        'details'
    );

    protected $_endpoint;

    public function __construct($service)
    {
        $this->_endpoint = $service->getClient()->getConfig()->get('endpointCheckout') .'/'. $service->getClient()->getApiCheckoutVersion() . '/payments/details';
        parent::__construct($service, $this->_endpoint, $this->_requiredFields);
    }

}
