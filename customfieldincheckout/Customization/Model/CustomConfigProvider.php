<?php

namespace Brainvire\Customization\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class CustomConfigProvider implements ConfigProviderInterface
{

    /**
    * @var \Rpassembler\Assembler\Model\AllassemblerFactory
    */
    private $allassemblerFactory;

    /** @var LayoutInterface  */
    protected $_layout;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Rpassembler\Assembler\Model\ResourceModel\Allassembler\CollectionFactory $assemblerFactory,
        LayoutInterface $layout
    ) {
        $this->_resource = $resource;
        $this->assemblerFactory = $assemblerFactory;
        $this->_layout = $layout;
    }

    // public function getStates()
    // {
    //     $adapter = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
    //     $select = $adapter->select()
    //                 ->from('inventory_source');
    //     return $adapter->fetchAll($select);
    // }

    public function getConfig()
    {   
        $assembler_config = array();
        $assembler_config[] = array(
            'zipcode' => "",
            'title' => "",
            'email' => "",
            'description' => "",
            'label' => "please select city",
            'value' => ""
        );
       
        $assemblers = $this->assemblerFactory->create();
        foreach ($assemblers->getData() as $assembler) {

        $assembler_config[] = array(
            'zipcode' => $assembler['zipcode'],
            'title' => $assembler['title'],
            'email' => $assembler['email'],
            'description' => $assembler['description'],
            'label' => $assembler['city'],
            'value' => $assembler['assembler_id']
        );
            // $assembler_config[] = array(
            //     'label' => "Assembler City 2",
            //     'value' => "382330"
            // );
            // echo $assembler['assembler_id'];
            // echo $assembler['title'];
            // echo $assembler['city'];
            // echo $assembler['email'];
            // echo $assembler['description'];
            // echo $assembler['zipcode'];
        }
        // echo "<pre>";
        // //print_r($assemblers->getData());
        // exit;
        // foreach ($this->getStates() as $field) {
        //     $storepick_config[] = array(
        //         'source_code' => $field['source_code'],
        //         'name' => $field['name']
        //     );
        // }     
        $config = [
            'assembler_config' => $assembler_config,
            'assembler_config_encode' => json_encode($assembler_config),
            'custom_block' => $this->_layout->createBlock('Brainvire\Customization\Block\Customization')->setTemplate("Brainvire_Customization::custom_block.phtml")->toHtml()
        ];
        return $config;
    }


}
