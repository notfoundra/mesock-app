<?php

if (! function_exists('clean_comment_html')) {
    /**
     * Bersihin HTML dari CKEditor sebelum disimpen — cuma izinin tag yang aman.
     */
    function clean_comment_html(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,s,ul,ol,li,a[href],img[src|alt|width|height],table,thead,tbody,tr,td,th,h1,h2,h3,h4,blockquote,code,pre');
        $config->set('HTML.TargetBlank', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
        $config->set('Cache.SerializerPath', WRITEPATH . 'cache');

        $purifier = new \HTMLPurifier($config);

        return $purifier->purify($html);
    }
}
