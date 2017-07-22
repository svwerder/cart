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
 * Export Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class ExportController extends \Extcode\Cart\Controller\Backend\Order\ActionController
{

    /**
     * Export Action
     */
    public function exportAction()
    {
        $format = $this->request->getFormat();

        if ($format == 'csv') {
            $title = 'Order-Export-' . date('Y-m-d_H-i');
            $filename = $title . '.' . $format;

            $this->response->setHeader('Content-Type', 'text/' . $format, true);
            $this->response->setHeader('Content-Description', 'File transfer', true);
            $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"', true);
        }

        $orderItems = $this->itemRepository->findAll($this->searchArguments);

        $this->view->assign('searchArguments', $this->searchArguments);
        $this->view->assign('orderItems', $orderItems);

        $pdfRendererInstalled = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('cart_pdf');
        $this->view->assign('pdfRendererInstalled', $pdfRendererInstalled);
    }
}
