<?php

namespace Brainvire\Fps\Controller\Adminhtml\Index;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;

class Download extends \Magento\Backend\App\Action
{
	    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\ImportExport\Model\Import\SampleFileProvider $sampleFileProvider
     * @param ComponentRegistrar $componentRegistrar
     * @param \Magento\ImportExport\Model\Import\SampleFileProvider|null $sampleFileProvider
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Component\ComponentRegistrar $componentRegistrar,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct(
            $context
        );
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->assetRepo = $assetRepo;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->resourceConnection = $resourceConnection;
    }


    public function execute()
    {
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Brainvire_Fps');
        $filePath = $this->getPath($path);
        $stream = $this->directory->openFile($path."/".$filePath, 'w+');
        /* Open file */
        //$stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $heading = [
            __('title'),
            __('sku'),
            __('resolution'),
            __('quality'),
            __('game'),
            __('fps'),
        ];
        /* Write Header */
        $stream->writeCsv($heading);

        $table = $this->resourceConnection->getTableName('fps');
        $this->_connection = $this->resourceConnection->getConnection();
        $data = 'SELECT * FROM  '. $table ;
        $collection = $this->_connection->fetchAll($data);
        foreach($collection as $fps){
            $itemData = [
                $fps['title'],
                $fps['sku'],
                $fps['resolution'],
                $fps['quality'],
                $fps['game'],
                $fps['fps'],
            ];
            $stream->writeCsv($itemData);
        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $path."/".$filePath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'FPS.csv';
        return $this->fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
		/** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
		$resultRaw = $this->resultRawFactory->create();
		return $resultRaw;
	}
    /* Header Columns */
    public function getColumnHeader() {
        $heading = [
            __('title'),
            __('sku'),
            __('resolution'),
            __('quality'),
            __('game'),
            __('fps'),
        ];
        return $heading;
    }

	 /**
     * Returns the Size for the given file associated to an Import entity
     *
     * @param string $entityName
     * @throws NoSuchEntityException
     * @return int|null
     */
    public function getSize($path)
    {
        $directoryRead = $this->getDirectoryRead($path);
        $filePath = $this->getPath($path);
        $fileSize = isset($directoryRead->stat($filePath)['size'])
            ? $directoryRead->stat($filePath)['size'] : null;

        return $fileSize;
    }

	/**
     * @param string $entityName
     * @return ReadInterface
     */
    private function getDirectoryRead($path)
    {
        $directoryRead = $this->readFactory->create($path);
        return $directoryRead;
    }

	/**
     * @return string $entityName
     * @throws NoSuchEntityException
     */
    private function getPath($path)
    {
    	$directoryRead = $this->getDirectoryRead($path);
        $fileAbsolutePath = $path . '/Files/Sample/FPS.csv';

        $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

        if (!$directoryRead->isFile($filePath)) {
            throw new NoSuchEntityException(__("There is no file: %file", ['file' => $filePath]));
        }

        return $filePath;
    }

    /**
     * Returns Content for the given file associated to an Import entity
     *
     * @param string $entityName
     * @throws NoSuchEntityException
     * @return string
     */
    public function getFileContents($path)
    {
        $directoryRead = $this->getDirectoryRead($path);
        $filePath = $this->getPath($path);

        return $directoryRead->readFile($filePath);
    }

}

?>