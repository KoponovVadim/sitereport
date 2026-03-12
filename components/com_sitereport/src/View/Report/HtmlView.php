<?php

declare(strict_types=1);

namespace Joomla\Component\Sitereport\Site\View\Report;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $item;

    public function display($tpl = null): void
    {
        $this->item = $this->get('Item');

        parent::display($tpl);
    }
}
