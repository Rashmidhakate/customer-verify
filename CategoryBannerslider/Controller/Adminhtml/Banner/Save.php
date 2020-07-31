<?php
namespace Brainvire\CategoryBannerslider\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Backend\App\Action
{
    protected $cacheTypeList;
    protected $jsHelper;

    const ADMIN_RESOURCE = 'Brainvire_CategoryBannerslider::save';
    protected $adapterFactory;
    protected $uploader;
    protected $filesystem;


    protected $_filesystem;
    protected $_storeManager;
    protected $_directory;
    protected $_imageFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Brainvire\CategoryBannerslider\Model\Banner $bannerModel
    )
    {        
        $this->_storeManager = $storeManager;
        $this->bannerModel = $bannerModel;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    public function execute()
    {   
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $position = $data["position"];
            $status = $data["status"];
            $store = implode(',', $data["store"]);
            $category_id = $data["category_id"];
            $image = $data["image"][0]["name"];
            $id = $this->getRequest()->getParam('banner_category_id');
            $model = $this->bannerModel;
            if ($id) {
                $model->load($id);
            }
            $model->setPosition($position);
            $model->setStatus($status);
            $model->setStore($store);
            $model->setCategoryId($category_id);
            $model->setImage($image);
            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this Banner.'));
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('categorybanner/banner/edit', ['banner_category_id' => $model->getBannerCategoryId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Attachment.'));
            }
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    
    }


}
