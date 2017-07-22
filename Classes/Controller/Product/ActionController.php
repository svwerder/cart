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
 * Action Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class ActionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @param \Extcode\Cart\Utility\CartUtility $cartUtility
     */
    public function injectCartUtility(
        \Extcode\Cart\Utility\CartUtility $cartUtility
    ) {
        $this->cartUtility = $cartUtility;
    }

    /**
     * Cart Utility
     *
     * @var \Extcode\Cart\Utility\CartUtility
     */
    protected $cartUtility;

    /**
     * productRepository
     *
     * @var \Extcode\Cart\Domain\Repository\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * categoryRepository
     *
     * @var \Extcode\Cart\Domain\Repository\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Plugin Settings
     *
     * @var array
     */
    protected $pluginSettings;

    /**
     * Action initializer
     */
    protected function initializeAction()
    {
        $this->pluginSettings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'Cart'
        );

        if (!empty($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])) {
            static $cacheTagsSet = false;

            /** @var $typoScriptFrontendController \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController */
            $typoScriptFrontendController = $GLOBALS['TSFE'];
            if (!$cacheTagsSet) {
                $typoScriptFrontendController->addCacheTags(['tx_cart']);
                $cacheTagsSet = true;
            }
        }
    }

    /**
     * @param \Extcode\Cart\Domain\Repository\Product\ProductRepository $productRepository
     */
    public function injectProductRepository(
        \Extcode\Cart\Domain\Repository\Product\ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Extcode\Cart\Domain\Repository\CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(
        \Extcode\Cart\Domain\Repository\CategoryRepository $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * assigns currency translation array to view
     */
    protected function assignCurrencyTranslationData()
    {
        $currencyTranslationData = [];

        $cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);

        if ($cart) {
            $currencyTranslationData['currencyCode'] = $cart->getCurrencyCode();
            $currencyTranslationData['currencySign'] = $cart->getCurrencySign();
            $currencyTranslationData['currencyTranslation'] = $cart->getCurrencyTranslation();
        }

        $this->view->assign('currencyTranslationData', $currencyTranslationData);
    }
}
