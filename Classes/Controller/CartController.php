<?php

namespace Extcode\Cart\Controller;

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
 * Cart Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class CartController extends \Extcode\Cart\Controller\Cart\ActionController
{
    /**
     * GpValues
     *
     * @var array
     */
    protected $gpValues = [];

    /**
     * TaxClasses
     *
     * @var array
     */
    protected $taxClasses = [];

    /**
     * Action Show Cart
     *
     * @param \Extcode\Cart\Domain\Model\Order\Item $orderItem
     * @param \Extcode\Cart\Domain\Model\Order\Address $billingAddress
     * @param \Extcode\Cart\Domain\Model\Order\Address $shippingAddress
     */
    public function showCartAction(
        \Extcode\Cart\Domain\Model\Order\Item $orderItem = null,
        \Extcode\Cart\Domain\Model\Order\Address $billingAddress = null,
        \Extcode\Cart\Domain\Model\Order\Address $shippingAddress = null
    ) {
        $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);

        $this->view->assign('cart', $this->cart);

        $this->parseData();

        $assignArguments = [
            'shippings' => $this->shippings,
            'payments' => $this->payments,
            'specials' => $this->specials
        ];
        $this->view->assignMultiple($assignArguments);

        if ($orderItem == null) {
            $orderItem = $this->objectManager->get(
                \Extcode\Cart\Domain\Model\Order\Item::class
            );
        }
        if ($billingAddress == null) {
            $billingAddress = $this->objectManager->get(
                \Extcode\Cart\Domain\Model\Order\Address::class
            );
        }
        if ($shippingAddress == null) {
            $shippingAddress = $this->objectManager->get(
                \Extcode\Cart\Domain\Model\Order\Address::class
            );
        }

        $assignArguments = [
            'orderItem' => $orderItem,
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress
        ];
        $this->view->assignMultiple($assignArguments);
    }

    /**
     * Action showMiniCart
     */
    public function showMiniCartAction()
    {
        $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);
        $this->view->assign('cart', $this->cart);
    }

    /**
     * Action Clear Cart
     */
    public function clearCartAction()
    {
        $this->cart = $this->cartUtility->getNewCart($this->settings['cart'], $this->pluginSettings);

        $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);

        $this->redirect('showCart');
    }

    /**
     * Action Update Cart
     */
    public function updateCartAction()
    {
        if ($this->request->hasArgument('quantities')) {
            $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);
            $updateQuantities = $this->request->getArgument('quantities');
            if (is_array($updateQuantities)) {
                foreach ($updateQuantities as $productId => $quantity) {
                    $product = $this->cart->getProductById($productId);
                    if ($product) {
                        if (ctype_digit($quantity)) {
                            $quantity = intval($quantity);
                            $product->changeQuantity(intval($quantity));
                        } elseif (is_array($quantity)) {
                            $product->changeVariantsQuantity($quantity);
                        }
                    }
                }
                $this->cart->reCalc();
            }

            $this->updateService();

            $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);
        }
        $this->redirect('showCart');
    }
}
