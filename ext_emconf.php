<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "powermailpdf".
 *
 * Auto generated 15-10-2019 12:16
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Powermail PDF Form',
  'description' => 'Add the possibility to download a pdf with data just entered.',
  'category' => 'fe',
  'author' => 'Eike Starkmann',
  'author_email' => 'starkmann@undkonsorten.com',
  'author_company' => 'undkonsorten Gbr',
  'state' => 'beta',
  'uploadfolder' => true,
  'createDirs' => '',
  'clearCacheOnLoad' => 0,
  'version' => '2.4.5',
  'autoload' =>
  array (
    'psr-4' =>
    array (
      'Undkonsorten\\Powermailpdf\\' => 'Classes',
    ),
  ),
  'constraints' =>
  array (
    'depends' =>
    array (
      'typo3' => '8.7.0-9.99.99',
      'php' => '7.0.0-7.99.99',
      'powermail' => '5.0.0-6.99.99',
    ),
    'conflicts' =>
    array (
    ),
    'suggests' =>
    array (
    ),
  ),
  '_md5_values_when_last_written' => 'a:32:{s:12:"ext_icon.gif";s:4:"e922";s:17:"ext_localconf.php";s:4:"a395";s:14:"ext_tables.php";s:4:"6f72";s:14:"ext_tables.sql";s:4:"d41d";s:21:"ExtensionBuilder.json";s:4:"5c39";s:16:"Classes/fpdm.php";s:4:"f58e";s:15:"Classes/Pdf.php";s:4:"bd22";s:21:"Classes/pdftk-php.php";s:4:"ba4a";s:29:"Classes/export/cache/data.fdf";s:4:"2156";s:36:"Classes/export/cache/pdf_flatten.pdf";s:4:"5af6";s:29:"Classes/export/export/fdf.php";s:4:"5c8d";s:26:"Classes/export/fdf/fdf.php";s:4:"c60e";s:32:"Classes/export/fdf/forge_fdf.php";s:4:"5877";s:28:"Classes/export/pdf/pdftk.php";s:4:"f8fc";s:28:"Classes/export/pdf/pdftk.txt";s:4:"9fe8";s:33:"Classes/filters/FilterASCII85.php";s:4:"89e4";s:34:"Classes/filters/FilterASCIIHex.php";s:4:"db94";s:31:"Classes/filters/FilterFlate.php";s:4:"5373";s:29:"Classes/filters/FilterLZW.php";s:4:"80e8";s:34:"Classes/filters/FilterStandard.php";s:4:"3499";s:19:"Classes/lib/url.php";s:4:"3bde";s:44:"Configuration/ExtensionBuilder/settings.yaml";s:4:"b61b";s:40:"Configuration/FlexForms/flexform_pdf.xml";s:4:"e028";s:38:"Configuration/TypoScript/constants.txt";s:4:"98e4";s:34:"Configuration/TypoScript/setup.txt";s:4:"fdc1";s:40:"Resources/Private/Language/locallang.xml";s:4:"efa2";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"ef6b";s:48:"Resources/Private/PDF/2012_12_15_S20-son5-02.pdf";s:4:"2099";s:32:"Resources/Private/PDF/sample.pdf";s:4:"9e0d";s:33:"Resources/Private/PDF/sample2.pdf";s:4:"bcd8";s:35:"Resources/Public/Icons/relation.gif";s:4:"e615";s:14:"doc/manual.sxw";s:4:"a007";}',
  'clearcacheonload' => false,
);
