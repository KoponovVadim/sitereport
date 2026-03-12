<?php

declare(strict_types=1);

namespace Joomla\Component\Sitereport\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;

class ReportModel extends AdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        return false;
    }
}
