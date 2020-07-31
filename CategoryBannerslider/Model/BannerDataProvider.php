<?php
namespace Brainvire\CategoryBannerslider\Model;
 
use Brainvire\CategoryBannerslider\Model\ResourceModel\Banner\CollectionFactory;
 
class BannerDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $_loadedData;
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $sliderCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $sliderCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->collection = $sliderCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
 
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $slider) {
            $this->_loadedData[$slider->getBannerCategoryId()] = $slider->getData();
            
            if($slider->getImage()){
                $m['image'][0]['name'] = $slider->getImage();
                $m['image'][0]['url'] = $this->getMediaUrl().$slider->getImage();
                $fullData = $this->_loadedData;
                $this->_loadedData[$slider->getBannerCategoryId()] = array_merge($fullData[$slider->getBannerCategoryId()], $m);
            }
        }
        return $this->_loadedData;
        //return [];
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()
        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'banner/upload/';
        return $mediaUrl;
    }
}