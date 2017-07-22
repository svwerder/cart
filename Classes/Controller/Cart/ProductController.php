<?php

namespace Extcode\Cart\Controller\Cart;

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
 * Product Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class ProductController extends \Extcode\Cart\Controller\Cart\ActionController
{
    /**
     * Product Utility
     *
     * @var \Extcode\Cart\Utility\ProductUtility
     */
    protected $productUtility;

    /**
     * @param \Extcode\Cart\Utility\ProductUtility $productUtility
     */
    public function injectProductUtility(
        \Extcode\Cart\Utility\ProductUtility $productUtility
    ) {
        $this->productUtility = $productUtility;
    }

    /**
     * Action Add Product
     *
     * @return string
     */
    public function addAction()
    {
        $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);

        $products = $this->productUtility->getProductsFromRequest(
            $this->pluginSettings,
            $this->request,
            $this->cart->getTaxClasses()
        );

        list($products, $errors) = $this->productUtility->checkProductsBeforeAddToCart($this->cart, $products);

        $quantity = $this->addProductsToCart($products);

        $this->updateService();

        $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);

        if (isset($_GET['type'])) {
            $productsChanged = $this->retrieveChangedProducts($products);

            // ToDo: have different response status
            $response = [
                'status' => '200',
                'added' => $quantity,
                'count' => $this->cart->getCount(),
                'net' => $this->cart->getNet(),
                'gross' => $this->cart->getGross(),
                'productsChanged' => $productsChanged,
            ];

            return json_encode($response);
        } else {
            if ($errors) {
                if (is_array($errors)) {
                    foreach ($errors as $error) {
                        if ($error['message']) {
                            $severity = \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING;
                            $severity = !empty($error['severity']) ? $error['severity'] : $severity;
                            $storeInSession = true;

                            $this->addFlashMessage(
                                $error['message'],
                                '',
                                $severity,
                                $storeInSession
                            );
                        }
                    }
                }
            }

            $this->redirect('showCart', 'Cart');
        }
    }

    /**
     * Action removeProduct
     */
    public function removeAction()
    {
        if ($this->request->hasArgument('product')) {
            $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);

            $this->cart->removeProductById($this->request->getArgument('product'));

            $this->updateService();

            $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);
        }
        $this->redirect('showCart', 'Cart');
    }

    /**
     * @param $products
     * @return int
     */
    protected function addProductsToCart($products)
    {
        $quantity = 0;

        foreach ($products as $product) {
            if ($product instanceof \Extcode\Cart\Domain\Model\Cart\Product) {
                $quantity += $product->getQuantity();
                $this->cart->addProduct($product);
            }
        }
        return $quantity;
    }

    /**
     * returns list of changed products
     *
     * @param $products
     *
     * @return array
     */
    protected function retrieveChangedProducts($products)
    {
        $productsChanged = [];

        foreach ($products as $product) {
            if ($product instanceof \Extcode\Cart\Domain\Model\Cart\Product) {
                $productChanged = $this->cart->getProduct($product->getId());
                $productsChanged[$product->getId()] = $productChanged->toArray();
            }
        }
        return $productsChanged;
    }
}
