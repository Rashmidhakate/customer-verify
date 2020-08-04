<?php
namespace PHPCuong\CustomerProfilePicture\Controller\Avatar;
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends \Magento\Framework\App\Action\Action
{
        /**
        * @var PageFactory
        */
        protected $resultPageFactory;

        /**
        * @var JsonFactory
        */
        protected $_resultJsonFactory;
        /**
        * View constructor.
        * @param Context $context
        * @param PageFactory $resultPageFactory
        * @param JsonFactory $resultJsonFactory
        */
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            JsonFactory $resultJsonFactory
        )
        {
            $this->resultPageFactory = $resultPageFactory;
            $this->_resultJsonFactory = $resultJsonFactory;
            parent::__construct($context);
        }
    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $file = $_FILES['file']['name'];
        if($_FILES['file']['name']){
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' => 'file']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            $uploader->setFilenamesCaseSensitivity(false);
             /** @var \Magento\Framework\Filesystem $filesystem */
            $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
            $path = $filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath('custom/');
            $uploader->save($path);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $fileData = preg_replace('/\s+/', '_', $file);
            $path = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
           
            $result->setData(['output' => $path."custom/".$fileData]);
            return $result;
        }
    }
}
