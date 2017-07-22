<?php

namespace Extcode\Cart\Controller\Frontend;

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
class CartController extends \Extcode\Cart\Controller\Frontend\Cart\OrderController
{
    /**
     * @var \Extcode\Cart\Domain\Repository\Product\CouponRepository
     */
    protected $couponRepository;

    /**
     * Product Utility
     *
     * @var \Extcode\Cart\Utility\ProductUtility
     */
    protected $productUtility;

    /**
     * Cart
     *
     * @var \Extcode\Cart\Domain\Model\Cart\Cart
     */
    protected $cart;

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
     * Shippings
     *
     * @var array
     */
    protected $shippings = [];

    /**
     * Payments
     *
     * @var array
     */
    protected $payments = [];

    /**
     * Specials
     *
     * @var array
     */
    protected $specials = [];

    /**
     * Plugin Settings
     *
     * @var array
     */
    protected $pluginSettings;

    /**
     * @param \Extcode\Cart\Domain\Repository\Product\CouponRepository $couponRepository
     */
    public function injectCouponRepository(
        \Extcode\Cart\Domain\Repository\Product\CouponRepository $couponRepository
    ) {
        $this->couponRepository = $couponRepository;
    }

    /**
     * @param \Extcode\Cart\Utility\ProductUtility $productUtility
     */
    public function injectProductUtility(
        \Extcode\Cart\Utility\ProductUtility $productUtility
    ) {
        $this->productUtility = $productUtility;
    }

    /**
     * Action initialize
     */
    public function initializeAction()
    {
        $this->pluginSettings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        if (TYPO3_MODE === 'BE') {
            $pageId = (int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id');

            $frameworkConf = $this->configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
            );
            $persistenceConf = ['persistence' => ['storagePid' => $pageId]];
            $this->configurationManager->setConfiguration(
                array_merge($frameworkConf, $persistenceConf)
            );
        }
    }

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
     *
     */
    public function updateCountryAction()
    {
        //ToDo check country is allowed by TypoScript

        $this->cartUtility->updateCountry($this->settings['cart'], $this->pluginSettings, $this->request);

        $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);

        $taxClasses = $this->parserUtility->parseTaxClasses($this->pluginSettings, $this->cart->getBillingCountry());

        $this->cart->setTaxClasses($taxClasses);
        $this->cart->reCalc();

        $this->parseData();

        $paymentId = $this->cart->getPayment()->getId();
        if ($this->payments[$paymentId]) {
            $payment = $this->payments[$paymentId];
            $this->cart->setPayment($payment);
        } else {
            foreach ($this->payments as $payment) {
                if ($payment->getIsPreset()) {
                    $this->cart->setPayment($payment);
                }
            }
        }
        $shippingId = $this->cart->getShipping()->getId();
        if ($this->shippings[$shippingId]) {
            $shipping = $this->shippings[$shippingId];
            $this->cart->setShipping($shipping);
        } else {
            foreach ($this->shippings as $shipping) {
                if ($shipping->getIsPreset()) {
                    $this->cart->setShipping($shipping);
                }
            }
        }

        $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);

        $this->updateService();

        $this->view->assign('cart', $this->cart);

        $assignArguments = [
            'shippings' => $this->shippings,
            'payments' => $this->payments,
            'specials' => $this->specials
        ];
        $this->view->assignMultiple($assignArguments);
    }

    /**
     *
     */
    public function editCurrencyAction()
    {
        $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);
        $this->view->assign('cart', $this->cart);
    }

    /**
     *
     */
    public function updateCurrencyAction()
    {
        $this->cartUtility->updateCurrency($this->settings['cart'], $this->pluginSettings, $this->request);

        $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);

        $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);

        $this->updateService();

        if (isset($_GET['type']) && intval($_GET['type']) == 2278003) {
            $this->view->assign('cart', $this->cart);
        } elseif (isset($_GET['type']) && intval($_GET['type']) == 2278001) {
            $this->view->assign('cart', $this->cart);

            $assignArguments = [
                'shippings' => $this->shippings,
                'payments' => $this->payments,
                'specials' => $this->specials
            ];
            $this->view->assignMultiple($assignArguments);
        }
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

    /**
     * Action Add Product
     *
     * @return string
     */
    public function addProductAction()
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
            $productsChanged = $this->getChangedProducts($products);

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
                            $severity = !empty($error['severity']) ? $error['severity'] : $severity = \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING;
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

            $this->redirect('showCart');
        }
    }

    protected function updateService()
    {
        $this->parseData();
        if (!$this->cart->getPayment()->isAvailable($this->cart->getGross())) {
            $fallBackId = $this->cart->getPayment()->getFallBackId();
            if ($fallBackId) {
                $payment = $this->cartUtility->getServiceById($this->payments, $fallBackId);
                $this->cart->setPayment($payment);
            }
        }

        if (!$this->cart->getShipping()->isAvailable($this->cart->getGross())) {
            $fallBackId = $this->cart->getShipping()->getFallBackId();
            if ($fallBackId) {
                $shipping = $this->cartUtility->getServiceById($this->shippings, $fallBackId);
                $this->cart->setShipping($shipping);
            }
        }
    }

    /**
     * Action Add Coupon
     */
    public function addCouponAction()
    {
        if ($this->request->hasArgument('couponCode')) {
            $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);

            $couponCode = $this->request->getArgument('couponCode');

            /** @var \Extcode\Cart\Domain\Model\Product\Coupon $coupon */
            $coupon = $this->couponRepository->findOneByCode($couponCode);
            if ($coupon && $coupon->getIsAvailable()) {
                $newCartCoupon = $this->objectManager->get(
                    \Extcode\Cart\Domain\Model\Cart\CartCoupon::class,
                    $coupon->getTitle(),
                    $coupon->getCode(),
                    $coupon->getCouponType(),
                    $coupon->getDiscount(),
                    $this->cart->getTaxClass($coupon->getTaxClassId()),
                    $coupon->getCartMinPrice(),
                    $coupon->getIsCombinable()
                );

                $couponWasAdded = $this->cart->addCoupon($newCartCoupon);

                if ($couponWasAdded == 1) {
                    $this->addFlashMessage(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'tx_cart.ok.coupon.added',
                            $this->extensionName
                        ),
                        '',
                        \TYPO3\CMS\Core\Messaging\AbstractMessage::OK,
                        true
                    );
                }
                if ($couponWasAdded == -1) {
                    $this->addFlashMessage(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'tx_cart.error.coupon.already_added',
                            $this->extensionName
                        ),
                        '',
                        \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING,
                        true
                    );
                }
                if ($couponWasAdded == -2) {
                    $this->addFlashMessage(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'tx_cart.error.coupon.not_combinable',
                            $this->extensionName
                        ),
                        '',
                        \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING,
                        true
                    );
                }
            } else {
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'tx_cart.error.coupon.not_accepted',
                        $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING,
                    true
                );
            }

            $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);
        }

        $this->redirect('showCart');
    }

    /**
     * Action Remove Coupon
     */
    public function removeCouponAction()
    {
        if ($this->request->hasArgument('couponCode')) {
            $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);
            $couponCode = $this->request->getArgument('couponCode');
            $couponWasRemoved = $this->cart->removeCoupon($couponCode);

            if ($couponWasRemoved == 1) {
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'tx_cart.ok.coupon.removed',
                        $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::OK,
                    true
                );
            }
            if ($couponWasRemoved == -1) {
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'tx_cart.error.coupon.not_found',
                        $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING,
                    true
                );
            }

            $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);
        }

        $this->redirect('showCart');
    }

    /**
     * Action removeProduct
     */
    public function removeProductAction()
    {
        if ($this->request->hasArgument('product')) {
            $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart']['pid'], $this->pluginSettings);

            $this->cart->removeProductById($this->request->getArgument('product'));

            $this->updateService();

            $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);
        }
        $this->redirect('showCart');
    }

    /**
     * Action setShipping
     *
     * @param int $shippingId
     */
    public function setShippingAction($shippingId)
    {
        $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);

        $this->shippings = $this->parserUtility->parseServices('Shipping', $this->pluginSettings, $this->cart);

        $shipping = $this->shippings[$shippingId];

        if ($shipping) {
            if ($shipping->isAvailable($this->cart->getGross())) {
                $this->cart->setShipping($shipping);
            } else {
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'tx_cart.controller.cart.action.set_shipping.not_available',
                        $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR,
                    true
                );
            }
        }

        $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);

        if (isset($_GET['type'])) {
            $this->view->assign('cart', $this->cart);

            $this->parseData();
            $assignArguments = [
                'shippings' => $this->shippings,
                'payments' => $this->payments,
                'specials' => $this->specials
            ];
            $this->view->assignMultiple($assignArguments);
        } else {
            $this->redirect('showCart');
        }
    }

    /**
     * Action setPayment
     *
     * @param int $paymentId
     */
    public function setPaymentAction($paymentId)
    {
        $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);

        $this->payments = $this->parserUtility->parseServices('Payment', $this->pluginSettings, $this->cart);

        $payment = $this->payments[$paymentId];

        if ($payment) {
            if ($payment->isAvailable($this->cart->getGross())) {
                $this->cart->setPayment($payment);
            } else {
                $this->addFlashMessage(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'tx_cart.controller.cart.action.set_payment.not_available',
                        $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR,
                    true
                );
            }
        }

        $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);

        if (isset($_GET['type'])) {
            $this->view->assign('cart', $this->cart);

            $this->parseData();
            $assignArguments = [
                'shippings' => $this->shippings,
                'payments' => $this->payments,
                'specials' => $this->specials
            ];
            $this->view->assignMultiple($assignArguments);
        } else {
            $this->redirect('showCart');
        }
    }

    /**
     * Parse Data
     */
    protected function parseData()
    {
        // parse all shippings
        $this->shippings = $this->parserUtility->parseServices('Shipping', $this->pluginSettings, $this->cart);

        // parse all payments
        $this->payments = $this->parserUtility->parseServices('Payment', $this->pluginSettings, $this->cart);

        // parse all specials
        $this->specials = $this->parserUtility->parseServices('Special', $this->pluginSettings, $this->cart);
    }

    /**
     * returns list of changed products
     *
     * @param $products
     *
     * @return array
     */
    protected function getChangedProducts($products)
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
}
