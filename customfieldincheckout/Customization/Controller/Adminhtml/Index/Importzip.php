<?php

namespace Brainvire\Customization\Controller\Adminhtml\Index;

class Importzip extends \Magento\Backend\App\Action
{

    protected $csv;
    protected $_resourceConnection;
    protected $connection;
    protected $_resource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\File\Csv $csv
    ) {
        $this->csv = $csv;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    public function execute()
    {
       $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $allowed = array('csv');
            $filename = $_FILES['zipcode']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $count = 0;
            $array = [];
            if (in_array($ext, $allowed)) {
                $files = $this->getRequest()->getFiles();
                $csvData = $this->csv->getData($files['zipcode']['tmp_name']);
                foreach ($csvData as $row => $data) {
                    if ($row > 0) {
                        $assemblar_id = $data[0];
                        $zipcode = $data[1];
                        $array[] = [
                            'assemblar_id' => $assemblar_id,
                            'zipcode' => $zipcode,
                        ]; 
                        if(is_numeric($zipcode)){
                            $table = $this->_resourceConnection->getTableName('bv_zipcode');
                            $this->_connection = $this->_resourceConnection->getConnection();
                            $data = "SELECT * FROM " . $table . " where assemblar_id = ".$assemblar_id;
                            $result = $this->_connection->fetchAll($data);
                            if($result){
                                $deleteSql = "Delete FROM " . $table." Where assemblar_id = ".$assemblar_id;
                                $this->_connection->query($deleteSql);

                            }
                        }
                    }
                }
                foreach ($array as $assemblarData) {
                    $assemblar_id = $assemblarData['assemblar_id'];
                    $zipcode = $assemblarData['zipcode'];
                    $table = $this->_resourceConnection->getTableName('bv_zipcode');
                    $this->_connection = $this->_resourceConnection->getConnection();
                    $sql = "Insert Into " . $table . " (id,zipcode,assemblar_id) Values ('','$zipcode','$assemblar_id')";
                    $collection = $this->_connection->query($sql);
                }
            
                $this->messageManager->addSuccess('File has been uploaded.');
                return $resultRedirect->setPath('*/*/');
            } else {
                $this->messageManager->addError('File type is not allowded.');
                return $resultRedirect->setPath('*/*/');
            }

        } catch (\Exception $e) {
            $this->messageManager->addError(
                __($e->getMessage())
            );
        }

    }
}