<?php

defined('TYPO3_MODE') or die();

// configure plugins

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'MiniCart',
    [
        'Cart' => 'showMiniCart, updateCurrency',
    ],
    // non-cacheable actions
    [
        'Cart' => 'showMiniCart, updateCurrency',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'Cart',
    [
        'Cart' => 'showCart, clearCart, updateCountry, updateCurrency, updateCart',
        'Cart\Order' => 'orderCart',
        'Cart\Coupon' => 'addCoupon, removeCoupon',
        'Cart\Currency' => 'update',
        'Cart\Product' => 'add, remove',
        'Cart\Service' => 'setShipping, setPayment, ',
        'Order' => 'paymentSuccess, paymentCancel',
    ],
    [
        'Cart' => 'showCart, clearCart, updateCountry, updateCurrency, updateCart',
        'Cart\Order' => 'orderCart',
        'Cart\Coupon' => 'addCoupon, removeCoupon',
        'Cart\Currency' => 'update',
        'Cart\Product' => 'add, remove',
        'Cart\Service' => 'setShipping, setPayment',
        'Order' => 'paymentSuccess, paymentCancel',
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
        'Product' => 'show, list, teaser, showForm',
    ],
    [
        'Product' => 'list, showForm',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'ProductPartial',
    [
        'Product' => 'showForm',
    ],
    [
        'Product' => 'showForm',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'FlexProduct',
    [
        'Product' => 'flexform',
    ],
    [
        'Product' => '',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Extcode.' . $_EXTKEY,
    'Order',
    [
        'Order' => 'list, show',
    ],
    [
        'Order' => 'list, show',
    ]
);

// ke_search indexer

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerIndexerConfiguration'][] = 'EXT:cart/Classes/Hooks/KeSearchIndexer.php:Extcode\Cart\Hooks\KeSearchIndexer';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer'][] = 'EXT:cart/Classes/Hooks/KeSearchIndexer.php:Extcode\Cart\Hooks\KeSearchIndexer';
