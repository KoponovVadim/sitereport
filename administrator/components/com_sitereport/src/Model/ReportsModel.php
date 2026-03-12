<?php

declare(strict_types=1);

namespace Joomla\Component\Sitereport\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class ReportsModel extends ListModel
{
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sitereport_reports'))
            ->order($db->quoteName('created') . ' DESC');

        return $query;
    }
}
