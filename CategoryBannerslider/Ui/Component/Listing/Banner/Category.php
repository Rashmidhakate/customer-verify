<?php
namespace Brainvire\CategoryBannerslider\Ui\Component\Listing\Banner;
 
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
 

class Category extends Column
{
    protected $escaper;
    protected $systemStore;
    protected $productloader;

 
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryfactory,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->categoryfactory = $categoryfactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $category = $this->categoryfactory->create()->load((int)$item[$this->getData('name')]);
                $item[$this->getData('name')] = $category->getName();
            }
        }
        return $dataSource;
    }
}