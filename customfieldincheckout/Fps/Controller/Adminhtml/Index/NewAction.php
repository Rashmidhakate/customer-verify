<?php
namespace Brainvire\Fps\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class NewAction extends \Magento\Backend\App\Action
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
        return $this->_authorization->isAllowed('Brainvire_Fps::fps_upload');
    }

    public function execute(){
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Brainvire_Fps::fps_upload'
        )->addBreadcrumb(
            __('Fps'),
            __('Fps')
        )->addBreadcrumb(
            __('Fps Upload'),
            __('Fps Upload')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Fps Upload'));
        return $resultPage;
    }
}