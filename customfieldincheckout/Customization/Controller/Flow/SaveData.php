<?php

namespace Brainvire\Customization\Controller\Flow;

use Magento\Framework\App\Action\Context;

class SaveData extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_coreSession;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreSession = $coreSession;
        parent::__construct($context);
    }

    public function execute()
    {

        $gameId = $this->getRequest()->getParam('game_ids');
        $resolution = $this->getRequest()->getParam('resolution');
        $quality = $this->getRequest()->getParam('quality');
        $response['response'] = [];
        if ($gameId && $resolution && $quality) {
            $this->_coreSession->start();
            $this->_coreSession->setGameId($gameId);
            $this->_coreSession->setResolution($resolution);
            $this->_coreSession->setQuality($quality);
            $response['response'] = array('message' => 'success', 'code' => 200);
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
