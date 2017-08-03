<?php

class Shopware_Controllers_Frontend_StylaApi extends Shopware_Controllers_Frontend_Checkout {



    public function indexAction(){
	  //$this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
    //$this->View()->extendsTemplate('frontend/custom/index.tpl');
    //$this->View()->loadTemplate('frontend/stylaapi/index.tpl');
	$this->View()->assign('someNumber', 5);
    }

    public function __call($name, $value = null) {
        echo "Invalid call"; exit;
    }

    public function double_slashes_clean($string){
    	return preg_replace("#(^|[^:])//+#", "\\1/", $string);
    }

	public function categoriesAction(){
		$resource = \Shopware\Components\Api\Manager::getResource('category');

		$limit  = $this->Request()->getParam('limit', 1000);
		$offset = $this->Request()->getParam('start', 0);
		$sort   = $this->Request()->getParam('sort', array());
		$filter = $this->Request()->getParam('filter', array());

		$result = $resource->getList($offset, $limit, $filter, $sort);

		$tree = $this->buildCategoryTree($result['data']);

		$res	= json_encode($tree, JSON_PRETTY_PRINT);
		echo $res; exit;

	}

	public function buildCategoryTree(array $elements, $parentId = 0) {
	    $branch = array();

	    foreach ($elements as $element) {
		if ($element['parentId'] == $parentId) {
		    $children = $this->buildCategoryTree($elements, $element['id']);
			$element['name']	= htmlentities($element['name']);
			$element['image']	= '';
		    if ($children) {
		        $element['children'] = $children;
		    }
			unset($element['active']);
			unset($element['position']);
			unset($element['articleCount']);
			unset($element['childrenCount']);
			unset($element['parentId']);

		    $branch[] = $element;
		}
	    }

	    return $branch;
	}



	public function productsAction(){
		$resource = \Shopware\Components\Api\Manager::getResource('article');

		$limit  = $this->Request()->getParam('limit', 1000);
		$offset = $this->Request()->getParam('offset', 0);
		$sort   = $this->Request()->getParam('sort', array());
		//$filter = $this->Request()->getParam('filter', array());
		$categoryId = $this->Request()->getParam('category', '');
		$search = $this->Request()->getParam('search', '');

		$term = trim(stripslashes(html_entity_decode($search)));
		$doSearch = (!$term || strlen($term) < Shopware()->Config()->MinSearchLenght) ? false : true;

		if($categoryId > 0 && $doSearch){
			$sql = "SELECT a.id, a.name
				FROM `s_articles` a
				LEFT JOIN `s_articles_categories_ro` acr ON acr.articleID = a.id
				LEFT JOIN `s_categories` c ON acr.articleID = c.id
				LEFT JOIN `s_articles_supplier` asp ON a.supplierID = asp.id
        LEFT JOIN `s_articles_details` sad ON sad.articleID = a.id
				WHERE acr.categoryID = $categoryId
					AND (a.name LIKE '%".$term."%'
          OR a.id LIKE '%".$term."%'
					OR a.description LIKE '%".$term."%'
					OR asp.name LIKE '%".$term."%'
					OR c.description LIKE '%".$term."%'
          OR sad.ordernumber LIKE '%".$term."%')
				GROUP BY a.id";

			$filterArticles = Shopware()->Db()->fetchAll($sql);
			$filter['id'] = array_column($filterArticles, 'id');
			//echo '<pre>'; print_r($filter['id']); exit;

		} else if($categoryId > 0){
			$sql = "SELECT a.id, a.name
				FROM `s_articles_categories_ro` acr
				LEFT JOIN `s_articles` a ON acr.articleID = a.id
				WHERE acr.categoryID = $categoryId ";

			$categoryArticles = Shopware()->Db()->fetchAll($sql);
			$articleIds = array_column($categoryArticles, 'id');

			$filter['id'] = $articleIds;

		} else if($doSearch){

			$sql = "SELECT a.id, a.name
				FROM `s_articles` a
				LEFT JOIN `s_articles_supplier` asp ON a.supplierID = asp.id
				LEFT JOIN `s_articles_categories_ro` acr ON acr.articleID = a.id
				LEFT JOIN `s_categories` c ON acr.articleID = c.id
        LEFT JOIN `s_articles_details` sad ON sad.articleID = a.id
				WHERE a.name LIKE '%".$term."%'
          OR a.id LIKE '%".$term."%'
					OR a.description LIKE '%".$term."%'
					OR asp.name LIKE '%".$term."%'
					OR c.description LIKE '%".$term."%'
          OR sad.ordernumber LIKE '%".$term."%'
				GROUP BY a.id";

			$searchArticles = Shopware()->Db()->fetchAll($sql);
			$filter['id'] = array_column($searchArticles, 'id');
			//echo '<pre>'; print_r($filter['id']); exit;
		}


		//echo '<pre>'; print_r($filter); exit;
		$result = $resource->getList($offset, $limit, $filter, $sort, array(
		    'language' => $this->Request()->getParam('language')
		));

		$articles = Shopware()->Modules()->Articles();

		$res = array();
		foreach($result['data'] as $key => $value){
			$imgRes = $articles->getArticleListingCover($value['id']);

			$res[] = array(
					'shopId' => $value['id'],
					'caption' => htmlentities($value['name']),
					'image' => $imgRes['src']['original'],
					'imageSmall' => $imgRes['src'][0],
					'pageUrl' => $this->double_slashes_clean($this->getLinksOfProduct($value['id'], htmlentities($value['name']))),
					'shop' => ($value['active'] ? 'true' : 'false'));
		}
		//echo '<pre>'; print_r($res); exit;
		$res	= json_encode($res, JSON_PRETTY_PRINT);
		echo $res; exit;

	}

	//	copied from /engine/Shopware/Core/sArticles.php
	private function getLinksOfProduct($productId, $productName, $categoryId = null){
		$config = Shopware()->Container()->get('config');
		$baseFile = $config->get('baseFile');

		$detail = $baseFile . "?sViewport=detail&sArticle=" . $productId;
		if($categoryId) {
			$detail .= '&sCategory=' . $categoryId;
		}

		$rewrite = Shopware()->Modules()->Core()->sRewriteLink($detail, $productName);
		return $rewrite;
	}

	public function productAction(){
		$resource = \Shopware\Components\Api\Manager::getResource('article');

		$id = $this->Request()->getParam('id');
		$useNumberAsId = (boolean) $this->Request()->getParam('useNumberAsId', 0);

		if ($useNumberAsId) {
		    $article = $resource->getOneByNumber($id, array(
		        'language' => $this->Request()->getParam('language'),
		        'considerTaxInput' => $this->Request()->getParam('considerTaxInput'),
		    ));
		} else {
		    $article = $resource->getOne($id, array(
		        'language' => $this->Request()->getParam('language'),
		        'considerTaxInput' => $this->Request()->getParam('considerTaxInput')
		    ));
		}

		$taxInclPrice = $article['mainDetail']['prices'][0]['price'] + ($article['mainDetail']['prices'][0]['price'] * ($article['tax']['tax'] / 100));
		setlocale(LC_MONETARY,"de_DE");
		$priceFormatted = money_format("%.2n", $taxInclPrice);
		$oldPrice = '';
		if($article['mainDetail']['prices'][0]['pseudoPrice'] > 0){
			$taxInclOldPrice = $article['mainDetail']['prices'][0]['pseudoPrice'] + ($article['mainDetail']['prices'][0]['pseudoPrice'] * ($article['tax']['tax'] / 100));
			setlocale(LC_MONETARY,"de_DE");
			$oldPriceFormatted = money_format("%.2n", $taxInclOldPrice);
		}
		$res = array('id' => $article['mainDetail']['number'], //$article['id']
				'name' => htmlentities($article['name']),
				'description' => $article['description'],
				'categories' => array_column($article['categories'], 'id'),
				'saleable' => ($article['active'] && ($article['mainDetail']['inStock'] > 0 || !$article['lastStock'] )) ? 'true' : 'false',
				'price' => $priceFormatted,
				'priceTemplate' => '# {price} &euro;',
				'oldPrice' => $oldPriceFormatted,
				'minqty' => $article['mainDetail']['minPurchase'],
				'maxqty' => ($article['mainDetail']['maxPurchase'] > 0) ? $article['mainDetail']['maxPurchase'] : 100,
				'tax' => array(
					'rate' => $article['tax']['tax'], 'label' => $article['tax']['name'], 'taxIncluded' => 'true', 'showLabel' => 'true'
				),
			);

		foreach($article['configuratorSet']['groups'] as $variant) {
			$res['attributes'][ $variant['id'] ] =  array(
						'id' => $variant['id'],
						'label' => htmlentities($variant['name'])
						);

			foreach($article['mainDetail']['configuratorOptions'] as $m_option){
				if($variant['id'] == $m_option['groupId']){
					//$rr = (array)$res['attributes'][ $variant['id'] ]['options'][ $m_option['id'] ]['products'];
					$saleable = ($article['mainDetail']['inStock'] > 0 || !$article['lastStock']) ? 'true' : 'false';
					$res['attributes'][ $variant['id'] ]['options'][ $m_option['id'] ] = array(
						'id' => $m_option['id'],
						'label' => htmlentities($m_option['name']),
						'price' => $priceFormatted,
						//'products' => array_merge($rr, array($article['mainDetail']['number']))
						'products' => array( 0 => array('id' => $article['mainDetail']['number'], 'saleable' => $saleable))
					);
				}
			}
			unset($m_option);

			foreach($article['details'] as $configuration){
		        $m_option = $configuration['configuratorOptions'][0];
		        if($variant['id'] == $m_option['groupId']){
		        $rrr = (array)$res['attributes'][ $variant['id'] ]['options'][ $m_option['id'] ]['products'];
		        $saleable = ($configuration['inStock'] > 0 || !$article['lastStock']) ? 'true' : 'false';
		        $res['attributes'][ $variant['id'] ]['options'][ $m_option['id'] ] = array(
		            'id' => $m_option['id'],
		            'label' => htmlentities($m_option['name']),
		            'price' => (string) number_format(($configuration['prices'][0]['price'] * ($article['tax']['tax'] / 100 + 1 )), 2, '.', ''),
					//'products' => array_merge($rrr, array($configuration['number']))
		            'products' => array_merge($rrr, array(0 => array('id' => $configuration['number'], 'saleable' => $saleable, 'price' => (string) number_format(($configuration['prices'][0]['price'] * ($article['tax']['tax'] / 100 + 1 )), 2, '.', ''))))
		        );
		        }
			}

		}

		$res	= json_encode($res, JSON_PRETTY_PRINT);
		echo $res; exit;

	}

	public function cartAddAction(){
		$orderNumber = $this->Request()->getParam('id');
        	$quantity = $this->Request()->getParam('qty');

		$quantity = ($quantity > 0) ? $quantity : 1;

		$this->View()->assign(
		    'basketInfoMessage',
		    $this->getInstockInfo($orderNumber, $quantity)
		);

		$basket = Shopware()->Modules()->Basket();
		$basket->sAddArticle($orderNumber, $quantity);

		$this->ajaxCartAction();
	}

	public function cartUpdateAction(){

		$this->ajaxCartAction();
	}

}
