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
 * Order Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class OrderController extends \Extcode\Cart\Controller\Cart\ActionController
{
    /**
     * Order Utility
     *
     * @var \Extcode\Cart\Utility\OrderUtility
     */
    protected $orderUtility;

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
     * @param \Extcode\Cart\Utility\OrderUtility $orderUtility
     */
    public function injectOrderUtility(
        \Extcode\Cart\Utility\OrderUtility $orderUtility
    ) {
        $this->orderUtility = $orderUtility;
    }

    /**
     * @return string
     */
    protected function getErrorFlashMessage()
    {
        $getValidationResults = $this->arguments->getValidationResults();

        if ($getValidationResults->hasErrors()) {
            $errorMsg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'tx_cart.error.validation',
                $this->extensionName
            );

            return $errorMsg;
        }

        $errorMsg = parent::getErrorFlashMessage();

        return $errorMsg;
    }

    public function initializeOrderCartAction()
    {
        foreach (['orderItem', 'billingAddress', 'shippingAddress'] as $argumentName) {
            if (!$this->arguments->hasArgument($argumentName)) {
                continue;
            }
            if ($this->settings['validation'] &&
                $this->settings['validation'][$argumentName] &&
                $this->settings['validation'][$argumentName]['fields']
            ) {
                $fields = $this->settings['validation'][$argumentName]['fields'];

                foreach ($fields as $propertyName => $validatorConf) {
                    $this->setDynamicValidation(
                        $argumentName,
                        $propertyName,
                        [
                            'validator' => $validatorConf['validator'],
                            'options' => is_array($validatorConf['options'])
                                         ? $validatorConf['options']
                                         : []
                        ]
                    );
                }
            }
        }

        if ($this->arguments->hasArgument('orderItem')) {
            $this->arguments->getArgument('orderItem')
                ->getPropertyMappingConfiguration()
                ->setTargetTypeForSubProperty('additional', 'array');
        }
        if ($this->arguments->hasArgument('billingAddress')) {
            $this->arguments->getArgument('billingAddress')
                ->getPropertyMappingConfiguration()
                ->setTargetTypeForSubProperty('additional', 'array');
        }
        if ($this->arguments->hasArgument('shippingAddress')) {
            $this->arguments->getArgument('shippingAddress')
                ->getPropertyMappingConfiguration()
                ->setTargetTypeForSubProperty('additional', 'array');
        }
    }

    /**
     * Action Order Cart
     *
     * @param \Extcode\Cart\Domain\Model\Order\Item $orderItem
     * @param \Extcode\Cart\Domain\Model\Order\Address $billingAddress
     * @param \Extcode\Cart\Domain\Model\Order\Address $shippingAddress
     */
    public function orderCartAction(
        \Extcode\Cart\Domain\Model\Order\Item $orderItem = null,
        \Extcode\Cart\Domain\Model\Order\Address $billingAddress = null,
        \Extcode\Cart\Domain\Model\Order\Address $shippingAddress = null
    ) {
        if (($orderItem == null) || ($billingAddress == null)) {
            $this->redirect('showCart');
        }

        $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);

        if ($this->cart->getCount() == 0) {
            $this->redirect('showCart');
        }

        $this->orderUtility->checkStock($this->cart, $this->pluginSettings);

        $orderItem->setCartPid(intval($GLOBALS['TSFE']->id));

        if ($this->request->hasArgument('shipping_same_as_billing')) {
            $useSameAddress = $this->request->getArgument('shipping_same_as_billing');

            if ($useSameAddress === 'true') {
                $shippingAddress = null;
                $orderItem->removeShippingAddress();
            }
        }

        $this->orderUtility->saveOrderItem(
            $this->pluginSettings,
            $this->cart,
            $orderItem,
            $billingAddress,
            $shippingAddress
        );

        $this->orderUtility->handleStock($this->cart, $this->pluginSettings);

        $providerUsed = $this->orderUtility->handlePayment($orderItem, $this->cart);

        if (!$providerUsed) {
            $this->orderUtility->autoGenerateDocuments($orderItem, $this->pluginSettings);

            $this->sendMails($orderItem);

            $this->view->assign('cart', $this->cart);
            $this->view->assign('orderItem', $orderItem);
        }

        $paymentId = $this->cart->getPayment()->getId();
        $paymentSettings = $this->parserUtility->getTypePluginSettings($this->pluginSettings, $this->cart, 'payments');

        if (intval($paymentSettings['options'][$paymentId]['preventClearCart']) != 1) {
            $this->cart = $this->cartUtility->getNewCart($this->settings['cart'], $this->pluginSettings);
        }

        $this->cartUtility->writeCartToSession($this->cart, $this->settings['cart']['pid']);

        if ($paymentSettings['options'][$paymentId] &&
            $paymentSettings['options'][$paymentId]['redirects'] &&
            $paymentSettings['options'][$paymentId]['redirects']['success'] &&
            $paymentSettings['options'][$paymentId]['redirects']['success']['url']
        ) {
            $this->redirectToURI($paymentSettings['options'][$paymentId]['redirects']['success']['url'], 0, 200);
        }
    }

    /**
     * Send Mails
     *
     * @param \Extcode\Cart\Domain\Model\Order\Item $orderItem
     */
    protected function sendMails(
        \Extcode\Cart\Domain\Model\Order\Item $orderItem
    ) {
        $paymentId = $this->cart->getPayment()->getId();
        if (intval($this->pluginSettings['payments']['options'][$paymentId]['preventBuyerEmail']) != 1) {
            $this->sendBuyerMail($orderItem);
        }
        if (intval($this->pluginSettings['payments']['options'][$paymentId]['preventSellerEmail']) != 1) {
            $this->sendSellerMail($orderItem);
        }
    }

    /**
     * Send a Mail to Buyer
     *
     * @param \Extcode\Cart\Domain\Model\Order\Item $orderItem
     */
    protected function sendBuyerMail(
        \Extcode\Cart\Domain\Model\Order\Item $orderItem
    ) {
        /* @var \Extcode\Cart\Service\MailHandler $mailHandler*/
        $mailHandler = $this->objectManager->get(
            \Extcode\Cart\Service\MailHandler::class
        );
        $mailHandler->setCart($this->cart);
        $mailHandler->sendBuyerMail($orderItem);
    }

    /**
     * Send a Mail to Seller
     *
     * @param \Extcode\Cart\Domain\Model\Order\Item $orderItem
     */
    protected function sendSellerMail(
        \Extcode\Cart\Domain\Model\Order\Item $orderItem
    ) {
        /* @var \Extcode\Cart\Service\MailHandler $mailHandler*/
        $mailHandler = $this->objectManager->get(
            \Extcode\Cart\Service\MailHandler::class
        );
        $mailHandler->setCart($this->cart);
        $mailHandler->sendSellerMail($orderItem);
    }

    /**
     * Sets the dynamic validation rules.
     *
     * @param string $argumentName
     * @param string $propertyName
     * @param array $validatorConf
     * @throws \TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException
     */
    protected function setDynamicValidation($argumentName, $propertyName, $validatorConf)
    {
        // build custom validation chain
        /** @var \TYPO3\CMS\Extbase\Validation\ValidatorResolver $validatorResolver */
        $validatorResolver = $this->objectManager->get(
            \TYPO3\CMS\Extbase\Validation\ValidatorResolver::class
        );

        if ($validatorConf['validator'] == 'Empty') {
            $validatorConf['validator'] = '\Extcode\Cart\Validation\Validator\EmptyValidator';
        }

        $propertyValidator = $validatorResolver->createValidator(
            $validatorConf['validator'],
            $validatorConf['options']
        );

        if ($argumentName === 'orderItem') {
            /** @var \Extcode\Cart\Domain\Validator\OrderItemValidator $modelValidator */
            $modelValidator = $validatorResolver->createValidator(
                \Extcode\Cart\Domain\Validator\OrderItemValidator::class
            );
        } else {
            /** @var \TYPO3\CMS\Extbase\Validation\Validator\GenericObject $modelValidator */
            $modelValidator = $validatorResolver->createValidator('GenericObject');
        }

        $modelValidator->addPropertyValidator(
            $propertyName,
            $propertyValidator
        );

        /** @var \TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator $conjunctionValidator */
        $conjunctionValidator = $this->arguments->getArgument($argumentName)->getValidator();
        if ($conjunctionValidator === null) {
            $conjunctionValidator = $validatorResolver->createValidator(
                \TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator::class
            );
            $this->arguments->getArgument($argumentName)->setValidator($conjunctionValidator);
        }
        $conjunctionValidator->addValidator($modelValidator);
    }
}
