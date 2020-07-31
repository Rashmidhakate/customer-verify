<?php
namespace Brainvire\CategoryBannerslider\Block\Adminhtml\Banner;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
/**
 * Class SaveButton
 */
class SaveButton extends GenericButton  implements ButtonProviderInterface
{
    /**
     * Save button
     *
     * @return array
     */
    public function getButtonData()
    {
            return [
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save']],
                    'form-role' => 'save',
                ],
                'sort_order' => 30,
            ];
    }

}
