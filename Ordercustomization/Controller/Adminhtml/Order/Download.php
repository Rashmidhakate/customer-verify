<?php 
namespace Brainvire\Ordercustomization\Controller\Adminhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;

class Download extends \Magento\Backend\App\Action 
{
    public function __construct(
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        Filesystem $filesystem,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
        $this->filesystem = $filesystem;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }
    public function execute()
    {
        $fileDirectoryPath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)."/completed/";
        $incrementId = $this->getRequest()->getParam("id");
        $fileName = $incrementId."_order.txt";
        $filePath = $fileDirectoryPath.$fileName;
        if ($this->fileDriver->isExists($filePath)) {
            return $this->fileFactory->create(
                $fileName,
                $this->fileDriver->fileGetContents($filePath),
                 DirectoryList::VAR_DIR
            );
        }
    
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw;
    }

    /**
     * @return Redirect
     */
    // private function getResultRedirect(): Redirect
    // {
    //     $resultRedirect = $this->resultRedirectFactory->create();
    //     $resultRedirect->setPath('sales/order/index');

    //     return $resultRedirect;
    // }
}