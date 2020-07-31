<?php
namespace Brainvire\CategoryBannerslider\Model;
class Banner extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Brainvire\CategoryBannerslider\Model\ResourceModel\Banner');
    }
}

