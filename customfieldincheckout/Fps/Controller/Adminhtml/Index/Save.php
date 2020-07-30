<?php
namespace Brainvire\Fps\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Brainvire_Fps::fps_upload';

    protected $filesystem;
    protected $_storeManager;
    protected $csv;
    protected $_resourceConnection;
    protected $connection;
    protected $_resource;

    public function __construct(
        Action\Context $context,       
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {        
        $this->_storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->csv = $csv;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    public function execute()
    {
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $files = $this->getRequest()->getPostValue();
            
        try {
                $array = [];
                $file = $files['file'][0]['path'].$files['file'][0]['name'];
                $csvData = $this->csv->getData($file);
                foreach ($csvData as $row => $data) {
                    if ($row > 0) {
                        $title = $data[0];
                        $sku = $data[1];
                        $resolution = $data[2];
                        $quality = $data[3];
                        $game = $data[4];
                        $fps = $data[5];
                        $array[] = [
                            'title' => $title,
                            'sku' => $sku,
                            'resolution' => $resolution,
                            'quality' => $quality,
                            'game' => $game,
                            'fps' => $fps,
                        ]; 

                        $table = $this->_resourceConnection->getTableName('fps');
                        $this->_connection = $this->_resourceConnection->getConnection();
                        $data = 'SELECT * FROM  '. $table .' where title = "'.$title .'" AND sku = "'.$sku .'" AND resolution = "'.$resolution .'" AND quality = "'.$quality .'" AND game = "'.$game .'" AND fps ='.$fps ;

                        $result = $this->_connection->fetchAll($data);
                        if($result){
                            $deleteSql = 'DELETE FROM  '. $table .' where title = "'.$title .'" AND sku = "'.$sku .'" AND resolution = "'.$resolution .'" AND quality = "'.$quality .'" AND game = "'.$game .'" AND fps ='.$fps ;
                            $this->_connection->query($deleteSql);
                        }
                    }
                }   

                foreach ($array as $assemblarData) {
                    $title = $assemblarData['title'];
                    $sku = $assemblarData['sku'];
                    $resolution = $assemblarData['resolution'];
                    $quality = $assemblarData['quality'];
                    $game = $assemblarData['game'];
                    $fps = $assemblarData['fps'];

                    $table = $this->_resourceConnection->getTableName('fps');
                    $this->_connection = $this->_resourceConnection->getConnection();
                    $sql = "Insert Into " . $table . " (fps_id,title,sku,resolution,quality,game,fps) Values ('','$title','$sku','$resolution','$quality','$game','$fps')";
                    $collection = $this->_connection->query($sql);
                }
                $message =  __('Successfully imported %1 data.', count($array));
                $this->messageManager->addSuccess($message);
                return $resultRedirect->setPath('*/*/new');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Attachment.'));
            }
        //}
        return $resultRedirect->setPath('*/*/new');
    }

}
