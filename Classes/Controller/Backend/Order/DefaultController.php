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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Default Controller
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class DefaultController extends \Extcode\Cart\Controller\Backend\Order\ActionController
{
    /**
     * Order Payment Repository
     *
     * @var \Extcode\Cart\Domain\Repository\Order\PaymentRepository
     */
    protected $paymentRepository;

    /**
     * Order Shipping Repository
     *
     * @var \Extcode\Cart\Domain\Repository\Order\ShippingRepository
     */
    protected $shippingRepository;

    /**
     * Order Utility
     *
     * @var \Extcode\Cart\Utility\OrderUtility
     */
    protected $orderUtility;

    /**
     * @param \Extcode\Cart\Domain\Repository\Order\PaymentRepository $paymentRepository
     */
    public function injectPaymentRepository(
        \Extcode\Cart\Domain\Repository\Order\PaymentRepository $paymentRepository
    ) {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @param \Extcode\Cart\Domain\Repository\Order\ShippingRepository $shippingRepository
     */
    public function injectShippingRepository(
        \Extcode\Cart\Domain\Repository\Order\ShippingRepository $shippingRepository
    ) {
        $this->shippingRepository = $shippingRepository;
    }

    /**
     * @param \Extcode\Cart\Utility\OrderUtility $orderUtility
     */
    public function injectOrderUtility(
        \Extcode\Cart\Utility\OrderUtility $orderUtility
    ) {
        $this->orderUtility = $orderUtility;
    }

    /**
     * Initialize Update Action
     */
    public function initializeUpdateAction()
    {
        if ($this->request->hasArgument('orderItem')) {
            $orderItem = $this->request->getArgument('orderItem');

            $invoiceDateString = $orderItem['invoiceDate'];
            $orderItem['invoiceDate'] = \DateTime::createFromFormat('d.m.Y', $invoiceDateString);

            $this->request->setArgument('orderItem', $orderItem);
        }
    }

    /**
     * List Action
     */
    public function listAction()
    {
        $orderItems = $this->itemRepository->findAll($this->searchArguments);
        $this->view->assign('orderItems', $orderItems);

        $this->view->assign('searchArguments', $this->searchArguments);

        $this->assignPaymentStatus();
        $this->assignShippingStatus();

        $pdfRendererInstalled = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('cart_pdf');
        $this->view->assign('pdfRendererInstalled', $pdfRendererInstalled);
    }

    /**
     * Show Action
     *
     * @param \Extcode\Cart\Domain\Model\Order\Item $orderItem
     *
     * @ignorevalidation $orderItem
     */
    public function showAction(\Extcode\Cart\Domain\Model\Order\Item $orderItem)
    {
        $this->view->assign('orderItem', $orderItem);

        $paymentStatusOptions = [];
        $items = $GLOBALS['TCA']['tx_cart_domain_model_order_payment']['columns']['status']['config']['items'];
        foreach ($items as $item) {
            $paymentStatusOptions[$item[1]] = LocalizationUtility::translate(
                $item[0],
                $this->extensionName
            );
        }
        $this->view->assign('paymentStatusOptions', $paymentStatusOptions);

        $shippingStatusOptions = [];
        $items = $GLOBALS['TCA']['tx_cart_domain_model_order_shipping']['columns']['status']['config']['items'];
        foreach ($items as $item) {
            $shippingStatusOptions[$item[1]] = LocalizationUtility::translate(
                $item[0],
                $this->extensionName
            );
        }
        $this->view->assign('shippingStatusOptions', $shippingStatusOptions);

        $pdfRendererInstalled = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('cart_pdf');
        $this->view->assign('pdfRendererInstalled', $pdfRendererInstalled);
    }

    /**
     * @param \Extcode\Cart\Domain\Model\Order\Payment $payment
     */
    public function updatePaymentAction(\Extcode\Cart\Domain\Model\Order\Payment $payment)
    {
        $this->paymentRepository->update($payment);

        $msg = LocalizationUtility::translate(
            'tx_cart.controller.order.action.update_payment_action.success',
            $this->extensionName
        );

        $this->addFlashMessage($msg);

        $this->redirect('show', null, null, ['orderItem' => $payment->getItem()]);
    }

    /**
     * @param \Extcode\Cart\Domain\Model\Order\Shipping $shipping
     */
    public function updateShippingAction(\Extcode\Cart\Domain\Model\Order\Shipping $shipping)
    {
        $this->shippingRepository->update($shipping);

        $msg = LocalizationUtility::translate(
            'tx_cart.controller.order.action.update_shipping_action.success',
            $this->extensionName
        );

        $this->addFlashMessage($msg);

        $this->redirect('show', null, null, ['orderItem' => $shipping->getItem()]);
    }

    /**
     * Generate Number Action
     *
     * @param \Extcode\Cart\Domain\Model\Order\Item $orderItem
     * @param string $numberType
     */
    public function generateNumberAction(\Extcode\Cart\Domain\Model\Order\Item $orderItem, $numberType)
    {
        $getter = 'get' . ucfirst($numberType) . 'Number';
        $setNumberTypeFunction = 'set' . ucfirst($numberType) . 'Number';
        $setNumberDateFunction = 'set' . ucfirst($numberType) . 'Date';

        if (!$orderItem->$getter()) {
            $generatedNumber = $this->generateNumber($orderItem, $numberType);
            $orderItem->$setNumberTypeFunction($generatedNumber);
            $orderItem->$setNumberDateFunction(new \DateTime());

            $this->itemRepository->update($orderItem);
            $this->persistenceManager->persistAll();

            $msg = LocalizationUtility::translate(
                'tx_cart.controller.order.action.generate_number_action.' . $numberType . '.success',
                $this->extensionName,
                [
                    0 => $generatedNumber,
                ]
            );

            $this->addFlashMessage($msg);
        } else {
            $msg = LocalizationUtility::translate(
                'tx_cart.controller.order.action.generate_number_action.' . $numberType . '.already_generated',
                $this->extensionName
            );

            $this->addFlashMessage($msg, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
        }

        $this->redirect('show', null, null, ['orderItem' => $orderItem]);
    }

    /**
     * Generate Number
     *
     * @param \Extcode\Cart\Domain\Model\Order\Item $orderItem
     * @param string $pdfType
     *
     * @return string
     */
    protected function generateNumber(\Extcode\Cart\Domain\Model\Order\Item $orderItem, $pdfType)
    {
        $this->buildTSFE();

        /**
         * @var \TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService
         */
        $typoScriptService = $this->objectManager->get(
            \TYPO3\CMS\Extbase\Service\TypoScriptService::class
        );

        $configurationManager = $this->objectManager->get(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::class
        );

        $cartConfiguration =
            $configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
            );

        if ($cartConfiguration) {
            $typoScriptSettings = $typoScriptService->convertTypoScriptArrayToPlainArray($cartConfiguration);
        }

        //TODO replace it width dynamic var
        $typoScriptSettings['settings'] = [
            'cart' => [
                'pid' => $orderItem->getCartPid(),
            ],
        ];

        $number = $this->orderUtility->getNumber($typoScriptSettings, $pdfType);

        return $number;
    }

    /**
     * Build TSFE
     *
     * @param int $pid Page Id
     */
    protected function buildTSFE($pid = 1, $typeNum = 0)
    {
        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = new \TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
            $GLOBALS['TT']->start();
        }

        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
            $GLOBALS['TYPO3_CONF_VARS'],
            $pid,
            $typeNum
        );
        $GLOBALS['TSFE']->connectToDB();
        $GLOBALS['TSFE']->initFEuser();
        $GLOBALS['TSFE']->id = $pid;
        //$GLOBALS['TSFE']->determineId();
        //$GLOBALS['TSFE']->initTemplate();
        //$GLOBALS['TSFE']->getConfigArray();
    }

    /**
     * prepare payment status for select box and assign to view
     */
    public function assignPaymentStatus()
    {
        $paymentStatusArray = [];

        $paymentStatus = new \stdClass();
        $paymentStatus->key = '';
        $paymentStatus->value = LocalizationUtility::translate(
            'tx_cart_domain_model_order_payment.status.all',
            'Cart'
        );
        $paymentStatusArray[] = $paymentStatus;

        $entries = ['open', 'pending', 'paid', 'canceled'];
        foreach ($entries as $entry) {
            $paymentStatus = new \stdClass();
            $paymentStatus->key = $entry;
            $paymentStatus->value = LocalizationUtility::translate(
                'tx_cart_domain_model_order_payment.status.' . $entry,
                'Cart'
            );
            $paymentStatusArray[] = $paymentStatus;
        }

        $this->view->assign('paymentStatus', $paymentStatusArray);
    }

    /**
     * prepare shipping status for select box and assign to view
     */
    public function assignShippingStatus()
    {
        $shippingStatusArray = [];

        $shippingStatus = new \stdClass();
        $shippingStatus->key = '';
        $shippingStatus->value = LocalizationUtility::translate(
            'tx_cart_domain_model_order_shipping.status.all',
            'Cart'
        );
        $shippingStatusArray[] = $shippingStatus;

        $entries = ['open', 'on_hold', 'in_process', 'shipped'];
        foreach ($entries as $entry) {
            $shippingStatus = new \stdClass();
            $shippingStatus->key = $entry;
            $shippingStatus->value = LocalizationUtility::translate(
                'tx_cart_domain_model_order_shipping.status.' . $entry,
                'Cart'
            );
            $shippingStatusArray[] = $shippingStatus;
        }

        $this->view->assign('shippingStatus', $shippingStatusArray);
    }
}
