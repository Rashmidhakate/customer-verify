<?php
namespace Brainvire\CategoryBannerslider\Controller\Adminhtml\Banner;

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
        return $this->_authorization->isAllowed('Brainvire_CategoryBannerslider::manage');
    }

    public function execute(){
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Brainvire_CategoryBannerslider::manage_banner'
        )->addBreadcrumb(
            __('Category Banner'),
            __('Category Banner')
        )->addBreadcrumb(
            __('Manage Category Banner'),
            __('Manage Category Banner')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Category Banner'));
        return $resultPage;
    }
}