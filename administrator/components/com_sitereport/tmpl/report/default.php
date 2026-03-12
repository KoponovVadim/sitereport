<?php

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$item = $this->item;

if (!$item) : ?>
    <div class="alert alert-warning"><?php echo Text::_('COM_SITEREPORT_REPORT_NOT_FOUND'); ?></div>
<?php return; endif;

$data = json_decode($item->data ?? '{}', true);
?>
<h2><?php echo htmlspecialchars($item->domain, ENT_QUOTES, 'UTF-8'); ?></h2>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">HTTP Info</div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">IP Address</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($data['ip'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></dd>
                    <dt class="col-sm-4">HTTP Code</dt>
                    <dd class="col-sm-8"><?php echo (int) $item->http_code; ?></dd>
                    <dt class="col-sm-4">Response Time</dt>
                    <dd class="col-sm-8"><?php echo number_format((float) $item->response_time, 3); ?> s</dd>
                    <dt class="col-sm-4">GZIP</dt>
                    <dd class="col-sm-8"><?php echo !empty($data['gzip']) ? 'Yes' : 'No'; ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">SSL Certificate</div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8"><?php echo !empty($data['ssl']['valid']) ? '<span class="text-success">Valid</span>' : '<span class="text-danger">Invalid</span>'; ?></dd>
                    <?php if (!empty($data['ssl']['issuer'])) : ?>
                    <dt class="col-sm-4">Issuer</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($data['ssl']['issuer'], ENT_QUOTES, 'UTF-8'); ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($data['ssl']['expires'])) : ?>
                    <dt class="col-sm-4">Expires</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($data['ssl']['expires'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int) $data['ssl']['days_left']; ?> days left)</dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">SEO</div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">SEO Score</dt>
                    <dd class="col-sm-8"><?php echo (int) $item->seo_score; ?>/100</dd>
                    <dt class="col-sm-4">Title</dt>
                    <dd class="col-sm-8"><?php echo !empty($data['title']) ? htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Missing</span>'; ?></dd>
                    <dt class="col-sm-4">Meta Description</dt>
                    <dd class="col-sm-8"><?php echo !empty($data['meta_description']) ? htmlspecialchars($data['meta_description'], ENT_QUOTES, 'UTF-8') : '<span class="text-muted">Missing</span>'; ?></dd>
                    <dt class="col-sm-4">H1 Tags</dt>
                    <dd class="col-sm-8"><?php echo (int) ($data['h1_count'] ?? 0); ?></dd>
                    <dt class="col-sm-4">Open Graph</dt>
                    <dd class="col-sm-8"><?php echo (int) ($data['og_tags'] ?? 0); ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">Security</div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">HSTS</dt>
                    <dd class="col-sm-8"><?php echo !empty($data['hsts']) ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>'; ?></dd>
                    <dt class="col-sm-4">CSP</dt>
                    <dd class="col-sm-8"><?php echo !empty($data['csp']) ? '<span class="text-success">Enabled</span>' : '<span class="text-danger">Disabled</span>'; ?></dd>
                    <dt class="col-sm-4">X-Frame-Options</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($data['x_frame_options'] ?? 'Not set', ENT_QUOTES, 'UTF-8'); ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
