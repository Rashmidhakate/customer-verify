<?php 

namespace Brainvire\Customization\Observer;
use Magento\Framework\Event\ObserverInterface;
class PaymentMethodAvailable implements ObserverInterface
{
	public function __construct(
		\Psr\Log\LoggerInterface $logger,
		\Magento\Checkout\Model\Cart $cartModel,
		\Magento\Quote\Model\QuoteRepository $quoteRepository
	)
	{
		$this->logger = $logger;
		$this->cartModel = $cartModel;
		$this->quoteRepository = $quoteRepository;
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$cart = $this->cartModel;
		$quote = $cart->getQuote();
		$quoteId = $quote->getId();
    	$event           = $observer->getEvent();
        $method          = $event->getMethodInstance();
        $result          = $event->getResult();

        $quoteData = $this->quoteRepository->get($quoteId);
        if($quote->getAssemblerId() == 1){
        	$result->setData('is_available', false); 
        }else{
        	$result->setData('is_available', true); 
        }
    }
}