<?php
namespace Brainvire\Fps\Model\ResourceModel\Fps;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection 
{
	protected $_idFieldName = 'fps_id';
    protected function _construct()
    {
        $this->_init('Brainvire\Fps\Model\Fps','Brainvire\Fps\Model\ResourceModel\Fps');
    }
}