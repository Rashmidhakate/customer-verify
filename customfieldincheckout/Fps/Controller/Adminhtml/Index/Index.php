<?php
namespace Brainvire\Fps\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
	private $resultPageFactory;
	public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Brainvire_Fps::fps');
    }

    public function execute(){
    	$resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Brainvire_Fps::create'
        )->addBreadcrumb(
            __('Fps'),
            __('Fps')
        )->addBreadcrumb(
            __('Manage Fps'),
            __('Manage Fps')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Fps'));
        return $resultPage;
    }
}