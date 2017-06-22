<?php
namespace Undkonsorten\Powermailpdf;



use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Error\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
		include_once 'Service/fpdm.php';
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
			$pdf->Output(GeneralUtility::getFileAbsFileName($pdfFilename), "");
		}else{
			throw new Exception("No pdf file is set in Typoscript. Please set tx_powermailpdf.settings.sourceFile if you want to use the filling feature.",1417432239);
		}
		
		return $pdfFilename;
		
	}
	
	/**
	 * 
	 * @param array $field
	 * @return path to pdf
	 * This function is used when pdftk is installed on system
	 */
	
	function generate_pdf_pdftk($field){
		require 'Service/pdftk-php.php';
		
		// Initiate the class
		$pdfmaker = new pdftk_php;
		
		// Define variables for all the data fields in the PDF form. You need to assign a column in the database to each field that you'll be using in the PDF.
		// Example:
		// $pdf_column = $data['column'];
		
		// You can also format the MySQL data how you want here. One common example is formatting a date saved in the database. For example:
		// $pdf_date = date("l, F j, Y, g:i a", strtotime($data['date']));
		

		
		// $fdf_data_strings associates the names of the PDF form fields to the PHP variables you just set above. In order to work correctly the PDF form field name has to be exact. PDFs made in Acrobat generally have simpler names - just the name you assigned to the field. PDFs made in LiveCycle Designer nest their forms in other random page elements, creating a long and hairy field name. You can use pdftk to discover the real names of your PDF form fields: run "pdftk form.pdf dump_data_fields > form-fields.txt" to generate a report.
		
		// Example of field names from a PDF created in LiveCycle:
		// $fdf_data_strings= array('form1[0].#subform[0].#area[0].LastName[0]' => $pdf_lastname,  'form1[0].#subform[0].#area[0].FirstName[0]' => $pdf_firstname, 'form1[0].#subform[0].#area[0].EMail[0]' => $pdf_email, );
		#$fdf_data_strings= array('E-Mail' => $pdf_firstname,  'Fax' => $pdf_lastname);
		$fdf_data_strings=$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailpdf.']['settings.']['fieldMap.'];
		
		// See the documentation of pdftk-php.php for more explanation of these other variables.
		
		// Used for radio buttons and check boxes
		// Example: (For check boxes options are Yes and Off)
		// $pdf_checkbox1 = "Yes";
		// $pdf_checkbox2 = "Off";
		// $pdf_checkbox3 = "Yes";
		// $fdf_data_names = array('checkbox1' => $pdf_checkbox1,'checkbox2' => $pdf_checkbox2,'checkbox3' => $pdf_checkbox3,'checkbox4' => $pdf_checkbox4);
		$fdf_data_names = array(); // Leave empty if there are no radio buttons or check boxes
		$fdf_data_names = array('Einverst'.chr(228).'ndnis-Checkbox'    => 'Nein', 'Preis1'    => 'Nein', 'Preis2'    => 'Nein', 'Preis3' => 'Ja');
		
		$fields_hidden = array(); // Used to hide form fields
		
		$fields_readonly = array(); // Used to make fields read only - however, flattening the output with pdftk will in effect make every field read only. If you don't want a flattened pdf and still want some read only fields, use this variable and remove the flatten flag near line 70 in pdftk-php.php
		
		// Name of file to be downloaded
		$pdf_filename = "Test PDF for $pdf_firstname $pdf_lastname.pdf";
		
		// Name/location of original, empty PDF form
		$pdf_original = "source2.pdf";
		
		// Finally make the actual PDF file!
		$pdfmaker->make_pdf($fdf_data_strings, $fdf_data_names, $fields_hidden, $fields_readonly, $pdf_original, $pdf_filename);
		
		
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
	 */
	public function createAction(\In2code\Powermail\Domain\Model\Mail $mail, $hash = NULL){
		$settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailpdf.']['settings.'];
		$powermailSettings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermail.']['settings.'];
		$filePath = $settings['sourceFile'];
		$powermailFilePath = $powermailSettings['setup.']['misc.']['file.']['folder'] . basename($filePath);
		
		if($settings['enablePowermailPdf']){
			//$settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'powermailpdf', 'pi1');
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
			
			/*	Show download link in submit page
				Needs the Web.html to be edited like so:
				<f:if condition="{0:answer.field.type} == {0:'downloadLink'}">
					<f:then>
						<f:format.html>{answer.value}</f:format.html>
					</f:then>
					<f:else>
					<	f:format.nl2br>{answer.value}</f:format.nl2br>
					</f:else>
				</f:if>
			*/
			if($settings['showDownloadLink']){
				$label= LocalizationUtility::translate("download","powermailpdf");
				$link=$this->render($powermailFilePath, $label);
				//Adds a field for the download link at the thx site
				/* @var $answer In2code\Powermail\Domain\Model\Answer */
				$answer = $this->objectManager->get('In2code\Powermail\Domain\Model\Answer');
				/* @var $field In2code\Powermail\Domain\Model\Field */
				$field = $this->objectManager->get('In2code\Powermail\Domain\Model\Field');
				$field->setTitle(LocalizationUtility::translate('downloadLink', 'powermailpdf'));
				$field->setType('downloadLink');
				$answer->setField($field);
				$answer->setValue($link);
				$mail->addAnswer($answer);
			}
			
			if($settings['email.']['attachFile']){
				/* @var $answer In2code\Powermail\Domain\Model\Answer */
				$answer = $this->objectManager->get('In2code\Powermail\Domain\Model\Answer');
					
				/* @var $field In2code\Powermail\Domain\Model\Field */
				$field = $this->objectManager->get('In2code\Powermail\Domain\Model\Field');
					
				$field->setTitle(LocalizationUtility::translate('file', 'powermailpdf'));
				$field->setType('file');
					
				$answer->setField($field);
				$answer->setValue(basename($powermailFilePath));
				$mail->addAnswer($answer);
				
			}
		
		
			
		
			//Addds a field for the email link
			/* @var $answer In2code\Powermail\Domain\Model\Answer */
			#$answer = $this->objectManager->get('In2code\Powermail\Domain\Model\Answer');
			/* @var $field In2code\Powermail\Domain\Model\Field */
			#$field = $this->objectManager->get('In2code\Powermail\Domain\Model\Field');
			#$field->setType('mailDownloadLink');
			#$answer->setField($field);
			#$answer->setValue($_SERVER['HTTP_HOST'].'/'.$powermailFilePath);
			#$mail->addAnswer($answer);
			
		}
	}
}
?>
