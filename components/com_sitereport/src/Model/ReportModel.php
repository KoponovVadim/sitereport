<?php

declare(strict_types=1);

namespace Joomla\Component\Sitereport\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

class ReportModel extends BaseDatabaseModel
{
    public function getItem(): ?object
    {
        $domain = trim((string) Factory::getApplication()->getInput()->getString('domain', ''));

        if ($domain === '') {
            return null;
        }

        $domain = $this->normalizeDomain($domain);

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sitereport_reports'))
            ->where($db->quoteName('domain') . ' = :domain')
            ->bind(':domain', $domain, ParameterType::STRING);

        $db->setQuery($query);
        $row = $db->loadObject();

        if ($row) {
            return $row;
        }

        // Analyse domain and store result
        try {
            $result = $this->analyzeDomain($domain);
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage('Error analyzing domain: ' . $e->getMessage(), 'error');
            return null;
        }

        $now = Factory::getDate()->toSql();

        $columns = ['domain', 'data', 'seo_score', 'http_code', 'response_time', 'created', 'updated'];
        $values  = ':domain, :data, :seo_score, :http_code, :response_time, :created, :updated';

        $dataJson     = json_encode($result['data'], JSON_UNESCAPED_UNICODE);
        $seoScore     = (int) $result['seo_score'];
        $httpCode     = (int) $result['http_code'];
        $responseTime = (string) round($result['response_time'], 3);

        $insert = $db->getQuery(true)
            ->insert($db->quoteName('#__sitereport_reports'))
            ->columns($db->quoteName($columns))
            ->values($values)
            ->bind(':domain', $domain, ParameterType::STRING)
            ->bind(':data', $dataJson, ParameterType::STRING)
            ->bind(':seo_score', $seoScore, ParameterType::INTEGER)
            ->bind(':http_code', $httpCode, ParameterType::INTEGER)
            ->bind(':response_time', $responseTime, ParameterType::STRING)
            ->bind(':created', $now, ParameterType::STRING)
            ->bind(':updated', $now, ParameterType::STRING);

        $db->setQuery($insert);
        
        try {
            $db->execute();
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage('Error saving report: ' . $e->getMessage(), 'error');
            return null;
        }

        // Reload
        $db->setQuery($query);

        return $db->loadObject();
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = trim($domain);
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#/.*$#', '', $domain);

        return strtolower($domain);
    }

    private function analyzeDomain(string $domain): array
    {
        $url     = 'https://' . $domain;
        $timeout = 10;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_USERAGENT      => 'SitereportBot/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER         => true,
            CURLOPT_CERTINFO       => true,
        ]);

        $start    = microtime(true);
        $response = curl_exec($ch);
        $time     = microtime(true) - $start;
        $code     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $info     = curl_getinfo($ch);
        curl_close($ch);

        if ($response === false) {
            $response = '';
        }

        // Split headers and body
        $headerSize = $info['header_size'] ?? 0;
        $headersRaw = substr($response, 0, $headerSize);
        $body       = substr($response, $headerSize);

        // Parse headers
        $headers = $this->parseHeaders($headersRaw);

        // Get IP address
        $ip = gethostbyname($domain);

        // SSL info
        $ssl = $this->getSSLInfo($domain);

        // Parse HTML
        $htmlData = $this->parseHTML($body);

        // Check robots.txt
        $robotsTxt = $this->checkURL('https://' . $domain . '/robots.txt');

        // Check sitemap.xml
        $sitemapXml = $this->checkURL('https://' . $domain . '/sitemap.xml');

        // Calculate SEO score
        $seoScore = 0;
        if (!empty($htmlData['title'])) $seoScore += 10;
        if (!empty($htmlData['meta_description'])) $seoScore += 10;
        if ($htmlData['h1_count'] > 0) $seoScore += 10;
        if ($htmlData['og_tags'] > 0) $seoScore += 10;
        if ($code >= 200 && $code < 400) $seoScore += 10;
        if ($robotsTxt) $seoScore += 10;
        if ($sitemapXml) $seoScore += 10;
        if (!empty($ssl['valid'])) $seoScore += 10;
        if (!empty($headers['strict-transport-security'])) $seoScore += 5;
        if ($htmlData['images_without_alt'] == 0 && $htmlData['total_images'] > 0) $seoScore += 5;
        if (!empty($headers['content-encoding'])) $seoScore += 5;
        if (!empty($headers['x-frame-options'])) $seoScore += 5;

        $data = [
            'ip'                        => $ip,
            'http_code'                 => $code,
            'response_time'             => round($time, 3),
            'headers'                   => $headers,
            'ssl'                       => $ssl,
            'title'                     => $htmlData['title'],
            'meta_description'          => $htmlData['meta_description'],
            'h1_count'                  => $htmlData['h1_count'],
            'h2_count'                  => $htmlData['h2_count'],
            'word_count'                => $htmlData['word_count'],
            'text_length'               => $htmlData['text_length'],
            'total_images'              => $htmlData['total_images'],
            'images_without_alt'        => $htmlData['images_without_alt'],
            'total_links'               => $htmlData['total_links'],
            'internal_links'            => $htmlData['internal_links'],
            'external_links'            => $htmlData['external_links'],
            'og_tags'                   => $htmlData['og_tags'],
            'robots_txt'                => $robotsTxt,
            'sitemap_xml'               => $sitemapXml,
            'hsts'                      => !empty($headers['strict-transport-security']),
            'csp'                       => !empty($headers['content-security-policy']),
            'x_frame_options'           => $headers['x-frame-options'] ?? null,
            'x_content_type_options'    => $headers['x-content-type-options'] ?? null,
            'gzip'                      => !empty($headers['content-encoding']),
            'encoding'                  => $htmlData['encoding'],
        ];

        return [
            'domain'        => $domain,
            'data'          => $data,
            'seo_score'     => $seoScore,
            'http_code'     => $code,
            'response_time' => round($time, 3),
        ];
    }

    private function parseHeaders(string $headersRaw): array
    {
        $headers = [];
        $lines   = explode("\r\n", $headersRaw);

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }

        return $headers;
    }

    private function getSSLInfo(string $domain): array
    {
        $ssl = [
            'valid'      => false,
            'issuer'     => null,
            'expires'    => null,
            'days_left'  => null,
        ];

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer'       => false,
                'verify_peer_name'  => false,
            ],
        ]);

        $client = @stream_socket_client(
            'ssl://' . $domain . ':443',
            $errno,
            $errstr,
            5,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($client) {
            $params = stream_context_get_params($client);
            $cert   = $params['options']['ssl']['peer_certificate'] ?? null;

            if ($cert) {
                $certInfo = openssl_x509_parse($cert);
                
                if ($certInfo) {
                    $ssl['valid']     = true;
                    $ssl['issuer']    = $certInfo['issuer']['O'] ?? $certInfo['issuer']['CN'] ?? 'Unknown';
                    $ssl['expires']   = date('Y-m-d H:i:s', $certInfo['validTo_time_t']);
                    $ssl['days_left'] = floor(($certInfo['validTo_time_t'] - time()) / 86400);
                }
            }

            fclose($client);
        }

        return $ssl;
    }

    private function parseHTML(string $body): array
    {
        $data = [
            'title'              => '',
            'meta_description'   => '',
            'h1_count'           => 0,
            'h2_count'           => 0,
            'word_count'         => 0,
            'text_length'        => 0,
            'total_images'       => 0,
            'images_without_alt' => 0,
            'total_links'        => 0,
            'internal_links'     => 0,
            'external_links'     => 0,
            'og_tags'            => 0,
            'encoding'           => 'UTF-8',
        ];

        if ($body === '') {
            return $data;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($body);
        libxml_clear_errors();

        // Title
        $titles = $dom->getElementsByTagName('title');
        if ($titles->length > 0) {
            $data['title'] = trim($titles->item(0)->textContent);
        }

        // Meta tags
        foreach ($dom->getElementsByTagName('meta') as $meta) {
            $name     = strtolower($meta->getAttribute('name'));
            $property = strtolower($meta->getAttribute('property'));

            if ($name === 'description') {
                $data['meta_description'] = trim($meta->getAttribute('content'));
            }

            if (strpos($property, 'og:') === 0) {
                $data['og_tags']++;
            }

            if ($name === 'charset' || $meta->hasAttribute('charset')) {
                $data['encoding'] = $meta->getAttribute('charset') ?: $meta->getAttribute('content');
            }
        }

        // H1/H2
        $data['h1_count'] = $dom->getElementsByTagName('h1')->length;
        $data['h2_count'] = $dom->getElementsByTagName('h2')->length;

        // Images
        $images = $dom->getElementsByTagName('img');
        $data['total_images'] = $images->length;
        
        foreach ($images as $img) {
            if (!$img->hasAttribute('alt') || trim($img->getAttribute('alt')) === '') {
                $data['images_without_alt']++;
            }
        }

        // Links
        $links = $dom->getElementsByTagName('a');
        $data['total_links'] = $links->length;

        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            
            if (strpos($href, 'http://') === 0 || strpos($href, 'https://') === 0) {
                $data['external_links']++;
            } else {
                $data['internal_links']++;
            }
        }

        // Text analysis
        $text = strip_tags($body);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        $data['text_length'] = mb_strlen($text);
        $data['word_count']  = str_word_count($text);

        return $data;
    }

    private function checkURL(string $url): bool
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY         => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $code >= 200 && $code < 400;
    }
}
