<?php
namespace Brainvire\Fps\Model;
 
use Brainvire\Fps\Model\ResourceModel\Fps\CollectionFactory;
 
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $_loadedData;
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $employeeCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $employeeCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->collection = $employeeCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
 
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        // if (isset($this->_loadedData)) {
        //     return $this->_loadedData;
        // }
        // $items = $this->collection->getItems();
        // foreach ($items as $employee) {
        //     $this->_loadedData[$employee->getId()] = $employee->getData();
        //     if($employee->getFile()){
        //         $m['file'][0]['name'] = $employee->getFile();
        //         $m['file'][0]['url'] = $this->getMediaUrl().$employee->getFile();
        //         $fullData = $this->_loadedData;
        //         $this->_loadedData[$employee->getId()] = array_merge($fullData[$employee->getId()], $m);
        //     }
        // }
        // return $this->_loadedData;
        return [];
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()
        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'employee/';
        return $mediaUrl;
    }
}