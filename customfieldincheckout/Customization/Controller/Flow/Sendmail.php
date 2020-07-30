<?php
namespace Brainvire\Customization\Controller\Flow;
 
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory; 
 
class Sendmail extends Action
{
 
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
 
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
    

    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
 
    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context, 
        \Magento\Catalog\Model\ProductFactory $productFactory,
        PageFactory $resultPageFactory, 
        JsonFactory $resultJsonFactory,
        ResultFactory $resultFactory,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Rpassembler\Assembler\Model\AllassemblerFactory $assemblerFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->productFactory = $productFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->resultFactory = $resultFactory;
        $this->storemanager =  $storemanager;
        $this->currencyFactory =  $currencyFactory;
        $this->priceHelper =  $priceHelper;
        $this->cartModel =  $cartModel;
        $this->formKey =  $formKey;
        $this->quoteRepository =  $quoteRepository;
        $this->assemblerFactory = $assemblerFactory;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }
 
 
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $customerEmail = $params['email'];
        $customerName = $params['name'];
        $sender = [];
        if($customerEmail && $customerName){
             $sender = [
                'name' => $customerName,
                'email' => $customerEmail,
            ];
        }else{
            $customerSession = $this->customerSession;
            if($customerSession->isLoggedIn()) {
                $customer = $customerSession->getCustomer();

                $sender = [
                    'name' => $customer->getName(),
                    'email' => $customer->getEmail(),
                ];
            }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $cart = $this->cartModel;
        $quote = $cart->getQuote();
        $assemblers = $this->assemblerFactory->create();
        $quoteData = $this->quoteRepository->get($quote->getId());
        $headAssembler = $assemblers->load($quote->getAssemblerId());
        $email = $headAssembler->getEmail();
        $title = $headAssembler->getTitle();
        $message = '';
        try
        {
            $this->_inlineTranslation->suspend();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $to = $email;

           
            $transport = $this->_transportBuilder
            ->setTemplateIdentifier('assembler_mail_template')
            ->setTemplateOptions(
                [
                    'area' => 'frontend',
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars(
               $sender
            )
            ->setFrom($sender)
            ->addTo($to)
            ->getTransport();

            $transport->sendMessage();

            $this->_inlineTranslation->resume();
            //$this->messageManager->addSuccessMessage( __('Thanks for contacting us with your comments and questions. We will respond to you very soon.'));
            $message = 'Thanks for contacting us with your comments and questions. We will respond to you very soon.';
        } catch (\Exception $e) {
            //$this->messageManager->addErrorMessage($e->getMessage());
            $message = $e->getMessage();
        }

        $data = array(
            'message' => $message
        );
        $resultRedirect->setData(['output' => $data]);
        return $resultRedirect;
    }
 
}