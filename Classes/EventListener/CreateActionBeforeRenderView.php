<?php

namespace Undkonsorten\Powermailpdf\EventListener;

use FPDM;
use In2code\Powermail\Controller\FormController;
use In2code\Powermail\Domain\Model\Answer;
use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\Domain\Model\Mail;
use TYPO3\CMS\Core\Error\Exception;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Fluid\View\StandaloneView;
use In2code\Powermail\Events\FormControllerCreateActionBeforeRenderViewEvent;


/**
 * PDF handling.
 *
 */
final class CreateActionBeforeRenderView
{
    /** @var ResourceFactory */
    protected $resourceFactory;
    private StandaloneView $standaloneView;

    protected ?bool $encoding = null;

    public function __construct(ResourceFactory $resourceFactory, StandaloneView $standaloneView)
    {
        $this->resourceFactory = $resourceFactory;
        $this->standaloneView = $standaloneView;
    }

    /**
     * @param Mail $mail
     * @return File
     * @throws Exception
     */
    protected function generatePdf(Mail $mail)
    {

        $settings = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_powermailpdf.']['settings.'];
        $this->encoding = $settings['encoding']??'';

        /** @var Folder $folder */
        $folder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($settings['target.']['pdf']);

        // Include \FPDM library from phar file, if not included already (e.g. composer installation)
        if (!class_exists('\FPDM')) {
            @include 'phar://' . ExtensionManagementUtility::extPath('powermailpdf') . 'Resources/Private/PHP/fpdm.phar/vendor/autoload.php';
        }

        //Normal Fields
        $fieldMap = $settings['fieldMap.'];

        $answers = $mail->getAnswers();

        $fdfDataStrings = array();
        $pdfField_value = null;
        foreach ($fieldMap as $fieldID => $fieldConfig) {

            if (is_array($fieldConfig)) {
                $pdfField_type = $fieldConfig['type'];
                $pdfField_value = $fieldConfig['form_value'];
                $formField_name = $fieldConfig['form_name'];
            } else {
                $pdfField_type = 'text';
                $formField_name = $fieldConfig;
            }

            $pdfField_name = explode('.', $fieldID)[0];

            $fdfDataStrings[$pdfField_name] = 'k.A.';

            foreach ($answers as $answer) {

                if ($formField_name == $answer->getField()->getMarker()) {
                    if ($pdfField_type == 'text') {
                        $pdfField_value = $this->encodeValue($answer->getValue());
                    } elseif ($pdfField_type == 'checkbox') {
                        if ($answer->getValue() == $fieldConfig['form_value']) {
                            $pdfField_value = $this->encodeValue($fieldConfig['pdf_value']);
                        }
                    }
                } else {
                    continue;
                }

                if (!empty($pdfField_value)) {
                    $fdfDataStrings[$pdfField_name] = $pdfField_value;
                }
            }
        }

        // Variables
        if (isset($settings['variables.'])) {
            $variables = $settings['variables.'];

            if (!empty($variables)) {
                $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
                $variables = $typoScriptService->convertTypoScriptArrayToPlainArray($variables);
                $cObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
                foreach ($variables as $key => $item) {
                    $type = $item['_typoScriptNodeValue'];
                    unset($item['_typoScriptNodeValue']);
                    $fdfDataStrings[$key] = $cObject->cObjGetSingle($type, $item);
                }
            }
        }

        $pdfOriginal = GeneralUtility::getFileAbsFileName($GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_powermailpdf.']['settings.']['sourceFile']);

        if (!empty($pdfOriginal)) {
            $pdfFlatTempFile = (string) null;
            $info = pathinfo($pdfOriginal);
            $pdfFilename = basename($pdfOriginal, '.' . $info['extension']) . '_';
            $pdfTempFile = GeneralUtility::tempnam($pdfFilename, '.pdf');

            $pdf = new \FPDM($pdfOriginal);
            $pdf->useCheckboxParser = true;
            $pdf->Load($fdfDataStrings, !$this->encoding); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
            $pdf->Merge();
            $pdf->Output("F", GeneralUtility::getFileAbsFileName($pdfTempFile));

            if (isset($settings['flatten']) && isset($settings['flattenTool'])) {
                $pdfFlatTempFile = GeneralUtility::tempnam($pdfFilename, '.pdf');
                $tempFile = GeneralUtility::tempnam($pdfFilename, '.pdf');
                switch ($settings['flattenTool']) {
                    case 'gs':
                        // Flatten PDF with ghostscript
                        @shell_exec("gs -sDEVICE=pdfwrite -dSubsetFonts=false -dPDFSETTINGS=/default -dNOPAUSE -dBATCH -sOutputFile=" . $pdfFlatTempFile . " " . $pdfTempFile);
                        break;
                    case 'pdftocairo':
                        // Flatten PDF with pdftocairo
                        @shell_exec('pdftocairo -pdf ' . $pdfTempFile . ' ' . $pdfFlatTempFile);
                        break;
                    case 'pdftk':
                        // Flatten PDF with pdftk
                        @shell_exec('pdftk ' . $pdfTempFile . ' generate_fdf output ' . $tempFile);
                        @shell_exec('pdftk ' . $pdfTempFile . ' fill_form ' . $tempFile . ' output ' . $pdfFlatTempFile . ' flatten');
                        break;
                }
            }
        } else {
            throw new Exception("No pdf file is set in Typoscript. Please set tx_powermailpdf.settings.sourceFile if you want to use the filling feature.", 1417432239);
        }

        if (file_exists($pdfFlatTempFile)) {
            return $folder->addFile($pdfFlatTempFile);
        }

        return $folder->addFile($pdfTempFile);
    }

    /**
     * @param File $file
     * @param $label
     * @return mixed
     */
    protected function render(File $file, $label)
    {
        $settings = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_powermailpdf.']['settings.'];
        $templatePath = GeneralUtility::getFileAbsFileName($settings['template']);
        $this->standaloneView->setFormat('html');
        $this->standaloneView->setTemplatePathAndFilename($templatePath);
        $this->standaloneView->assignMultiple([
            'link' => $file->getPublicUrl(),
            'label' => $label
        ]);

        return $this->standaloneView->render();
    }

    /**
     *
     * @param FormControllerCreateActionBeforeRenderViewEvent $event
     * @throws Exception
     *
     */
    public function __invoke(FormControllerCreateActionBeforeRenderViewEvent $event): void
    {
        $settings = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_powermailpdf.']['settings.'];
        $mail = $event->getMail();
        $formController = $event->getFormController();

        if ($settings['enablePowermailPdf']) {
            if ($settings['sourceFile']) {
                if (!file_exists(GeneralUtility::getFileAbsFileName($settings['sourceFile']))) {
                    throw new \Exception("The file does not exist: " . $settings['sourceFile'] . " Please set correct path in plugin.tx_powermailpdf.settings.sourceFile", 1417520887);
                }
            }

            if ($settings['fillPdf']) {
                $powermailPdfFile = $this->generatePdf($mail);
            } else {
                $powermailPdfFile = null;
            }

            if ($settings['showDownloadLink']) {
                $label = LocalizationUtility::translate("download", "powermailpdf");
                //Adds a field for the download link at the thx site
                /* @var $answer Answer */
                $answer = GeneralUtility::makeInstance(Answer::class);
                /* @var $field Field */
                $field = GeneralUtility::makeInstance(Field::class);
                $field->setTitle(LocalizationUtility::translate('downloadLink', 'powermailpdf'));
                $field->setMarker('downloadLink');
                $field->setType('downloadLink');
                $answer->setField($field);
                $answer->setValue($this->render($powermailPdfFile, $label));
                $mail->addAnswer($answer);
            }

            if ($settings['email.']['attachFile']) {
                // set pdf filename for attachment via TypoScript
                $settings = $formController->getSettings();
                $settings['receiver']['addAttachment']['value'] = $powermailPdfFile->getForLocalProcessing(false);
                $settings['sender']['addAttachment']['value'] = $powermailPdfFile->getForLocalProcessing(false);
                $formController->setSettings($settings);
            }
        }
    }

    protected function encodeValue($value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        if ($this->encoding) {
            return iconv('UTF-8', $this->encoding, $value);
        } else {
            return $value;
        }
    }
}
