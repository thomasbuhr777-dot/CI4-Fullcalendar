<?php

if (! function_exists('vite')) {

    function vite(string $entry): string
    {
        $manifestPath = FCPATH . 'build/.vite/manifest.json';

        if (! file_exists($manifestPath)) {
            return '<!-- Vite Manifest nicht gefunden -->';
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        $entryFile = "resources/js/{$entry}.js";

        if (! isset($manifest[$entryFile])) {
            return "<!-- {$entry} nicht im Manifest -->";
        }

        $asset = $manifest[$entryFile];
        $html = '';

        if (! empty($asset['css'])) {
            foreach ($asset['css'] as $css) {
                $html .= '<link rel="stylesheet" href="' . base_url('build/' . $css) . '">' . PHP_EOL;
            }
        }

        $html .= '<script type="module" src="' . base_url('build/' . $asset['file']) . '"></script>';

        return $html;
    }
}