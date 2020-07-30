<?php
namespace Brainvire\Fps\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Upload extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Brainvire_Fps::fps_upload';

    protected $filesystem;
    protected $_storeManager;

    public function __construct(
        Action\Context $context,       
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Csv $csv
    )
    {        
        $this->_storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->csv = $csv;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $files = $this->getRequest()->getFiles();
        echo "<pre>";
        print_r($_FILES['file']['name']);
        exit;
        if ($data) {
            
            $model = $this->_objectManager->create('Custom\Uiform\Model\Uiform');

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
            $model->setTitle($data["title"]);
            $model->setStore(implode(',', $data["store"]));
            $model->setFile($data['file'][0]['name']);
            
            try {
                $model->save();
                $this->cacheTypeList->invalidate('full_page');
                $this->messageManager->addSuccess(__('You saved this Employee.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Attachment.'));
            }

            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

}
