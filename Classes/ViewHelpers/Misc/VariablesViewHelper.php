<?php
declare(strict_types=1);
namespace Undkonsorten\Powermailpdf\ViewHelpers\Misc;

use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Domain\Repository\MailRepository;
use In2code\Powermail\Domain\Service\ConfigurationService;
use In2code\Powermail\Utility\ArrayUtility;
use In2code\Powermail\Utility\TemplateUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class VariablesViewHelper
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
     * Configuration
     *
     * @var array
     */
    protected array $settings = [];

    /** @var ConfigurationService */
    protected mixed $configurationService;

    /** @var MailRepository */
    protected mixed $mailRepository;

    /** @var StandaloneView */
    protected mixed $standaloneView;

    public function __construct()
    {
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $this->mailRepository = GeneralUtility::makeInstance(MailRepository::class);
        $this->standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
    }

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
     * @throws InvalidConfigurationTypeException
     */
    public function render(): string
    {
        $mail = $this->arguments['mail'];
        $type = $this->arguments['type'];
        $function = $this->arguments['function'];
        $this->standaloneView->setTemplateSource($this->removePowermailAllParagraphTagWrap($this->renderChildren()));

        $variables = $this->mailRepository->getVariablesWithMarkersFromMail($mail);
        foreach ($variables as $key => $value){
            if($key != 'downloadLink'){
                $variables[$key] = html_entity_decode((string)$value);
            }
        }
        $this->standaloneView->assignMultiple(
            $variables
        );
        $this->standaloneView->assignMultiple(
            ArrayUtility::htmlspecialcharsOnArray($this->mailRepository->getLabelsWithMarkersFromMail($mail))
        );
        $this->standaloneView->assign('powermail_all', TemplateUtility::powermailAll($mail, $type, $this->settings, $function));
        return html_entity_decode($this->standaloneView->render(), ENT_QUOTES, 'UTF-8');
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
    protected function removePowermailAllParagraphTagWrap(string $content): string
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
        $this->settings = $this->configurationService->getTypoScriptSettings();
    }
}
