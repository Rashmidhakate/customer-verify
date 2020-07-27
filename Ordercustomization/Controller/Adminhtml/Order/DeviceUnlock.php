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
class DeviceUnlock extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
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
        \Magento\Framework\Filesystem\Io\File $ioAdapter
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->sftp = $sftp;
        $this->orderModel = $orderModel;
        $this->directoryList = $directoryList;
        $this->csvProcessor = $csvProcessor;
        $this->ioAdapter = $ioAdapter;
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
        try{
            foreach ($collection->getItems() as $order) {
                $loadedOrder = $model->load($order->getEntityId());
                if($loadedOrder->getDeviceUnlock() && $loadedOrder->getDeviceUnlock() == 'No' || $loadedOrder->getDeviceUnlock() == 'no'){
                    $loadedOrder->setDeviceUnlock('Yes');
                    $loadedOrder->save();
                }
            }
            $this->messageManager->addSuccess(__('You have Changed Device Lock %1 order(s).', $collection->count()));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

}
