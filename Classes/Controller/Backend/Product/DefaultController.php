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
 * Default Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class DefaultController extends \Extcode\Cart\Controller\Backend\Product\ActionController
{
    /**
     * List Action
     */
    public function listAction()
    {
        $products = $this->productRepository->findAll();
        $this->view->assign('products', $products);

        $this->view->assign('searchArguments', $this->searchArguments);
    }

    /**
     * Show Action
     *
     * @param \Extcode\Cart\Domain\Model\Product\Product $product
     *
     * @ignorevalidation $product
     */
    public function showAction(\Extcode\Cart\Domain\Model\Product\Product $product = null)
    {
        $this->view->assign('product', $product);
    }
}
