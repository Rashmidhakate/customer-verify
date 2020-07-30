<?php
namespace Brainvire\Fps\Block\Adminhtml\Fps\Edit;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
/**
 * Class SaveButton
 */
class DownloadButton extends GenericButton  implements ButtonProviderInterface
{
        /**
     * Save button
     *
     * @return array
     */
    public function getButtonData()
    {
            return [
                'label' => __('Download Sample File'),
                'class' => 'download primary',
                'on_click' => "location.href = '".$this->getSaveUrl()."'",
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'download']],
                    'form-role' => 'download',
                ],
                'sort_order' => 30,
            ];
    }

     public function getSaveUrl()
    {
        return $this->getUrl('fps/index/download');
    }
}
