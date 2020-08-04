<?php
namespace Brainvire\CustomerNotification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;

class AfterAddressSaveObserver implements ObserverInterface
{
    const XML_PATH_EMAIL_TEMPLATE_SHIPPING_ADDRESS_CHANGE  = 'customernotification/notify/shipping_address_change_template';
    const XML_PATH_EMAIL_TEMPLATE_BILLING_ADDRESS_CHANGE  = 'customernotification/notify/billing_address_change_template';
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';
    const XML_PATH_EMAIL_TEMPLATE_ACCOUNT_INFORMATION_CHANGE = 'customernotification/notify/account_information_change_template';

    protected $storeManager;
    protected $_transportBuilder;
    protected $inlineTranslation;

    public function __construct(
        StoreManagerInterface $storeManager,
        TransportBuilder $_transportBuilder,
        StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        SenderResolverInterface $senderResolver = null,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        DataObjectProcessor $dataProcessor,
        CustomerViewHelper $customerViewHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    )
    {        
        $this->storeManager=$storeManager;
        $this->_transportBuilder=$_transportBuilder;
        $this->inlineTranslation=$inlineTranslation;
        $this->_scopeConfig = $scopeConfig;
        $this->senderResolver = $senderResolver ?: ObjectManager::getInstance()->get(SenderResolverInterface::class);
        $this->_addressFactory = $addressFactory;
        $this->_request = $request;
        $this->customerRegistry = $customerRegistry;
        $this->dataProcessor = $dataProcessor;
        $this->customerViewHelper = $customerViewHelper;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getCustomer();
        $storeId = $customer->getStoreId();
        $customerEmail = $customer->getEmail();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }
        $customerData = $this->getFullCustomerObject($customer);
        $postValue = $this->_request->getPost();
        $customer_data = $this->_customerRepositoryInterface->getById($customer->getId());
        $group = $customer_data->getGroupId();
        $website = $customer_data->getWebsiteId();
        $prefix = $customer_data->getPrefix();
        $firstname = $customer_data->getFirstname();
        $middlename = $customer_data->getMiddlename();
        $lastname = $customer_data->getLastname();
        $suffix = $customer_data->getSuffix();
        $dob = $customer_data->getDob();
        $taxvat = $customer_data->getTaxvat();
        $gender = $customer_data->getGender();


        /* post value data */
        $customerGroup = $postValue['customer']['group_id'];
        $customerWebsite = $postValue['customer']['website_id'];
        $customerPrefix = $postValue['customer']['prefix'];
        $customerFirstname = $postValue['customer']['firstname'];
        $customerMiddlename = $postValue['customer']['middlename'];
        $customerLastname = $postValue['customer']['lastname'];
        $customerSuffix = $postValue['customer']['suffix'];
        $customerDob = $postValue['customer']['dob'];
        $newDate = date("Y-m-d", strtotime($customerDob));
        $customerTaxvat = $postValue['customer']['taxvat'];
        $customerGender = $postValue['customer']['gender'];


        if($customer){
            if((isset($customerPrefix) && ($prefix != $customerPrefix)) || (isset($customerMiddlename) && ($middlename != $customerMiddlename)) || (isset($customerSuffix) && ($suffix != $customerSuffix)) || (isset($customerTaxvat) && ($taxvat != $customerTaxvat)) || (($customerGender != NULL) && ($gender != $customerGender)) || (isset($customerDob) && ($dob != $newDate)) || ($firstname != $customerFirstname) || ($lastname != $customerLastname)){
                $this->sendEmailTemplate(
                    $customer,
                    self::XML_PATH_EMAIL_TEMPLATE_ACCOUNT_INFORMATION_CHANGE,
                    self::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ['customer' => $customer, 'store' => $this->storeManager->getStore($storeId)],
                    $this->storeManager->getStore()->getId(),
                    $customerEmail
                );
            }
        }
       
        $billingAddressId = $customer->getDefaultBilling();
        $shippingAddressId = $customer->getDefaultShipping();
        $customerDefaultShipping = $postValue['customer']['default_shipping'];
        $customerDefaultBilling = $postValue['customer']['default_billing'];
        $shippingAddressesArray = $postValue['address'][$shippingAddressId];
        $billingAddressesArray = $postValue['address'][$billingAddressId];
        $shippingStreet_one = $shippingAddressesArray['street'][0];
        $billingStreet_one = $billingAddressesArray['street'][0];
        
        if($shippingAddressId){
            $shippingAddress = $this->_addressFactory->create()->load($shippingAddressId);
            $shippingCity = $shippingAddress->getCity();
            $shippingCountryId = $shippingAddress->getCountryId();
            $shippingPostcode = $shippingAddress->getPostcode();
            $shippingState = $shippingAddress->getRegion();
            $shippingStreet = $shippingAddress->getData('street');
            if(($shippingCity != $shippingAddressesArray['city']) || ($shippingCountryId != $shippingAddressesArray['country_id']) || ($shippingPostcode != $shippingAddressesArray['postcode']) || ($shippingState != $shippingAddressesArray['region']) || ($shippingStreet != $shippingStreet_one)){
                $this->sendEmailTemplate(
                    $customer,
                    self::XML_PATH_EMAIL_TEMPLATE_SHIPPING_ADDRESS_CHANGE,
                    self::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ['customer' => $customerData, 'store' => $this->storeManager->getStore($storeId)],
                    $this->storeManager->getStore()->getId(),
                    $customerEmail
                );
            }
          
        }
        if($billingAddressId){
            $billingAddress = $this->_addressFactory->create()->load($billingAddressId);
            $billingCity = $billingAddress->getCity();
            $billingCountryId = $billingAddress->getCountryId();
            $billingPostcode = $billingAddress->getPostcode();
            $billingState = $billingAddress->getRegion();
            $billingStreet = $billingAddress->getData('street'); 

            if(($billingCity != $billingAddressesArray['city']) || ($billingCountryId != $billingAddressesArray['country_id']) || ($billingPostcode != $billingAddressesArray['postcode']) || ($billingState != $billingAddressesArray['region']) || ($billingStreet != $billingStreet_one)){
                $this->sendEmailTemplate(
                    $customer,
                    self::XML_PATH_EMAIL_TEMPLATE_BILLING_ADDRESS_CHANGE,
                    self::XML_PATH_FORGOT_EMAIL_IDENTITY,
                    ['customer' => $customerData, 'store' => $this->storeManager->getStore($storeId)],
                    $this->storeManager->getStore()->getId(),
                    $customerEmail
                );
            }
        }
       return $this;
    }

    public function sendEmailTemplate(
        $customer,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->_scopeConfig->getValue($template, 'store', $storeId);
        if ($email === null) {
            $email = $customer->getEmail();
        }

        /** @var array $from */
        $from = $this->senderResolver->resolve(
            $this->_scopeConfig->getValue($sender, 'store', $storeId),
            $storeId
        );

        $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($from)
            ->addTo($email)
            ->getTransport();

        $transport->sendMessage();
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param CustomerInterface $customer
     * @param int|string|null $defaultStoreId
     * @return int
     */
    private function getWebsiteStoreId($customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Create an object with data merged from Customer and CustomerSecure
     *
     * @param CustomerInterface $customer
     * @return \Magento\Customer\Model\Data\CustomerSecure
     */
    private function getFullCustomerObject($customer)
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor
            ->buildOutputDataArray($customer, \Magento\Customer\Api\Data\CustomerInterface::class);
        $mergedCustomerData->addData($customerData);
        $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
        return $mergedCustomerData;
    }

}