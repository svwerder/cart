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
 * Shipping Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class ServiceController extends \Extcode\Cart\Controller\Cart\ActionController
{
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
                    \TYPO3\CMS\Extbase\Utility\$this->localizationUtility->translatetranslate(
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
                    \TYPO3\CMS\Extbase\Utility\$this->localizationUtility->translatetranslate(
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
}
