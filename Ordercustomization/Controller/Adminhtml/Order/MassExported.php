<?php

namespace Brainvire\Ordercustomization\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class MassDelete
 */
class MassExported extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        \Magento\Sales\Model\Order $orderModel,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Filesystem\Io\File $ioAdapter,
        \Brainvire\SalesReps\Model\ResourceModel\SalesReps\CollectionFactory $salesRepsFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->sftp = $sftp;
        $this->orderModel = $orderModel;
        $this->directoryList = $directoryList;
        $this->csvProcessor = $csvProcessor;
        $this->ioAdapter = $ioAdapter;
        $this->salesRepsFactory = $salesRepsFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countDeleteOrder = 0;
        $model = $this->orderModel;
        try {

            foreach ($collection->getItems() as $order) {
                $loadedOrder = $model->load($order->getEntityId());
                $orderData = $this->getOrderData($order->getEntityId());
                $data = $order->getEntityId()."orderId";
                $fileName = $order->getIncrementId()."_order.txt";
                $exportedFolder ="erp/export/";
                $directoryList = $this->directoryList;
                $csvProcessor = $this->csvProcessor;
                //$fileDirectoryPath = $directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)."/export/";
                $fileDirectoryPath = $directoryList->getRoot().'/'.$exportedFolder;
                $ioAdapter = $this->ioAdapter;
                if(!is_dir($fileDirectoryPath)){
                    $ioAdapter->mkdir($fileDirectoryPath, 0777, true);
                }

                $filePath =  $fileDirectoryPath . '/' . $fileName; 
                if ($this->fileDriver->isExists($filePath)) {
                    $this->fileDriver->deleteFile($filePath);
                }
                $csvProcessor
                        ->setEnclosure('"')
                        ->setDelimiter('|')
                        ->saveData($filePath, $orderData);
                //$this->saveFile($fileName,$exportedFolder);
                if($loadedOrder->getExported() && $loadedOrder->getExported() == "Exported"){
                    $loadedOrder->setExported('Re-Exported');
                    $loadedOrder->setAcknowleadge('Waiting');
                }elseif($loadedOrder->getExported() && $loadedOrder->getExported() == "Re-Exported"){
                    $loadedOrder->setExported($loadedOrder->getExported());
                    $loadedOrder->setAcknowleadge('Waiting');
                }else{
                    $loadedOrder->setExported('Exported');
                    $loadedOrder->setAcknowleadge('Waiting');
                }
                $loadedOrder->save();
            }
            $this->messageManager->addSuccess(__('You have Exported %1 order(s).', $collection->count()));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

    /**
     * @param obj $warehouse
     */
    public function saveFile($fileName, $exportedFolder)
    {
        $sftp = $this->loginSftp();
        $directoryList = $this->directoryList;
        if (!is_dir($exportedFolder))
        {   
            $sftp->mkdir($exportedFolder, 0777, false);
        }
        // $this->sftp->cd($exportedFolder);
        // $this->sftp->rm($this->sftp->pwd().$fileName);
    
        $fileDirectoryPath = $directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)."/export/";
        $content = file_get_contents($fileDirectoryPath.$fileName);
        $this->sftp->cd($exportedFolder);//the path is you will upload path
        $this->sftp->write($fileName, $content); // filename and string data or local file name
        $this->sftp->close();
    }

    /**
     * @return object $sftp
     */
    private function loginSftp()
    {
        // $host = '216.14.118.191';
        // $port = '2223';
        // $username = '4sgm';
        // $password = 'w#gVC8U9fg5s$J2#';
        
        $host = '100.20.224.196';
        $port = '22';
        $username = '4sgm';
        $password = 'yrS8&bPlt#Ow4ks';

        $this->sftp->open(
            array(
                'host' => $host.':'.$port,
                'username' => $username,
                'password' => $password
            )
        );

        return $this->sftp;
    }

    protected function getOrderData($id)
    {
        $model = $this->orderModel;
        $loadedOrder = $model->load($id);
        $salesPersonCollection = $this->salesRepsFactory->create();
        $salesPersonCollection->addFieldToFilter('sales_person_group',array('eq' => $loadedOrder->getSalesPersonCode()));
        $salesPersonCollection->getFirstItem();
        if($salesPersonCollection->getSize()){
            foreach($salesPersonCollection as $salesPerson){
                $salesPersoncode = $salesPerson->getSalesPersonCode();
            }
        }else{
            $salesPersoncode = null;
        }
        
        $customerDivision = null;
        $customerSourceCode = null;
        if($loadedOrder->getCustomerId()){
            $customer = $this->customerRepositoryInterface->getById($loadedOrder->getCustomerId());        
            $customerDivision = ($customer->getCustomAttribute('erp_division')) ? $customer->getCustomAttribute('erp_division')->getValue() : '';
            $customerSourceCode = ($customer->getCustomAttribute('customer_source_code')) ? $customer->getCustomAttribute('customer_source_code')->getValue() : 'V2003';
        }

        $shippingAddress = $loadedOrder->getShippingAddress();
        $shippingName = $shippingAddress->getFirstname()." ".$shippingAddress->getLastname();
        $street = $shippingAddress->getStreet();
        $payment = $loadedOrder->getPayment();
        if(isset($street[1])){
            $streetLine2 =  $street[1];
        }else{
            $streetLine2 = null;
        }
        if(isset($street[2])){
            $streetLine3 = $street[2];
        }else{
            $streetLine3 = null;
        }
        $result = [];
        $result[] = [
            'HHH',
            'ROENumber#',
            'Order Date',
            'Customer E-mail address',
            'Mas200 Customer Account number',
            'Ship To Name',
            'Ship To Addr1',
            'Ship To Addr2',
            'Ship To Addr3',
            'Ship To City',
            'Ship To State/Province',
            'Ship To Zip Code/Postal code',
            'Ship To Country Code',
            'Customer PO number',
            'Contact Name/Customer Name',
            'Ship Via',
            'Shipping Parameters',
            'Salesperson code',
            'Total Weight',
            'Total Cube',
            'Taxable Amount',
            'Non Taxable Amount',
            'Sales Tax Amount',
            'Freight',
            'Coupon No',
            'Discount Amount',
            'Payment Type',
            'Cardholder name',
            'Tran No',
            'Authorization amount',
            'Affiliated Number',
            'Agent Code',
            'Customer Source Code',
            'Promotion Codes',
            'Division',
            'Hazmat Order',
            'SplittedOrder',
            'MultipleOrder',
            'Master Order',
            'SingleBoxOrder',
            'PartnerDiv',
            'PartnerAccNo',
            'LiftGate',
            'CallBeforeDelivery',
            'InsideDelivery',
            'Res_Comm_LmAccess',
            'Freight Estimate',
            'Trucking Company',
            'Trucking Company Contact Name',
            'Trucking Company Phone Number',
            'Trucking Company Email'
        ];

        $result[] = [
            'HDR',
            $loadedOrder->getIncrementId(),
            $loadedOrder->getCreatedAt(),
            $loadedOrder->getCustomerEmail(),
            $loadedOrder->getCustomerAccountNumber(),
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            'LTL',
            str_replace(array("\r\n", "\n", "\r"), "/", $loadedOrder->getCustomerNote()),
            $salesPersoncode,
            $loadedOrder->getTotalWeight(),
            $loadedOrder->getTotalCube(),
            $loadedOrder->getTaxableAmount(),
            $loadedOrder->getGrandTotal(),
            $loadedOrder->getSalesTaxAmount(),
            $loadedOrder->getShippingAmount(),
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $customerSourceCode,
            null,
            $customerDivision,
            $loadedOrder->getHazmat(),
            $loadedOrder->getSplittedOrder(),
            $loadedOrder->getMulitpleOrder(),
            $loadedOrder->getMasterOrder(),
            $loadedOrder->getSingleBoxOrder(),
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        ];

         $result[] = [
            'HHH',
            'Item Number',
            'Unit of measure',
            'Quantity',
            'Unit price',
            'Extension Amount',
            'Promo code',
            'Discount Amount per unit',
            'Line Item type',
            'CP',
            'IP'
        ];

        $orderItems = $loadedOrder->getAllItems();
        foreach ($orderItems as $item)
        {
           $quantity = $item->getQtyOrdered();
           $result[] = [
             'DTL',
            $item->getSku(),
            'EACH',
            $quantity,  /* Total qyt in Pice (cp *qty + ip *qty) */
            $item->getPrice(),
            $item->getRowTotal(),
            null,
            null,
            $item->getLineItemType(),
            $item->getCp(),
            $item->getIp()  
           ];
        }

        return $result;
    }
}
