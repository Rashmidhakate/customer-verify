<?php
namespace Brainvire\Fps\Model;
class Fps extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Brainvire\Fps\Model\ResourceModel\Fps');
    }
}

