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

        $settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailpdf.']['settings.'];

        /** @var Folder $folder */
        $folder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($settings['target.']['pdf']);

        // Include \FPDM library from phar file, if not included already (e.g. composer installation)
        if (!class_exists('\FPDM')) {
            @include 'phar://' . ExtensionManagementUtility::extPath('powermailpdf') . 'Resources/Private/PHP/fpdm.phar/vendor/autoload.php';
        }

        //Normal Fields
        $fieldMap = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailpdf.']['settings.']['fieldMap.'];

        $answers = $mail->getAnswers();
        $fdfDataStrings = array();

        foreach ($fieldMap as $key => $value) {
            foreach ($answers as $answer) {
                if ($value == $answer->getField()->getMarker()) {
                    $fdfDataStrings[$key] = $answer->getValue();
                }
            }
        }

        $pdfOriginal = GeneralUtility::getFileAbsFileName($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailpdf.']['settings.']['sourceFile']);

        if (!empty($pdfOriginal)) {
            $info = pathinfo($pdfOriginal);
            $pdfFilename = basename($pdfOriginal, '.' . $info['extension']) . '_';
            $pdfTempFile = GeneralUtility::tempnam($pdfFilename, '.pdf');
            $pdf = new FPDM($pdfOriginal);
            $pdf->Load($fdfDataStrings, true); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
            $pdf->Merge();
            $pdf->Output("F", GeneralUtility::getFileAbsFileName($pdfTempFile));
        } else {
            throw new Exception("No pdf file is set in Typoscript. Please set tx_powermailpdf.settings.sourceFile if you want to use the filling feature.", 1417432239);
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

        $settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailpdf.']['settings.'];
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
        $settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermailpdf.']['settings.'];
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
}
