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
 * Coupon Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class CouponController extends \Extcode\Cart\Controller\Cart\ActionController
{
    /**
     * @var \Extcode\Cart\Domain\Repository\Product\CouponRepository
     */
    protected $couponRepository;

    /**
     * @param \Extcode\Cart\Domain\Repository\Product\CouponRepository $couponRepository
     */
    public function injectCouponRepository(
        \Extcode\Cart\Domain\Repository\Product\CouponRepository $couponRepository
    ) {
        $this->couponRepository = $couponRepository;
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
                /** @var \Extcode\Cart\Domain\Model\Cart\CartCoupon $newCartCoupon */
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
                        $this->localizationUtility->translate(
                            'tx_cart.ok.coupon.added',
                            'Cart'
                        ),
                        '',
                        \TYPO3\CMS\Core\Messaging\AbstractMessage::OK,
                        true
                    );
                }
                if ($couponWasAdded == -1) {
                    $this->addFlashMessage(
                        $this->localizationUtility->translate(
                            'tx_cart.error.coupon.already_added',
                            'Cart'
                        ),
                        '',
                        \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING,
                        true
                    );
                }
                if ($couponWasAdded == -2) {
                    $this->addFlashMessage(
                        $this->localizationUtility->translate(
                            'tx_cart.error.coupon.not_combinable',
                            'Cart'
                        ),
                        '',
                        \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING,
                        true
                    );
                }
            } else {
                $this->addFlashMessage(
                    $this->localizationUtility->translate(
                        'tx_cart.error.coupon.not_accepted',
                        'Cart'
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
                    $this->localizationUtility->translate(
                        'tx_cart.ok.coupon.removed',
                        'Cart'
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::OK,
                    true
                );
            }
            if ($couponWasRemoved == -1) {
                $this->addFlashMessage(
                    $this->localizationUtility->translate(
                        'tx_cart.error.coupon.not_found',
                        'Cart'
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
}
