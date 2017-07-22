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
 * Currency Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class CurrencyController extends \Extcode\Cart\Controller\Cart\ActionController
{
    /**
     *
     */
    public function editAction()
    {
        $this->cart = $this->cartUtility->getCartFromSession($this->settings['cart'], $this->pluginSettings);
        $this->view->assign('cart', $this->cart);
    }

    /**
     *
     */
    public function updateAction()
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
}
