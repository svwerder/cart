<?php

namespace Extcode\Cart\Controller\Order;

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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Order Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class UserController extends \Extcode\Cart\Controller\Order\ActionController
{
    /**
     * List Action
     */
    public function listAction()
    {
        $feUser = (int)$GLOBALS['TSFE']->fe_user->user['uid'];
        $orderItems = $this->itemRepository->findByFeUser($feUser);

        $this->view->assign('orderItems', $orderItems);
    }

    /**
     * Show Action
     *
     * @param \Extcode\Cart\Domain\Model\Order\Item $orderItem
     *
     * TODO refactor
     *
     * @ignorevalidation $orderItem
     */
    public function showAction(\Extcode\Cart\Domain\Model\Order\Item $orderItem)
    {
        $feUser = (int)$GLOBALS['TSFE']->fe_user->user['uid'];
        if ($orderItem->getFeUser() != $feUser) {
            $this->addFlashMessage(
                'Access denied.',
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
            $this->redirect('list');
        }

        $this->view->assign('orderItem', $orderItem);

        $paymentStatusOptions = [];
        $items = $GLOBALS['TCA']['tx_cart_domain_model_order_payment']['columns']['status']['config']['items'];
        foreach ($items as $item) {
            $paymentStatusOptions[$item[1]] = $this->localizationUtility->translatetranslate(
                $item[0],
                'Cart'
            );
        }
        $this->view->assign('paymentStatusOptions', $paymentStatusOptions);

        $shippingStatusOptions = [];
        $items = $GLOBALS['TCA']['tx_cart_domain_model_order_shipping']['columns']['status']['config']['items'];
        foreach ($items as $item) {
            $shippingStatusOptions[$item[1]] = $this->localizationUtility->translatetranslate(
                $item[0],
                'Cart'
            );
        }
        $this->view->assign('shippingStatusOptions', $shippingStatusOptions);
    }
}
