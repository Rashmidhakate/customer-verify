<?php
namespace Brainvire\CategoryBannerslider\Ui\Component\Listing\Banner;
 
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
 
 
class Actions extends Column
{
     
    const URL_PATH_EDIT = 'categorybanner/banner/edit';
    const URL_PATH_DELETE = 'categorybanner/banner/delete';
 
     
    protected $urlBuilder;
 
     
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['banner_category_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'banner_category_id' => $item['banner_category_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'banner_category_id' => $item['banner_category_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete Event "${ $.$data.event_name }"'),
                                'message' => __('Are you sure you wan\'t to delete a event "${ $.$data.event_name }" record?')
                            ]
                        ]
                    ];
                }
            }
        }
 
        return $dataSource;
    }
}