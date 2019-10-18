<?php

class Shopware_Controllers_Frontend_Stylaapi extends Shopware_Controllers_Frontend_Checkout
{

    public function indexAction()
    {
        $this->View()->assign('someNumber', 5);
    }

    public function __call($name, $value = null)
    {
        echo "Invalid call";
        exit;
    }

    public function double_slashes_clean($string)
    {
        return preg_replace("#(^|[^:])//+#", "\\1/", $string);
    }

    public function categoriesAction()
    {
        $resource = \Shopware\Components\Api\Manager::getResource('category');

        $limit = $this->Request()->getParam('limit', 1000);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', array());
        $filter = $this->Request()->getParam('filter', array());

        $result = $resource->getList($offset, $limit, $filter, $sort);

        $tree = $this->buildCategoryTree($result['data']);

        $res = json_encode($tree, JSON_PRETTY_PRINT);
        echo $res;
        exit;

    }

    public function buildCategoryTree(array $elements, $parentId = 0)
    {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parentId'] == $parentId) {
                $children = $this->buildCategoryTree($elements, $element['id']);
                $element['name'] = htmlentities($element['name']);
                $element['image'] = '';
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


    public function productsAction()
    {
        $resource = \Shopware\Components\Api\Manager::getResource('article');

        $limit = $this->Request()->getParam('limit', 1000);
        $offset = $this->Request()->getParam('offset', 0);
        $sort = $this->Request()->getParam('sort', array());
        $filter = $this->Request()->getParam('filter', array());
        $categoryId = $this->Request()->getParam('category', '');
        $search = $this->Request()->getParam('search', '');
        $imagesMethod = $this->Request()->getParam('images', 'v1'); // determine wich method to use for media. default: v1

        $term = trim(stripslashes(html_entity_decode($search)));
        $doSearch = (!$term || strlen($term) < Shopware()->Config()->MinSearchLenght) ? false : true;

        if ($categoryId > 0 && $doSearch) {
            $sql = "SELECT a.id, a.name
				FROM `s_articles` a
				LEFT JOIN `s_articles_categories_ro` acr ON acr.articleID = a.id
				LEFT JOIN `s_categories` c ON acr.articleID = c.id
				LEFT JOIN `s_articles_supplier` asp ON a.supplierID = asp.id
        LEFT JOIN `s_articles_details` sad ON sad.articleID = a.id
				WHERE acr.categoryID = $categoryId
					AND (a.name LIKE '%" . $term . "%'
          OR a.id LIKE '%" . $term . "%'
					OR a.description LIKE '%" . $term . "%'
					OR asp.name LIKE '%" . $term . "%'
					OR c.description LIKE '%" . $term . "%'
          OR sad.ordernumber LIKE '%" . $term . "%')
				GROUP BY a.id";

            $filterArticles = Shopware()->Db()->fetchAll($sql);
            $filter['id'] = array_column($filterArticles, 'id');

        } else if ($categoryId > 0) {
            $sql = "SELECT a.id, a.name
				FROM `s_articles_categories_ro` acr
				LEFT JOIN `s_articles` a ON acr.articleID = a.id
				WHERE acr.categoryID = $categoryId ";

            $categoryArticles = Shopware()->Db()->fetchAll($sql);
            $articleIds = array_column($categoryArticles, 'id');

            $filter['id'] = $articleIds;

        } else if ($doSearch) {

            $sql = "SELECT a.id, a.name
				FROM `s_articles` a
				LEFT JOIN `s_articles_supplier` asp ON a.supplierID = asp.id
				LEFT JOIN `s_articles_categories_ro` acr ON acr.articleID = a.id
				LEFT JOIN `s_categories` c ON acr.articleID = c.id
        LEFT JOIN `s_articles_details` sad ON sad.articleID = a.id
				WHERE a.name LIKE '%" . $term . "%'
          OR a.id LIKE '%" . $term . "%'
					OR a.description LIKE '%" . $term . "%'
					OR asp.name LIKE '%" . $term . "%'
					OR c.description LIKE '%" . $term . "%'
          OR sad.ordernumber LIKE '%" . $term . "%'
				GROUP BY a.id";

            $searchArticles = Shopware()->Db()->fetchAll($sql);
            $filter['id'] = array_column($searchArticles, 'id');
        }

        $result = $resource->getList($offset, $limit, $filter, $sort, array(
            'language' => $this->Request()->getParam('language')
        ));

        $articles = Shopware()->Modules()->Articles();

        $res = array();

        foreach ($result['data'] as $key => $value) {
            if (!$value['active']) {
                continue;
            }

            try {
                $mainImg = $articles->getArticleListingCover($value['id']);
                $additionalImages = $articles->sGetArticlePictures($value['id'], false, 0, null, true);
                $articleDetails = $articles->sGetArticleById($value['id']);
                $imagesArr = array();
                $imagesArr[0] = $mainImg['src']['original'];

                if (is_array($additionalImages)){
                    foreach ($additionalImages as $image) {
                        $imagesArr[] = $image['src']['original'];
                    }
                }

                // Alternative method to get images - may solve issues for clients with custom implementation for Media
                $imagesNewArr = array();

                $imagesNewArr[] = $articleDetails['image']['source'];

                if ($articleDetails['sConfigurator']){
                    foreach ($articleDetails['sConfigurator'] as $variant) {
                        if (!$variant['values']) {
                            continue;
                        }

                        foreach ($variant['values'] as $singleValue) {
                            if (!$singleValue['media']['source']) {
                                continue;
                            }

                            if (in_array($singleValue['media']['source'], $imagesNewArr)){
                                continue;
                            }

                            $imagesNewArr[] = $singleValue['media']['source'];
                        }
                    }
                }

                switch ($imagesMethod) {
                    case 'v2': //new method for images
                        $defImages = $imagesNewArr;
                        break;

                    case 'v3': //v1 and v2 combined
                        $defImages = array_values(array_unique(array_merge($imagesArr, $imagesNewArr)));
                        break;

                    case 'v1': //old method for images (default)
                    default:
                        $defImages = $imagesArr;
                        break;
                }

                $res[] = array(
                    'shopId' => $value['id'],
                    'sku' => $articleDetails['ordernumber'],
                    'caption' => htmlentities($value['name']),
                    'images' => $defImages,
                    'pageUrl' => $this->double_slashes_clean($this->getLinksOfProduct($value['id'], htmlentities($value['name']))),
                    'shop' => ($value['active'] ? 'true' : 'false'));
            } catch (Exception $e) {
                error_log("Failed to retrieve article information for id " . $value['id'] . " : " . $e->getMessage());
            }
        }

        $res = json_encode($res, JSON_PRETTY_PRINT);
        echo $res;
        exit;

    }

    //	copied from /engine/Shopware/Core/sArticles.php
    private function getLinksOfProduct($productId, $productName, $categoryId = null)
    {
        $config = Shopware()->Container()->get('config');
        $baseFile = $config->get('baseFile');

        $detail = $baseFile . "?sViewport=detail&sArticle=" . $productId;

        if ($categoryId) {
            $detail .= '&sCategory=' . $categoryId;
        }

        return Shopware()->Modules()->Core()->sRewriteLink($detail, $productName);
    }

    public function getCurrency()
    {
        $shop = Shopware()->Shop();
        return $shop->getCurrency()->toArray();
    }

    public function throwErr($error)
    {
        $message['error'] = $error;
        $message['saleable'] = false;

        echo json_encode($message);

        exit;
    }

    public function getEKPrices($detail, $currencyInfo) {
        $productEKPrice = null;
        $productEKPseudoPrice = null;

        foreach ( $detail['prices'] as $price ) {
            if ($price['customerGroupKey'] !== 'EK') {
                continue;
            }

            if (
                !array_key_exists('customerGroup', $price)
                || $price['customerGroup']['key'] !== 'EK'
            ) {
                continue;
            }

            $productEKPrice = $price['price'];
            $productEKPseudoPrice = $price['pseudoPrice'];
        }

        // factor might be 0 or 1 which means its the base price
        $factor = $currencyInfo['factor'];
        $factor = $factor == 0 ? 1 : $factor;

        return array_merge(
            [ "price" => $productEKPrice * $factor ],
            $productEKPseudoPrice ? [ "pseudoPrice" => $productEKPseudoPrice * $factor ] : []
        );
    }

    public function productAction()
    {
        $resource = \Shopware\Components\Api\Manager::getResource('article');

        $currencyInfo = $this->getCurrency();

        $id = $this->Request()->getParam('id');
        $useNumberAsId = (boolean)$this->Request()->getParam('useNumberAsId', 0);

        $article = [];

        if ($useNumberAsId) {
            try {
                $article = $resource->getOneByNumber($id, [
                    'language' => $this->Request()->getParam('language'),
                    'considerTaxInput' => $this->Request()->getParam('considerTaxInput')
                ]);
            } catch (Exception $e) {
                $this->throwErr($e->getMessage());
            }
        } else {
            try {
                $article = $resource->getOne($id, [
                    'language' => $this->Request()->getParam('language'),
                    'considerTaxInput' => $this->Request()->getParam('considerTaxInput')
                ]);
            } catch (Exception $e) {
                $this->throwErr($e->getMessage());
            }
        }

        $ekPrices = $this->getEKPrices($article['mainDetail'], $currencyInfo);
        $productEKPrice = $ekPrices['price'];
        $productEKPseudoPrice = $ekPrices['pseudoPrice'];

        /*
            This point will be undefined behavior if no EK price exists.
        */

        $taxRate = $article['tax']['tax'] / 100;

        $taxInclPrice = $productEKPrice * ($taxRate + 1);

        $priceFormatted = money_format("%.2n", $taxInclPrice);
        $oldPriceFormatted = '';

        if ($productEKPseudoPrice > 0) {
            $taxInclOldPrice = $productEKPseudoPrice * ($taxRate + 1);
            $oldPriceFormatted = money_format("%.2n", $taxInclOldPrice);
        }

        switch ($currencyInfo['position']) {
            case '16':
                $priceTemplate = '#{price} ' . $currencyInfo['symbol'];
                break;

            case '32':
            default:
                $priceTemplate = $currencyInfo['symbol'] . ' #{price}';
                break;
        }

        $isActive = $article['active'];
        $hasStock = $article['mainDetail']['inStock'] > 0;

        $isLastStock = $article['lastStock'];

        $minPurchase = $article['mainDetail']['minPurchase'];
        $maxPurchase = $article['mainDetail']['maxPurchase'];

        $res = array_merge(
            [
                'id' => $article['mainDetail']['number'],
                'name' => htmlentities($article['name']),
                'description' => $article['description'],
                'categories' => array_column($article['categories'], 'id'),
                'saleable' => ($isActive && ($hasStock || !$isLastStock)) ? 'true' : 'false',
                'price' => $priceFormatted,
                'priceTemplate' => $priceTemplate,
                'minqty' => $minPurchase,
                'maxqty' => ($maxPurchase > 0) ? $maxPurchase : 100,
                'tax' => [
                    'rate' => $article['tax']['tax'],
                    'label' => $article['tax']['name'],
                    'taxIncluded' => 'true',
                    'showLabel' => 'true'
                ],
            ],
            $oldPriceFormatted ? [ 'oldPrice' => $oldPriceFormatted ] : []
        );

        foreach ($article['configuratorSet']['groups'] as $variant) {
            $res['attributes'][$variant['id']] = array(
                'id' => $variant['id'],
                'label' => htmlentities($variant['name'])
            );

            foreach ($article['mainDetail']['configuratorOptions'] as $m_option) {
                if ($variant['id'] != $m_option['groupId']) {
                    continue;
                }

                $isActive = $article['mainDetail']['active'];
                $hasStock = $article['mainDetail']['inStock'] > 0;

                $isLastStock = $article['lastStock'];

                $saleable = $isActive && ($hasStock || !$isLastStock) ? 'true' : 'false';

                $res['attributes'][$variant['id']]['options'][$m_option['id']] = array(
                    'id' => $m_option['id'],
                    'label' => htmlentities($m_option['name']),

                    'products' => [
                        0 => array_merge(
                            [
                                'id' => $article['mainDetail']['number'],
                                'saleable' => $saleable,
                                'price' => $priceFormatted,
                            ],
                            $oldPriceFormatted ? [ 'oldPrice' => $oldPriceFormatted ] : []
                        )
                    ]
                );
            }

            unset($m_option);

            foreach ($article['details'] as $configuration) {
                foreach ($configuration['configuratorOptions'] as $m_option) {
                    if ($variant['id'] != $m_option['groupId']) {
                        continue;
                    }

                    $isActive = $configuration['active'];
                    $hasStock = $configuration['inStock'] > 0;

                    $isLastStock = $article['lastStock'];

                    $saleable = $isActive && ($hasStock || !$isLastStock) ? 'true' : 'false';

                    $variantId = $variant['id'];
                    $optionId = $m_option['id'];

                    $variantOptionProducts = (array) $res['attributes'][$variantId]['options'][$optionId]['products'];

                    $ekPrices = $this->getEKPrices($configuration, $currencyInfo);

                    $formattedOptionPrice = (string)number_format(
                        $ekPrices['price'] * ($taxRate + 1),
                        2,
                        '.',
                        ''
                    );

                    $formattedOptionPseudoPrice = $ekPrices['pseudoPrice'] ? (string)number_format(
                        $ekPrices['pseudoPrice'] * ($taxRate + 1),
                        2,
                        '.',
                        ''
                    ) : null;

                    $res['attributes'][$variant['id']]['options'][$m_option['id']] = array(
                        'id' => $m_option['id'],
                        'label' => htmlentities($m_option['name']),
                        'products' => array_merge(
                            $variantOptionProducts,
                            [
                                0 => array_merge(
                                    [
                                        'id' => $configuration['number'],
                                        'saleable' => $saleable,
                                        'price' => $formattedOptionPrice
                                    ],
                                    $formattedOptionPseudoPrice ?
                                        [ 'oldPrice' => $formattedOptionPseudoPrice ] : []
                                )
                            ]
                        )
                    );
                }
            }

        }

        $res = json_encode($res, JSON_PRETTY_PRINT);
        echo $res;
        exit;

    }

    public function cartAddAction()
    {
        $orderNumber = $this->Request()->getParam('id');
        $quantity = $this->Request()->getParam('qty');

        $quantity = ($quantity > 0) ? $quantity : 1;

        $this->View()->assign(
            'basketInfoMessage',
            $this->getInstockInfo($orderNumber, $quantity)
        );

        $basket = Shopware()->Modules()->Basket();

        try {
            $basket->sAddArticle($orderNumber, $quantity);
        } catch (Exception $e) {
            $this->throwErr($e->getMessage());
        }

        $this->ajaxCartAction();
    }

    public function cartUpdateAction()
    {

        $this->ajaxCartAction();
    }

}
