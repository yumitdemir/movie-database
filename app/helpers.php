<?php

if (!function_exists('getEmbedUrl')) {
    /**
     * Convert a standard video URL to an embeddable URL
     * 
     * @param string $url The original URL
     * @return string The embed URL
     */
    function getEmbedUrl($url)
    {
        // YouTube
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches) || 
            preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/([0-9]+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }
        
        // Dailymotion
        if (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://www.dailymotion.com/embed/video/' . $matches[1];
        }
        
        // If no patterns match, return the original URL
        return $url;
    }
} 