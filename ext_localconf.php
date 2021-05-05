<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
		'In2code\\Powermail\\Controller\\FormController',
		'createActionBeforeRenderView',
		\Undkonsorten\Powermailpdf\Pdf::class,
		'createActionBeforeRenderView'
);


$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\In2code\Powermail\ViewHelpers\Misc\VariablesViewHelper::class] = [
    'className' => \Undkonsorten\Powermailpdf\ViewHelpers\Misc\VariablesViewHelper::class
];
