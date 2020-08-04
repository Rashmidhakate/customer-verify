<?php
namespace Brainvire\CreateZenddeskTicket\Block;
 
class Form extends \Magento\Framework\View\Element\Template
{
 
   public function getFormAction()
   {
       return $this->getUrl('createzenddeskticket/index/post');
   }
 
}