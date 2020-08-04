<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Brainvire\Customerverify\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;

class CustomerData implements ObserverInterface {
	protected $_request;
	protected $groupRepository;
	/**
	 * @var \Magento\Framework\App\ActionFlag
	 */
	protected $_actionFlag;

	/**
	 * @var \Magento\Framework\Message\ManagerInterface
	 */
	protected $messageManager;

	/**
	 * @var \Magento\Framework\Session\SessionManagerInterface
	 */
	protected $_session;

	/**
	 * Customer data
	 *
	 * @var \Magento\Customer\Model\Url
	 */
	protected $_customerUrl;
	/**
	 * @var CustomerRepositoryInterface
	 */
	protected $customerRepository;

	public function __construct(
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\Session\SessionManagerInterface $customerSession,
		\Magento\Customer\Model\Url $customerUrl,
		\Magento\Framework\App\ActionFlag $actionFlag
	) {
		$this->_request = $request;
		$this->groupRepository = $groupRepository;
		$this->customerRepositoryInterface = $customerRepositoryInterface;
		$this->messageManager = $messageManager;
		$this->_customerUrl = $customerUrl;
		$this->_session = $customerSession;
		$this->_actionFlag = $actionFlag;
	}

	/**
	 *
	 * @param \Magento\Framework\Event\Observer $observer
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $observer) {
		$controller = $observer->getControllerAction();
		$loginParams = $controller->getRequest()->getPost('login');
		$login = (is_array($loginParams) && array_key_exists('username', $loginParams))
		? $loginParams['username']
		: null;
		$customer = $this->getCustomerRepository()->get($login);
		$customer = $this->customerRepositoryInterface->getById($customer->getId());
		$customerAttributeData = $customer->__toArray();
		if (!isset($customerAttributeData["custom_attributes"]["is_verified"])) {
			$this->messageManager->addError(__('This account is not verified.'));
			$this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
			$this->_session->setUsername($login);
			$beforeUrl = $this->_session->getBeforeAuthUrl();
			$url = $beforeUrl ? $beforeUrl : $this->_customerUrl->getLoginUrl();
			$controller->getResponse()->setRedirect($url);
			$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/21-8-customerverify.log');
			$logger = new \Zend\Log\Logger();
			$logger->addWriter($writer);
			$logger->info($customerAttributeData["custom_attributes"]);
		}else{
			if ($customer->getCustomAttribute('is_verified')->getValue() == 0) {
				$this->messageManager->addError(__('This account is not verified.'));
				$this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
				$this->_session->setUsername($login);
				$beforeUrl = $this->_session->getBeforeAuthUrl();
				$url = $beforeUrl ? $beforeUrl : $this->_customerUrl->getLoginUrl();
				$controller->getResponse()->setRedirect($url);
			}
			$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/21-8-customerverify.log');
			$logger = new \Zend\Log\Logger();
			$logger->addWriter($writer);
			$logger->info("not available");
		}

		return $this;
	}

	/**
	 * Get customer repository
	 *
	 * @return \Magento\Customer\Api\CustomerRepositoryInterface
	 */
	public function getCustomerRepository() {

		if (!($this->customerRepository instanceof \Magento\Customer\Api\CustomerRepositoryInterface)) {
			return \Magento\Framework\App\ObjectManager::getInstance()->get(
				\Magento\Customer\Api\CustomerRepositoryInterface::class
			);
		} else {
			return $this->customerRepository;
		}
	}

}