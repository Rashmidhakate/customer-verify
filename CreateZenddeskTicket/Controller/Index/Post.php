<?php
namespace Brainvire\CreateZenddeskTicket\Controller\Index;

use Zendesk\API\HttpClient as ZendeskAPI;
use Magento\Framework\Controller\ResultFactory; 
use Magento\Store\Model\StoreManagerInterface;
use GuzzleHttp\Psr7\LazyOpenStream;

class Post extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    const ZENDDESK_SUBDOMAIN = 'zenddesk/general/subdomain';
    const ZENDDESK_USERNAME = 'zenddesk/general/username';
    const ZENDDESK_API_TOKEN = 'zenddesk/general/token';

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_messageManager = $messageManager;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        return parent::__construct($context);
    }

    public function execute()
    {
        $postData = $this->getRequest()->getPostValue();
        $file = $this->getRequest()->getFiles('deposit_slip');
        $name = $postData['name'];
        $email = $postData['email'];
        $order_number = $postData['order_number'];
        $select_bank = $postData['select_bank'];
        $authorization_number = $postData['authorization_number'];
        $deposit = $postData['deposit'];
        $deposit_slip = $file['name'];
        $subject = $postData['subject'];
        $select_type = $postData['select_type'];
        $select_priority = $postData['select_priority'];

        if($deposit_slip){
            $target = $this->_mediaDirectory->getAbsolutePath('Deposit_Slip/');        
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'deposit_slip']);
            /** Allowed extension types */
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'zip', 'doc','pdf', 'csv']);
            /** rename file name if already exists */
            $uploader->setAllowRenameFiles(true);
            /** upload file in folder "Deposit_Slip" */
            $result = $uploader->save($target); 
        }

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $subdomain = $this->scopeConfig->getValue(self::ZENDDESK_SUBDOMAIN, $storeScope);
        $username = $this->scopeConfig->getValue(self::ZENDDESK_USERNAME, $storeScope);
        $token = $this->scopeConfig->getValue(self::ZENDDESK_API_TOKEN, $storeScope);
        $attachment = getcwd().'/'.$deposit_slip;
        //create ticket in zenddesk 
        $subdomain = $subdomain;
        $username = $username;
        $token = $token;
        $client = new ZendeskAPI($subdomain);
        $client->setAuth('basic', ['username' => $username, 'token' => $token]);
        try {
            $page = $this->_pageFactory->create();

            // Upload file
            $attachment = $client->attachments()->upload([
                'file' => new LazyOpenStream($attachment, 'r'),
                'type' => 'application/pdf',
                'name' => $attachment
            ]);

            // Create a new ticket with attachment
            $newTicket = $client->tickets()->create([
                    'type' => $select_type,
                    'tags'  => array('demo', 'testing', 'api', 'zendesk'),
                    'subject'  => $subject,
                    'comment'  => array(
                        'body' => '
                                    Order Number :'.$order_number.'
                                    Bank Name : '.$select_bank.'
                                    Authorization Number : '.$authorization_number.'
                                    Deposit : '.$deposit.'
                                  ',
                        'uploads' => [$attachment->upload->token]
                ),
                'requester' => array(
                    'name' => $name,
                    'email' => $email,
                ),
                'priority' => $select_priority,
            ]);
            $this->_messageManager->addSuccess(__("Successfully Created Ticket For User ".$name."."));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        } catch (\Zendesk\API\Exceptions\ApiResponseException $e) {
            return $this->_messageManager->addError($e->getMessage());
        }
    }
}