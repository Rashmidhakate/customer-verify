<?php
namespace Brainvire\Ordercustomization\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Escaper;
use \Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
 
class Acknowledge extends Column
{
	protected $_orderRepository;
	protected $_searchCriteria;
    const PRODUCT_URL_PATH_DOWNLOAD = 'ordercustomization/order/download';
 
	public function __construct(
    	PriceCurrencyInterface $priceCurrency,
    	ContextInterface $context,
    	UiComponentFactory $uiComponentFactory,
    	OrderRepositoryInterface $orderRepository,
    	SearchCriteriaBuilder $criteria,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        Escaper $escaper,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        UrlInterface $urlBuilder,
    	array $components = [],
    	array $data = [])
	{
    	$this->_orderRepository = $orderRepository;
    	$this->_searchCriteria  = $criteria;
    	$this->priceCurrency = $priceCurrency;
        $this->resourceConnection = $resourceConnection;
        $this->escaper = $escaper;
        $this->directoryList = $directoryList;
        $this->urlBuilder = $urlBuilder;
    	parent::__construct($context, $uiComponentFactory, $components, $data);
	}
	public function prepareDataSource(array $dataSource)
	{
    	if (isset($dataSource['data']['items'])) {
        	foreach ($dataSource['data']['items'] as & $item) {
            	$order  = $this->_orderRepository->get($item["entity_id"]);
                $incrementId = $order->getData("increment_id");
                if($order->getData("acknowleadge")){
                    if($order->getData("acknowleadge") == 'ERROR' || $order->getData("acknowleadge") == 'error' || $order->getData("acknowleadge") == 'Error'){
                        $html = '<a href="'.$this->urlBuilder->getUrl(self::PRODUCT_URL_PATH_DOWNLOAD, ['id' => $incrementId]).'" target="_blank"> <span class="grid-severity-critical"><span>'. $order->getData("acknowleadge") . '</span></span> </a> ';
                    }else{
                        $html = '<span class="grid-severity-notice"><span>'. $order->getData("acknowleadge") . '</span></span>';
                    }
                    
                    $item[$this->getData('name')] = $this->escaper->escapeHtml($html, ['a','span']);
                    //$item[$this->getData('name')] = html_entity_decode('<span class="grid-severity-notice"><span>'.$order->getData("acknowleadge") . '</span></span>');
               }
        	}
    	}
    	return $dataSource;
	}
}