<?php

namespace Extcode\Cart\Controller\Product;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Default Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class DefaultController extends \Extcode\Cart\Controller\Product\ActionController
{
    /**
     * Search Arguments
     *
     * @var array
     */
    protected $searchArguments;

    /**
     * Create the demand object which define which records will get shown
     *
     * @param array $settings
     *
     * @return \Extcode\Cart\Domain\Model\Dto\Product\ProductDemand
     */
    protected function createDemandObjectFromSettings($settings)
    {
        /** @var \Extcode\Cart\Domain\Model\Dto\Product\ProductDemand $demand */
        $demand = $this->objectManager->get(
            \Extcode\Cart\Domain\Model\Dto\Product\ProductDemand::class
        );

        if ($this->searchArguments['sku']) {
            $demand->setSku($this->searchArguments['sku']);
        }
        if ($this->searchArguments['title']) {
            $demand->setTitle($this->searchArguments['title']);
        }
        if ($settings['orderBy']) {
            $demand->setOrder($settings['orderBy'] . ' ' . $settings['orderDirection']);
        }

        $this->addCategoriesToDemandObjectFromSettings($demand);

        return $demand;
    }

    /**
     * @param \Extcode\Cart\Domain\Model\Dto\Product\ProductDemand $demand
     */
    protected function addCategoriesToDemandObjectFromSettings(&$demand)
    {
        if ($this->settings['categoriesList']) {
            $selectedCategories = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(
                ',',
                $this->settings['categoriesList'],
                true
            );

            $demand->setCategories($selectedCategories);

            $recursiveCategories = [];

            if ($this->settings['listSubcategories']) {
                foreach ($selectedCategories as $selectedCategory) {
                    $category = $this->categoryRepository->findByUid($selectedCategory);
                    $recursiveCategories = array_merge(
                        $recursiveCategories,
                        $this->categoryRepository->findSubcategoriesRecursiveAsArray($category)
                    );
                }

                $demand->setCategories($recursiveCategories);
            }
        }
    }

    /**
     * action list
     */
    public function listAction()
    {
        $demand = $this->createDemandObjectFromSettings($this->settings);
        $demand->setActionAndClass(__METHOD__, __CLASS__);

        $products = $this->productRepository->findDemanded($demand);

        $this->view->assign('searchArguments', $this->searchArguments);
        $this->view->assign('products', $products);

        $this->assignCurrencyTranslationData();
    }

    /**
     * action show
     *
     * @param \Extcode\Cart\Domain\Model\Product\Product $product
     *
     * @ignorevalidation $product
     */
    public function showAction(\Extcode\Cart\Domain\Model\Product\Product $product = null)
    {
        if (empty($product)) {
            $this->forward('list');
        }

        $this->view->assign('user', $GLOBALS['TSFE']->fe_user->user);
        $this->view->assign('product', $product);

        $this->assignCurrencyTranslationData();
    }

    /**
     * action showForm
     *
     * @param \Extcode\Cart\Domain\Model\Product\Product $product
     */
    public function showFormAction(\Extcode\Cart\Domain\Model\Product\Product $product = null)
    {
        if (!$product && $this->request->getPluginName()=='ProductPartial') {
            $requestBuilder =$this->objectManager->get(
                \TYPO3\CMS\Extbase\Mvc\Web\RequestBuilder::class
            );
            $configurationManager = $this->objectManager->get(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class
            );
            $configurationManager->setConfiguration([
                'vendorName' => 'Extcode',
                'extensionName' => 'Cart',
                'pluginName' => 'Product',
            ]);
            /**
             * @var \TYPO3\CMS\Extbase\Mvc\Web\Request $cartProductRequest
             */
            $cartProductRequest = $requestBuilder->build();

            if ($cartProductRequest->hasArgument('product')) {
                $productUid = $cartProductRequest->getArgument('product');
            }

            $productRepository = $this->objectManager->get(
                \Extcode\Cart\Domain\Repository\Product\ProductRepository::class
            );

            $product =  $productRepository->findByUid($productUid);
        }
        $this->view->assign('product', $product);

        $this->assignCurrencyTranslationData();
    }
}
