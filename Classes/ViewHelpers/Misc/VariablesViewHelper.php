<?php
namespace Undkonsorten\Powermailpdf\ViewHelpers\Misc;

// use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Utility\ArrayUtility;
use In2code\Powermail\Utility\TemplateUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Parses Variables for powermail
 *
 * @package TYPO3
 * @subpackage Fluid
 */
class VariablesViewHelper extends AbstractViewHelper
{

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var \In2code\Powermail\Domain\Repository\MailRepository
     * @inject
     */
    protected $mailRepository;

    /**
     * Configuration
     *
     * @var array
     */
    protected $settings = [];

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('mail', Mail::class, 'Mail', true);
        $this->registerArgument('type', 'string', '"web" or "mail"', false, 'web');
        $this->registerArgument('function', 'string', 'createAction, senderMail, receiverMail', false, 'createAction');
    }

    /**
     * Enable variables within variable {powermail_rte} - so string will be parsed again
     *
     * @return string
     */
    public function render(): string
    {
        $mail = $this->arguments['mail'];
        $type = $this->arguments['type'];
        $function = $this->arguments['function'];
        /** @var StandaloneView $parseObject */
        $parseObject = $this->objectManager->get(StandaloneView::class);
        $parseObject->setTemplateSource($this->removePowermailAllParagraphTagWrap($this->renderChildren()));

        $variables = $this->mailRepository->getVariablesWithMarkersFromMail($mail);

        foreach ($variables as $key => $value) {
            if($key != 'downloadLink') {
                $variables[$key] = html_entity_decode($value);
            } else {
                // I don't know if this is a problem for (e. g.) TYPO3 with baseURL set - so:
                // If link starts with slash change this to a full abs link/url
                $query = '/';
                $link = explode('<a href="', $value)[1];
                if(substr($link, 0, strlen($query)) === $query) {
                    $variables['downloadLink'] = '<a href="'. GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . explode('<a href="', $value)[1];
                }
            }
        }

        $parseObject->assignMultiple(
            $variables
        );
        $parseObject->assignMultiple(
            ArrayUtility::htmlspecialcharsOnArray($this->mailRepository->getLabelsWithMarkersFromMail($mail))
        );
        $parseObject->assign('powermail_all', TemplateUtility::powermailAll($mail, $type, $this->settings, $function));

        return html_entity_decode($parseObject->render(), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Helper method which triggers the rendering of everything between the
     * opening and the closing tag. In addition change -&gt; to ->
     *
     * @return mixed The finally rendered child nodes.
     */
    public function renderChildren()
    {
        $content = parent::renderChildren();
        $content = str_replace('-&gt;', '->', $content);
        return $content;
    }

    /**
     * Get renderChildren
     *        <p>{powermail_all}</p> =>
     *            {powermail_all}
     *
     * @param string $content
     * @return string
     */
    protected function removePowermailAllParagraphTagWrap($content)
    {
        return preg_replace('#<p([^>]*)>\s*{powermail_all}\s*<\/p>#', '{powermail_all}', $content);
    }

    /**
     * Init to get TypoScript Configuration
     *
     * @return void
     */
    public function initialize()
    {
        $typoScriptSetup = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        if (!empty($typoScriptSetup['plugin.']['tx_powermail.']['settings.']['setup.'])) {
            $this->settings = GeneralUtility::removeDotsFromTS(
                $typoScriptSetup['plugin.']['tx_powermail.']['settings.']['setup.']
            );
        }
    }
}
