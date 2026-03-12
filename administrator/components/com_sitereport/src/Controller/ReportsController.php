<?php

declare(strict_types=1);

namespace Joomla\Component\Sitereport\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class ReportsController extends AdminController
{
    protected $text_prefix = 'COM_SITEREPORT';

    public function getModel($name = 'Report', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
