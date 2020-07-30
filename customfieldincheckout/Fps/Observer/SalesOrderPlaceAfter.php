<?php
namespace Brainvire\Fps\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderPlaceAfter implements ObserverInterface
{
  
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectmanager,
    \Magento\Sales\Api\OrderRepositoryInterface $orderRepository)
    {
        $this->objectmanager = $objectmanager;
         $this->orderRepository = $orderRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        
        $quoteRepository = $this->objectmanager->create('Magento\Quote\Model\QuoteRepository');
        $quote = $quoteRepository->get($order->getQuoteId());
        if($quote->getAssemblerId()){
            $order = $observer->getEvent()->getOrder();
            $order->setAssemblerOrderId($quote->getAssemblerId());
            $order->save();
        }
        
        //$order->save();
        return $this;
    }

}