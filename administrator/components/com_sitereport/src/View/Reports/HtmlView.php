<?php

declare(strict_types=1);

namespace Joomla\Component\Sitereport\Administrator\View\Reports;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        ToolbarHelper::title(Text::_('COM_SITEREPORT_REPORTS_TITLE'), 'list');
        ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'reports.delete');

        parent::display($tpl);
    }
}
