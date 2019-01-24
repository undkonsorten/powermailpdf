<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
		'In2code\\Powermail\\Controller\\FormController',
		'createActionBeforeRenderView',
		\Undkonsorten\Powermailpdf\Pdf::class,
		'createAction'
);


