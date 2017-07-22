<?php

namespace Extcode\Cart\Controller\Backend\Order;

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
class ActionController extends \Extcode\Cart\Controller\Backend\ActionController
{
    /**
     * Order Item Repository
     *
     * @var \Extcode\Cart\Domain\Repository\Order\ItemRepository
     */
    protected $itemRepository;

    /**
     * Localization Utility
     *
     * @var \TYPO3\CMS\Extbase\Utility\LocalizationUtility
     */
    protected $localizationUtility;

    /**
     * @param \Extcode\Cart\Domain\Repository\Order\ItemRepository $itemRepository
     */
    public function injectItemRepository(
        \Extcode\Cart\Domain\Repository\Order\ItemRepository $itemRepository
    ) {
        $this->itemRepository = $itemRepository;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Utility\LocalizationUtility $localizationUtility
     */
    public function injectLocalizationUtility(
        \TYPO3\CMS\Extbase\Utility\LocalizationUtility $localizationUtility
    ) {
        $this->localizationUtility = $localizationUtility;
    }
}
