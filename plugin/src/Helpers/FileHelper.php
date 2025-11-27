<?php
/**
 * File Helper Utilities
 *
 * @package PDF_Builder
 * @subpackage Helpers
 */

namespace PDF_Builder\Helpers;

/**
 * FileHelper class for file system operations
 */
class FileHelper {

    /**
     * Calculate the total size of a directory recursively
     *
     * @param string $directory Path to the directory
     * @return int Total size in bytes
     */
    public static function getDirectorySize($directory) {
        $size = 0;

        if (!is_dir($directory)) {
            return $size;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Format file size in human readable format
     *
     * @param int $bytes Size in bytes
     * @param int $precision Number of decimal places
     * @return string Formatted size
     */
    public static function formatFileSize($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get file extension from filename
     *
     * @param string $filename Filename
     * @return string File extension (lowercase)
     */
    public static function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Check if file is an image
     *
     * @param string $filename Filename
     * @return bool True if file is an image
     */
    public static function isImageFile($filename) {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        return in_array(self::getFileExtension($filename), $imageExtensions);
    }

    /**
     * Check if file is a PDF
     *
     * @param string $filename Filename
     * @return bool True if file is a PDF
     */
    public static function isPdfFile($filename) {
        return self::getFileExtension($filename) === 'pdf';
    }

    /**
     * Safely delete a file or directory recursively
     *
     * @param string $path Path to delete
     * @return bool True on success
     */
    public static function deleteRecursive($path) {
        if (!file_exists($path)) {
            return true;
        }

        if (is_file($path)) {
            return unlink($path);
        }

        if (is_dir($path)) {
            $items = new \FilesystemIterator($path);
            foreach ($items as $item) {
                if (!self::deleteRecursive($item->getPathname())) {
                    return false;
                }
            }
            return rmdir($path);
        }

        return false;
    }

    /**
     * Create directory recursively with proper permissions
     *
     * @param string $path Directory path
     * @param int $permissions Directory permissions (default 0755)
     * @return bool True on success
     */
    public static function createDirectory($path, $permissions = 0755) {
        if (is_dir($path)) {
            return true;
        }

        return mkdir($path, $permissions, true);
    }

    /**
     * Get list of files in directory with optional filtering
     *
     * @param string $directory Directory path
     * @param array $extensions Array of file extensions to filter (optional)
     * @param bool $recursive Whether to search recursively
     * @return array Array of file paths
     */
    public static function getFilesInDirectory($directory, $extensions = [], $recursive = false) {
        $files = [];

        if (!is_dir($directory)) {
            return $files;
        }

        $iterator = $recursive
            ? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS))
            : new \DirectoryIterator($directory);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();

                // Filter by extensions if provided
                if (!empty($extensions)) {
                    $extension = self::getFileExtension($filename);
                    if (!in_array($extension, $extensions)) {
                        continue;
                    }
                }

                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}