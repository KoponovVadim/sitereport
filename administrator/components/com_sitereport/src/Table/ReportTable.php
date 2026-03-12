<?php

declare(strict_types=1);

namespace Joomla\Component\Sitereport\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class ReportTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__sitereport_reports', 'id', $db);
    }
}
