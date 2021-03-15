<?php
namespace Undkonsorten\Powermailpdf;



use In2code\Powermail\Domain\Model\Mail;
use TYPO3\CMS\Core\Error\Exception;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class Pdf extends \In2code\Powermail\Controller\FormController {


	public function addAttachment(\TYPO3\CMS\Core\Mail\MailMessage $message, \In2code\Powermail\Domain\Model\Mail $mail, $settings){
	//@TODO Maybe better use this slot
	}

	/**
	 * This function is used when pdftk is NOT installed on system
	 *  Remember: No chockboxes possible
	 * @param In2code\Powermail\Domain\Model\Mail $mail
	 * @throws \FileNotFoundException
	 */
	protected function generatePdf(\In2code\Powermail\Domain\Model\Mail $mail){

		// Include \FPDM library from phar file, if not included already (e.g. composer installation)
		if (!class_exists('\FPDM')) {
			@include 'phar://' . ExtensionManagementUtility::extPath('powermailpdf') . 'Resources/Private/PHP/fpdm.phar/vendor/autoload.php';
		}

		//ToDo Map Fields from $field to array;
		//Normal Fields
		$fieldMap= $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailpdf.']['settings.']['fieldMap.'];
		$powermailSettings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermail.']['settings.'];

		$answers = $mail->getAnswers();
		$fdfDataStrings = array();

		foreach ($fieldMap as $key => $value) {
			foreach($answers as $answer){
				if($value == $answer->getField()->getMarker()){
					$fdfDataStrings[$key]  = $answer->getValue();
				}
			}
		}

		$pdfOriginal = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailpdf.']['settings.']['sourceFile']);

		if (!empty($pdfOriginal)){
			$info = pathinfo($pdfOriginal);
			$fileName = basename($pdfOriginal,'.'.$info['extension']);
			// Name of file to be downloaded
			$pdfFilename = $powermailSettings['setup.']['misc.']['file.']['folder'].$fileName."_".md5(time()).'.pdf';
			$pdf = new \FPDM($pdfOriginal);
			$pdf->Load($fdfDataStrings, true); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
			$pdf->Merge();
            $pdf->Output("F", GeneralUtility::getFileAbsFileName($pdfFilename));
		}else{
			throw new Exception("No pdf file is set in Typoscript. Please set tx_powermailpdf.settings.sourceFile if you want to use the filling feature.",1417432239);
		}

		return $pdfFilename;

	}

	/**
	 * @param string $uri the URI that will be put in the href attribute of the rendered link tag
	 * @param string $label
	 * @return string Rendered link
	 */

	protected function render($uri, $label) {

		//get filelinkconf from typoscript setup plugin.tx_productdownloads.settings.filelink
		$filelinkconf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailpdf.']['settings.']['filelink.'];

		//replace the link label with the param $label
		$filelinkconf['labelStdWrap.']['cObject'] = 'TEXT';
		$filelinkconf['labelStdWrap.']['cObject.']['value'] = $label;

		$output = $GLOBALS['TSFE']->cObj->filelink($uri,$filelinkconf);
		return $output;
	}

	/**
	 *
	 * @param \In2code\Powermail\Domain\Model\Mail $mail
	 * @param \string $hash
     * @param \In2code\Powermail\Controller\FormController
	 */
	public function createAction(Mail $mail, string $hash = '')
	{
		$settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailpdf.']['settings.'];
		$powermailSettings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermail.']['settings.'];
		$filePath = $settings['sourceFile'];
		$powermailFilePath = $powermailSettings['setup.']['misc.']['file.']['folder'] . basename($filePath);

		if($settings['enablePowermailPdf']){
			if($settings['sourceFile']){
				if(!file_exists(GeneralUtility::getFileAbsFileName($settings['sourceFile']))){
					throw new \Exception("The file does not exist: ". $settings['sourceFile']." Please set correct path in plugin.tx_powermailpdf.settings.sourceFile", 1417520887);
				}
			}



			if($settings['fillPdf']){
				$powermailFilePath = $this->generatePdf($mail);
			}else{
				//Copy our pdf to powermail when is does not exist or has changed
				if(!file_exists(GeneralUtility::getFileAbsFileName($powermailFilePath))
					|| (md5_file(GeneralUtility::getFileAbsFileName($powermailFilePath)) != md5_file(GeneralUtility::getFileAbsFileName($filePath)))) {
					copy(GeneralUtility::getFileAbsFileName($filePath),GeneralUtility::getFileAbsFileName($powermailFilePath));
				}
			}

			if($settings['showDownloadLink']){
				$label= LocalizationUtility::translate("download","powermailpdf");
				$link=$this->render($powermailFilePath, $label);
				//Adds a field for the download link at the thx site
				/* @var $answer \In2code\Powermail\Domain\Model\Answer */
				$answer = $this->objectManager->get('In2code\Powermail\Domain\Model\Answer');
				/* @var $field \In2code\Powermail\Domain\Model\Field */
				$field = $this->objectManager->get('In2code\Powermail\Domain\Model\Field');
				$field->setTitle(LocalizationUtility::translate('downloadLink', 'powermailpdf'));
				$field->setMarker('downloadLink');
				$field->setType('html');
				$answer->setField($field);
				$answer->setValue($link);
				$mail->addAnswer($answer);
			}

            if($settings['email.']['attachFile']){
                // powermail version > 3.22.0
                if (VersionNumberUtility::convertVersionNumberToInteger(ExtensionManagementUtility::getExtensionVersion("powermail")) >= 3022000) {
                    // set pdf filename for attachment via TypoScript
                    $this->settings['receiver']['addAttachment']['value'] = $powermailFilePath;
                    $this->settings['sender']['addAttachment']['value'] = $powermailFilePath;
                } else {
                    /* @var $answer \In2code\Powermail\Domain\Model\Answer */
                    $answer = $this->objectManager->get('In2code\Powermail\Domain\Model\Answer');
                    /* @var $field \In2code\Powermail\Domain\Model\Field */
                    $field = $this->objectManager->get('In2code\Powermail\Domain\Model\Field');
                    $field->setTitle(LocalizationUtility::translate('file', 'powermailpdf'));
                    $field->setType('file');
                    $answer->setField($field);
                    $answer->setValue(basename($powermailFilePath));
                    $mail->addAnswer($answer);
                }

			}
		}
	}
}
?>
