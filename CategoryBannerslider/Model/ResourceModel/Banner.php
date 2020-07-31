<?php
namespace Brainvire\CategoryBannerslider\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Banner extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('manage_category_banner','banner_category_id');
    }
}

