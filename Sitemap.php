<?php
namespace App\Controllers;

class Sitemap extends BaseController
{
    public function index()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://desakaliboja.my.id/</loc>
    <lastmod>2025-11-21</lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>https://desakaliboja.my.id/berita</loc>
    <lastmod>2025-11-21</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc>https://desakaliboja.my.id/produk</loc>
    <lastmod>2025-11-21</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
</urlset>';

        return $this->response->setContentType('application/xml')->setBody($xml);
    }
}