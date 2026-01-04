<?php

namespace App\Controllers;

class SitemapController extends BaseController
{
    public function index()
    {
        // Load the sitemap view or file
        $sitemapContent = file_get_contents(ROOTPATH . 'public/sitemap.xml');
        
        // Set the content type to XML
        return $this->response->setContentType('application/xml')->setBody($sitemapContent);
    }
}