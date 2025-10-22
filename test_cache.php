<?php require_once 'src/Cache/RendererCache.php'; PDF_Builder\Cache\RendererCache::set('test', 'value'); echo PDF_Builder\Cache\RendererCache::get('test');
