<?php

defined('TYPO3_MODE') or die();

// configure plugins

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'MiniCart',
    [
        'Cart\Default' => 'showMiniCart',
        'Cart\Currency' => 'update',
    ],
    // non-cacheable actions
    [
        'Cart\Default' => 'showMiniCart',
        'Cart\Currency' => 'update',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'Cart',
    [
        'Cart\Default' => 'showCart, clearCart, updateCountry, updateCart',
        'Cart\Order' => 'orderCart',
        'Cart\Coupon' => 'addCoupon, removeCoupon',
        'Cart\Currency' => 'update',
        'Cart\Product' => 'add, remove',
        'Cart\Service' => 'setShipping, setPayment, ',
        'Order\Payment' => 'paymentSuccess, paymentCancel',
    ],
    [
        'Cart\Default' => 'showCart, clearCart, updateCountry, updateCart',
        'Cart\Order' => 'orderCart',
        'Cart\Coupon' => 'addCoupon, removeCoupon',
        'Cart\Currency' => 'update',
        'Cart\Product' => 'add, remove',
        'Cart\Service' => 'setShipping, setPayment',
        'Order\Payment' => 'paymentSuccess, paymentCancel',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'Currency',
    [
        'Cart\Currency' => 'edit, update',
    ],
    [
        'Cart\Currency' => 'edit, update',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'Product',
    [
        'Product\Default' => 'show, list, showForm',
        'Product\Teaser' => 'show',
    ],
    [
        'Product\Default' => 'list, showForm',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'ProductPartial',
    [
        'Product\Default' => 'showForm',
    ],
    [
        'Product\Default' => 'showForm',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'FlexProduct',
    [
        'Product\Flexform' => 'show',
    ],
    [
        'Product\Flexform' => '',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'Order',
    [
        'Order\Default' => 'list, show',
    ],
    [
        'Order\Default' => 'list, show',
    ]
);

// ke_search indexer

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerIndexerConfiguration'][] = 'EXT:cart/Classes/Hooks/KeSearchIndexer.php:Extcode\Cart\Hooks\KeSearchIndexer';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer'][] = 'EXT:cart/Classes/Hooks/KeSearchIndexer.php:Extcode\Cart\Hooks\KeSearchIndexer';
