<?php

namespace Brainvire\Customapi\Model\Salesperson;


/**
 * Defines the implementaiton class of the \Brainvire\Customapi\Api\CustomerInterface
 */
class Salesperson extends \Magento\Framework\Model\AbstractModel implements \Brainvire\Customapi\Api\Salesperson\SalespersonInterface {

	protected $customerCheck;
	const PAGE_LIMIT = '20';
	/**
	 *
	 * @param \Magento\Customer\Model\Customer $customerCheck
	 */
	public function __construct(
		\Brainvire\SalesReps\Model\SalesRepsFactory $SalesRepsFactory,
	 	\Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		$this->SalesRepsFactory = $SalesRepsFactory;
		$this->curlFactory = $curlFactory;
		$this->jsonHelper = $jsonHelper;
		$this->request = $request;
		$this->storeManager = $storeManager;
	}

	public function getSalespersonList($timestamp = ""){
		$resultData = [];
        $params = $this->request->getParams();
		$this->checkLoginCustomer();
		$salesPersonObj = $this->SalesRepsFactory->create()->getCollection();

		if (array_key_exists('page', $params) && array_key_exists('count', $params) && !empty($params['page']) && !empty($params['count'])) {
            $page = $params['page'];
            $count = $params['count'];
            $totalProducts = $salesPersonObj->getSize();

            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }
        
            if ($pageSize < $params['page']) {
                $Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
                $result = array(
                    "Settings" => ["Code" => "400","Message" => "No data found"],
                    "Data" => $resultData,
                    "Pagination" => $Pagination
                );              
                echo json_encode($result); exit;

            } else {
                $startLimit = ($page - 1) * $count;
                $salesPersonObj->setPageSize($count)->setCurPage($page);
            }

        }elseif (array_key_exists('count', $params) && !empty($params['count'])){
            $count = $params['count'];
            $totalProducts = $salesPersonObj->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = (1 - 1) * $count;

            $salesPersonObj->setPageSize($count)->setCurPage(1);
        }elseif (array_key_exists('page', $params) && !empty($params['page'])){
            $page = $params['page'];
            $count = self::PAGE_LIMIT;
            $totalProducts = $salesPersonObj->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = ($page - 1) * $count;
            $salesPersonObj->setPageSize($count)->setCurPage($page);
        }else{
            $page = 1;
            $count = self::PAGE_LIMIT;
            $totalProducts = $salesPersonObj->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = ($page - 1) * $count;
            $salesPersonObj->setPageSize($count)->setCurPage($page);
        }
		$salesPersonArray = [];
		if($salesPersonObj->getSize() > 0){
			foreach ($salesPersonObj as $salesPerson) {
				$salesPersonArray[] = [
                        "SalesPersonId" => strval($salesPerson->getSalesrepsId()),
						"SalesPersonCode" => strval($salesPerson->getSalesPersonCode()),
						"SalesPersonName" => strval($salesPerson->getSalesPersonName()),
						"SalesPersonDiv" => strval($salesPerson->getSalesPersonDivision()),
						"SalesPersonGroup" => strval($salesPerson->getSalesPersonGroup()),
						"SalesManagerDiv" => strval($salesPerson->getSalesManagerDivision()),
						"SalesManagerCode" => strval($salesPerson->getSalesManagerCode()),
						"UserId" => strval($salesPerson->getCustomerIds())
					];
			}
			$Pagination['pageInfo']['TotalItems'] = $totalProducts;
            $Pagination['pageInfo']['TotalPages'] = $pageSize;
			$salepersonsData = [
				"Settings" => [
					"Code" => "200",
					"Message" => "Salesperson data synced successfully",
				],
				"Data" => $salesPersonArray,
				"Pagination" => $Pagination
			];
			$ary_response[] = $salepersonsData;

        }else{
        	$Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
			$salepersonsData = [
				"Settings" => [
					"Code" => "400",
					"Message" => "We can't find salesperson matching the selection.",
					"Pagination" => $Pagination
				]
			];
			$ary_response[] = $salepersonsData;
        }
        echo json_encode($salepersonsData); exit;
	}

	public function getUserSalespersonList($userId){
		$resultData = [];
        $params = $this->request->getParams();
		$this->checkLoginCustomer();
		$salesPersonObj = $this->SalesRepsFactory->create()->getCollection();
		$salesPersonObj->addFieldToFilter('customer_ids',array('eq' => $userId));

				if (array_key_exists('page', $params) && array_key_exists('count', $params) && !empty($params['page']) && !empty($params['count'])) {
            $page = $params['page'];
            $count = $params['count'];
            $totalProducts = $salesPersonObj->getSize();

            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }
        
            if ($pageSize < $params['page']) {
                $Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
                $result = array(
                    "Settings" => ["Code" => "400","Message" => "No data found"],
                    "Data" => $resultData,
                    "Pagination" => $Pagination
                );              
                echo json_encode($result); exit;

            } else {
                $startLimit = ($page - 1) * $count;
                $salesPersonObj->setPageSize($count)->setCurPage($page);
            }

        }elseif (array_key_exists('count', $params) && !empty($params['count'])){
            $count = $params['count'];
            $totalProducts = $salesPersonObj->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = (1 - 1) * $count;

            $salesPersonObj->setPageSize($count)->setCurPage(1);
        }elseif (array_key_exists('page', $params) && !empty($params['page'])){
            $page = $params['page'];
            $count = self::PAGE_LIMIT;
            $totalProducts = $salesPersonObj->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = ($page - 1) * $count;
            $salesPersonObj->setPageSize($count)->setCurPage($page);
        }else{
            $page = 1;
            $count = self::PAGE_LIMIT;
            $totalProducts = $salesPersonObj->getSize();
            if ($totalProducts > 0) {
                $pageSize = ceil($totalProducts / $count);
            } else {
                $pageSize = 0;
            }

            $startLimit = ($page - 1) * $count;
            $salesPersonObj->setPageSize($count)->setCurPage($page);
        }
       
        $salesPersonArray = [];
		if($salesPersonObj->getSize() > 0){
			foreach ($salesPersonObj as $salesPerson) {
				$salesPersonArray[] = [
                        "SalesPersonId" => strval($salesPerson->getSalesrepsId()),
						"SalesPersonCode" => strval($salesPerson->getSalesPersonCode()),
						"SalesPersonName" => strval($salesPerson->getSalesPersonName()),
						"SalesPersonDiv" => strval($salesPerson->getSalesPersonDivision()),
						"SalesPersonGroup" => strval($salesPerson->getSalesPersonGroup()),
						"SalesManagerDiv" => strval($salesPerson->getSalesManagerDivision()),
						"SalesManagerCode" => strval($salesPerson->getSalesManagerCode())
					];
			}
			$Pagination['pageInfo']['TotalItems'] = $totalProducts;
            $Pagination['pageInfo']['TotalPages'] = $pageSize;
			$salepersonsData = [
				"Settings" => [
					"Code" => "200",
					"Message" => "Salesperson data synced successfully",
				],
				"Data" => $salesPersonArray,
				"Pagination" => $Pagination
			];
			$ary_response[] = $salepersonsData;
		}else{
			$Pagination['pageInfo'] = array('TotalItems' => 0, 'TotalPages' => 0);
			$salepersonsData = [
				"Settings" => [
					"Code" => "400",
					"Message" => "We can't find salesperson matching the selection.",
					"Pagination" => $Pagination
				]
			];
			$ary_response[] = $salepersonsData;
		}
		echo json_encode($salepersonsData); exit;
    }

	public function checkLoginCustomer(){
		$host = $this->storeManager->getStore()->getBaseUrl();
		$url = $host.'rest/V1/customers/me';
		$Pagination = [];
        $headers = array(
            'Authorization:  '. $this->request->getHeader('Authorization'),
        );
		$httpAdapter = $this->curlFactory->create();
		$httpAdapter->write(\Zend_Http_Client::GET, $url, '1.1', $headers);
		$result = $httpAdapter->read();
		$body = \Zend_Http_Response::extractBody($result);
		/* convert JSON to Array */
		$response = $this->jsonHelper->jsonDecode($body);
		if (array_key_exists('message',$response)){
            $message = "Customer is not logged in.";
            $error = (object) array();
            header('x', true, 401);

            $result = array(
                "Settings" => ["Code" => "400","Message" => strval($message)]
            );  
            echo $this->jsonHelper->jsonEncode($result); exit;
        }
	}
}
