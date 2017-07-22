<?php

namespace Extcode\Cart\Controller\Backend\Product;

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
     * Product Repository
     *
     * @var \Extcode\Cart\Domain\Repository\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @param \Extcode\Cart\Domain\Repository\Product\ProductRepository $productRepository
     */
    public function injectProductRepository(
        \Extcode\Cart\Domain\Repository\Product\ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }
}
