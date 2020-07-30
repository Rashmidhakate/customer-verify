<?php

namespace Brainvire\Customization\Controller\Flow;

use Magento\Framework\App\Action\Context;

class Filter extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_coreSession;
    protected $_resourceConnection;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Rpassembler\Assembler\Model\ResourceModel\Allassembler\CollectionFactory $assemblerFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreSession = $coreSession;
        $this->helper = $helper;
        $this->resultRawFactory = $resultRawFactory;
        $this->assemblerFactory = $assemblerFactory;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    public function execute()
    {

        $response = [];
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $credentials = $this->helper->jsonDecode($this->getRequest()->getContent());
        if(isset($credentials['postcode'])){

        
        // try {
        //     $credentials = $this->helper->jsonDecode($this->getRequest()->getContent());
        // } catch (\Exception $e) {
        //     return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        // }

        try {
            $table = $this->_resourceConnection->getTableName('bv_zipcode');
            $this->_connection = $this->_resourceConnection->getConnection();
            $sql = "select assemblar_id from " . $table . " where zipcode = ".$credentials['postcode'];
            $collection = $this->_connection->fetchAll($sql);
            $array = array();
            foreach($collection as $assembler){
                $array[] = $assembler['assemblar_id'];
            }
            $array = array_unique($array);
            $assembler_config = array();
            $assembler_config[] = array(
                'zipcode' => "",
                'title' => "",
                'email' => "",
                'description' => "",
                'label' => "please select city",
                'value' => ""
            );
            $assemblers = $this->assemblerFactory->create();
            $assemblers->addFieldToFilter('assembler_id', array('in' => $array));
            // $assemblers->addFieldToFilter('zipcode',
            //     array(
            //         array('finset' => array($credentials['postcode'])),
            //     )
            //);
            if($assemblers->getData()){
                foreach ($assemblers->getData() as $assembler) {

                    $assembler_config[] = array(
                        'zipcode' => $assembler['zipcode'],
                        'title' => $assembler['title'],
                        'email' => $assembler['email'],
                        'description' => $assembler['description'],
                        'label' => $assembler['city'],
                        'value' => $assembler['assembler_id']
                    );
                }
            }else{
                $headeAssemblers = $this->assemblerFactory->create();
                $headeAssemblers->addFieldToFilter('assembler_id', array('eq' => 1));
                foreach ($headeAssemblers->getData() as $assembler) {

                    $assembler_config[] = array(
                        'zipcode' => $assembler['zipcode'],
                        'title' => $assembler['title'],
                        'email' => $assembler['email'],
                        'description' => $assembler['description'],
                        'label' => $assembler['city'],
                        'value' => $assembler['assembler_id']
                    );
                }
                //echo "not available";
            }
            
            $response['response'] = $assembler_config;
        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage(),
            ];
        } 
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
