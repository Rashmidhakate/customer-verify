<?php
namespace Brainvire\Customerterm\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
	public function __construct(
		\Magento\Customer\Model\Session $customerSession
	){
		$this->customerSession = $customerSession;
	}

	public function isLoggedIn(){
		return $this->customerSession->isLoggedIn();
	}
}