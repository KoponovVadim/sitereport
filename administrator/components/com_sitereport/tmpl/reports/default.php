<?php

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');

$items = $this->items ?? [];
?>
<form action="<?php echo Route::_('index.php?option=com_sitereport&view=reports'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (empty($items)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span>
            <?php echo Text::_('COM_SITEREPORT_NO_REPORTS'); ?>
        </div>
    <?php else : ?>
        <table class="table" id="reportList">
            <thead>
                <tr>
                    <th width="1%" class="text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                    <th><?php echo Text::_('JGLOBAL_FIELD_ID_LABEL'); ?></th>
                    <th><?php echo Text::_('COM_SITEREPORT_DOMAIN'); ?></th>
                    <th>IP</th>
                    <th><?php echo Text::_('COM_SITEREPORT_HTTP_CODE'); ?></th>
                    <th><?php echo Text::_('COM_SITEREPORT_RESPONSE_TIME'); ?></th>
                    <th><?php echo Text::_('COM_SITEREPORT_SEO_SCORE'); ?></th>
                    <th>SSL</th>
                    <th><?php echo Text::_('COM_SITEREPORT_ANALYSIS_DATE'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item) : 
                    $data = json_decode($item->data ?? '{}', true);
                ?>
                <tr>
                    <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                    <td><?php echo (int) $item->id; ?></td>
                    <td><?php echo htmlspecialchars($item->domain, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($data['ip'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="badge badge-<?php echo $item->http_code >= 200 && $item->http_code < 300 ? 'success' : 'danger'; ?>"><?php echo (int) $item->http_code; ?></span></td>
                    <td><?php echo number_format((float) $item->response_time, 3); ?> s</td>
                    <td><span class="badge badge-<?php echo $item->seo_score >= 70 ? 'success' : ($item->seo_score >= 40 ? 'warning' : 'danger'); ?>"><?php echo (int) $item->seo_score; ?></span></td>
                    <td><?php echo !empty($data['ssl']['valid']) ? '<span class="icon-check text-success"></span>' : '<span class="icon-cancel text-danger"></span>'; ?></td>
                    <td><?php echo htmlspecialchars($item->created, ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
