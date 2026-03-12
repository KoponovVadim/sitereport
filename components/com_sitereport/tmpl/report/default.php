<?php

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

// Load UIKit
HTMLHelper::_('script', 'https://cdn.jsdelivr.net/npm/uikit@3.16.14/dist/js/uikit.min.js', [], ['defer' => true]);
HTMLHelper::_('script', 'https://cdn.jsdelivr.net/npm/uikit@3.16.14/dist/js/uikit-icons.min.js', [], ['defer' => true]);
HTMLHelper::_('stylesheet', 'https://cdn.jsdelivr.net/npm/uikit@3.16.14/dist/css/uikit.min.css');

$item   = $this->item;
$domain = Factory::getApplication()->getInput()->getString('domain', '');
$data   = $item ? json_decode($item->data ?? '{}', true) : [];
$app    = Factory::getApplication();
?>
<div class="com-sitereport uk-container">
    <h1 class="uk-heading-line"><span><?php echo Text::_('COM_SITEREPORT_REPORT_VIEW_TITLE'); ?></span></h1>

    <form method="get" class="uk-margin-medium-bottom">
        <input type="hidden" name="option" value="com_sitereport" />
        <input type="hidden" name="view" value="report" />
        <?php if ($app->getMenu()->getActive()) : ?>
        <input type="hidden" name="Itemid" value="<?php echo $app->getMenu()->getActive()->id; ?>" />
        <?php endif; ?>
        <div class="uk-inline uk-width-1-1">
            <span class="uk-form-icon" uk-icon="icon: world"></span>
            <input type="text" name="domain" class="uk-input uk-form-large"
                   value="<?php echo htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="example.com" required />
        </div>
        <button type="submit" class="uk-button uk-button-primary uk-button-large uk-margin-small-top uk-width-1-1">
            <span uk-icon="search"></span> <?php echo Text::_('COM_SITEREPORT_ANALYZE'); ?>
        </button>
    </form>

    <?php if ($domain !== '' && !$item) : ?>
        <div class="uk-alert-warning" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><span uk-icon="warning"></span> <?php echo Text::_('COM_SITEREPORT_REPORT_NOT_FOUND'); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($item) : ?>
        <div class="uk-child-width-1-2@m uk-grid-match" uk-grid>
            <!-- HTTP Info Card -->
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">HTTP Info</h3>
                    <dl class="uk-description-list">
                        <dt>IP Address</dt>
                        <dd><?php echo htmlspecialchars($data['ip'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></dd>
                        <dt>HTTP Code</dt>
                        <dd><span class="uk-badge <?php echo $item->http_code >= 200 && $item->http_code < 300 ? 'uk-badge-success' : 'uk-badge-danger'; ?>"><?php echo (int) $item->http_code; ?></span></dd>
                        <dt>Response Time</dt>
                        <dd><?php echo number_format((float) $item->response_time, 3); ?> s</dd>
                        <dt>GZIP</dt>
                        <dd><?php echo !empty($data['gzip']) ? '<span uk-icon="check" class="uk-text-success"></span> Yes' : '<span uk-icon="close" class="uk-text-danger"></span> No'; ?></dd>
                    </dl>
                </div>
            </div>

            <!-- SSL Card -->
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">SSL Certificate</h3>
                    <dl class="uk-description-list">
                        <dt>Status</dt>
                        <dd><?php echo !empty($data['ssl']['valid']) ? '<span uk-icon="check" class="uk-text-success"></span> Valid' : '<span uk-icon="close" class="uk-text-danger"></span> Invalid'; ?></dd>
                        <?php if (!empty($data['ssl']['issuer'])) : ?>
                        <dt>Issuer</dt>
                        <dd><?php echo htmlspecialchars($data['ssl']['issuer'], ENT_QUOTES, 'UTF-8'); ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($data['ssl']['expires'])) : ?>
                        <dt>Expires</dt>
                        <dd><?php echo htmlspecialchars($data['ssl']['expires'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int) $data['ssl']['days_left']; ?> days left)</dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- HTML Card -->
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">HTML Analysis</h3>
                    <dl class="uk-description-list">
                        <dt>Title</dt>
                        <dd><?php echo !empty($data['title']) ? htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8') : '<span class="uk-text-muted">Missing</span>'; ?></dd>
                        <dt>Meta Description</dt>
                        <dd><?php echo !empty($data['meta_description']) ? htmlspecialchars($data['meta_description'], ENT_QUOTES, 'UTF-8') : '<span class="uk-text-muted">Missing</span>'; ?></dd>
                        <dt>H1 Tags</dt>
                        <dd><?php echo (int) ($data['h1_count'] ?? 0); ?></dd>
                        <dt>H2 Tags</dt>
                        <dd><?php echo (int) ($data['h2_count'] ?? 0); ?></dd>
                        <dt>Word Count</dt>
                        <dd><?php echo number_format((int) ($data['word_count'] ?? 0)); ?></dd>
                        <dt>Text Length</dt>
                        <dd><?php echo number_format((int) ($data['text_length'] ?? 0)); ?> chars</dd>
                        <dt>Encoding</dt>
                        <dd><?php echo htmlspecialchars($data['encoding'] ?? 'UTF-8', ENT_QUOTES, 'UTF-8'); ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Images Card -->
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">Images</h3>
                    <dl class="uk-description-list">
                        <dt>Total Images</dt>
                        <dd><?php echo (int) ($data['total_images'] ?? 0); ?></dd>
                        <dt>Images without ALT</dt>
                        <dd class="<?php echo ($data['images_without_alt'] ?? 0) > 0 ? 'uk-text-danger' : 'uk-text-success'; ?>">
                            <?php echo (int) ($data['images_without_alt'] ?? 0); ?>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Links Card -->
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">Links</h3>
                    <dl class="uk-description-list">
                        <dt>Total Links</dt>
                        <dd><?php echo (int) ($data['total_links'] ?? 0); ?></dd>
                        <dt>Internal Links</dt>
                        <dd><?php echo (int) ($data['internal_links'] ?? 0); ?></dd>
                        <dt>External Links</dt>
                        <dd><?php echo (int) ($data['external_links'] ?? 0); ?></dd>
                    </dl>
                </div>
            </div>

            <!-- SEO Card -->
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">SEO</h3>
                    <dl class="uk-description-list">
                        <dt>SEO Score</dt>
                        <dd>
                            <div class="uk-flex uk-flex-middle">
                                <progress class="uk-progress" value="<?php echo (int) $item->seo_score; ?>" max="100"></progress>
                                <span class="uk-margin-small-left"><?php echo (int) $item->seo_score; ?>/100</span>
                            </div>
                        </dd>
                        <dt>Open Graph Tags</dt>
                        <dd><?php echo (int) ($data['og_tags'] ?? 0); ?></dd>
                        <dt>robots.txt</dt>
                        <dd><?php echo !empty($data['robots_txt']) ? '<span uk-icon="check" class="uk-text-success"></span> Found' : '<span uk-icon="close" class="uk-text-danger"></span> Not found'; ?></dd>
                        <dt>sitemap.xml</dt>
                        <dd><?php echo !empty($data['sitemap_xml']) ? '<span uk-icon="check" class="uk-text-success"></span> Found' : '<span uk-icon="close" class="uk-text-danger"></span> Not found'; ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Security Card -->
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">Security</h3>
                    <dl class="uk-description-list">
                        <dt>HSTS</dt>
                        <dd><?php echo !empty($data['hsts']) ? '<span uk-icon="check" class="uk-text-success"></span> Enabled' : '<span uk-icon="close" class="uk-text-danger"></span> Disabled'; ?></dd>
                        <dt>Content Security Policy</dt>
                        <dd><?php echo !empty($data['csp']) ? '<span uk-icon="check" class="uk-text-success"></span> Enabled' : '<span uk-icon="close" class="uk-text-danger"></span> Disabled'; ?></dd>
                        <dt>X-Frame-Options</dt>
                        <dd><?php echo !empty($data['x_frame_options']) ? htmlspecialchars($data['x_frame_options'], ENT_QUOTES, 'UTF-8') : '<span class="uk-text-muted">Not set</span>'; ?></dd>
                        <dt>X-Content-Type-Options</dt>
                        <dd><?php echo !empty($data['x_content_type_options']) ? htmlspecialchars($data['x_content_type_options'], ENT_QUOTES, 'UTF-8') : '<span class="uk-text-muted">Not set</span>'; ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Analysis Date Card -->
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title">Analysis Info</h3>
                    <dl class="uk-description-list">
                        <dt>Domain</dt>
                        <dd><strong><?php echo htmlspecialchars($item->domain, ENT_QUOTES, 'UTF-8'); ?></strong></dd>
                        <dt>Analysis Date</dt>
                        <dd><?php echo htmlspecialchars($item->created, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
