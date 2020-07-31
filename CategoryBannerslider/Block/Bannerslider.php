<?php
namespace Brainvire\CategoryBannerslider\Block;

class Bannerslider extends \Magento\Framework\View\Element\Template
{
	protected $sliderFactory;
	protected $_filesystem ;
	protected $_imageFactory;
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Brainvire\CategoryBannerslider\Model\BannerFactory $SliderFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Filesystem $filesystem,         
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryCollection
		)
	{
		$this->sliderFactory = $SliderFactory;
		$this->storeManager = $storeManager;
		$this->_filesystem = $filesystem;               
		$this->_imageFactory = $imageFactory; 
		$this->categoryCollection = $categoryCollection;
		parent::__construct($context);
	}

	public function getSliderCollection()
	{
		$slider = $this->sliderFactory->create();
		$collection = $slider->getCollection(); 
		$collection->addFieldToFilter('store',
			array(
				array('finset'=> array('0')),
				array('finset'=> $this->getStoreId())
			)
		);
	 	$collection->getSelect()->order('position ASC');
		return $collection;
	}

	public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()
        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'banner/upload/';
        return $mediaUrl;
    }


	public function resize($image, $width = null, $height = null)
	{
		$absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('banner/upload/').$image;
		if (!file_exists($absolutePath)) return false;
		$imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('banner/upload/resized/'.$width.'/').$image;
		if (!file_exists($imageResized)) { // Only resize image if not already exists.
		    //create image factory...
		    $imageResize = $this->_imageFactory->create();         
		    $imageResize->open($absolutePath);
		    $imageResize->constrainOnly(TRUE);         
		    $imageResize->keepTransparency(TRUE);         
		    $imageResize->keepFrame(FALSE);         
		    $imageResize->keepAspectRatio(TRUE);         
		    $imageResize->resize($width,$height);  
		    //destination folder                
		    $destination = $imageResized ;    
		    //save image      
		    $imageResize->save($destination);         
		} 
		$resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'banner/upload/resized/'.$width.'/'.$image;
		return $resizedURL;
	}

	  /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    } 

    public function getCategoryCollection($id){
    	return $this->categoryCollection->get($id);
    }
}
