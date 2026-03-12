<?php

declare(strict_types=1);

namespace Joomla\Component\Sitereport\Administrator\View\Report;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $item;

    public function display($tpl = null): void
    {
        $this->item = $this->get('Item');

        ToolbarHelper::title(Text::_('COM_SITEREPORT_REPORT_VIEW_TITLE'), 'eye');

        parent::display($tpl);
    }
}
