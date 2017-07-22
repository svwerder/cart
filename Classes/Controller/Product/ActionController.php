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
     * Cart Utility
     *
     * @var \Extcode\Cart\Utility\CartUtility
     */
    protected $cartUtility;

    /**
     * categoryRepository
     *
     * @var \Extcode\Cart\Domain\Repository\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * productRepository
     *
     * @var \Extcode\Cart\Domain\Repository\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * Plugin Settings
     *
     * @var array
     */
    protected $pluginSettings;

    /**
     * Localization Utility
     *
     * @var \TYPO3\CMS\Extbase\Utility\LocalizationUtility
     */
    protected $localizationUtility;

    /**
     * @param \Extcode\Cart\Utility\CartUtility $cartUtility
     */
    public function injectCartUtility(
        \Extcode\Cart\Utility\CartUtility $cartUtility
    ) {
        $this->cartUtility = $cartUtility;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Utility\LocalizationUtility $localizationUtility
     */
    public function injectLocalizationUtility(
        \TYPO3\CMS\Extbase\Utility\LocalizationUtility $localizationUtility
    ) {
        $this->localizationUtility = $localizationUtility;
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
     * @param \Extcode\Cart\Domain\Repository\Product\ProductRepository $productRepository
     */
    public function injectProductRepository(
        \Extcode\Cart\Domain\Repository\Product\ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

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
     * assigns currency translation array to view
     */
    protected function assignCurrencyTranslationData()
    {
        $currencyTranslations = [];

        $cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);

        if ($cart) {
            $currencyTranslations['currencyCode'] = $cart->getCurrencyCode();
            $currencyTranslations['currencySign'] = $cart->getCurrencySign();
            $currencyTranslations['currencyTranslation'] = $cart->getCurrencyTranslation();
        }

        $this->view->assign('currencyTranslationData', $currencyTranslations);
    }
}
