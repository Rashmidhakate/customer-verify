<?php
namespace Brainvire\Fps\Block\Adminhtml\Fps\Edit;
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

     public function getSaveUrl()
    {
        return $this->getUrl('fps/index/save');
    }
}
