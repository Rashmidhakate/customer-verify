<?php
namespace Brainvire\Fps\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Fps extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('fps','fps_id');
    }
}

