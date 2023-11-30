<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3')) {
	die ('Access denied.');
}



ExtensionManagementUtility::addStaticFile('powermailpdf', 'Configuration/TypoScript', 'Powermail PDF Form');

