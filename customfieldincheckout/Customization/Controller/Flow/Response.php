<?php

namespace Brainvire\Customization\Controller\Flow;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Response extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_coreSession = $coreSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    public function execute()
    {
        $this->_coreSession->start();
        $selectedGamesIds = $this->_coreSession->getGameId();
        if ($selectedGamesIds == '') {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('');
            return $resultRedirect;
        } else {
            $resultPage = $this->_resultPageFactory->create();
            return $resultPage;
        }

    }

}
