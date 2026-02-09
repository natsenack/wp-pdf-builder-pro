<?php

/**
 * PDF Builder Pro - File System Helper
 * Responsable des opérations de système de fichiers
 */

namespace PDF_Builder\Admin\Helpers;

/**
 * Classe responsable des opérations de système de fichiers
 */
class FileSystemHelper
{
    /**
     * Calcule la taille d'un répertoire récursivement
     *
     * @param string $directory Chemin du répertoire
     * @return int Taille en octets
     */
    public function getDirectorySize($directory)
    {
        $size = 0;

        if (!is_dir($directory)) {
            return 0;
        }

        $files = @scandir($directory);

        if ($files === false) {
            return 0;
        }

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = $directory . '/' . $file;

                if (is_dir($path)) {
                    $size += $this->getDirectorySize($path);
                } elseif (is_file($path)) {
                    $size += @filesize($path);
                }
            }
        }

        return $size;
    }

    /**
     * Formate une taille en octets en format lisible
     *
     * @param int $bytes Taille en octets
     * @param int $precision Nombre de décimales
     * @return string Taille formatée
     */
    public function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Supprime un répertoire récursivement
     *
     * @param string $directory Chemin du répertoire
     * @return bool Succès de la suppression
     */
    public function deleteDirectory($directory)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = @scandir($directory);

        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = $directory . '/' . $file;

                if (is_dir($path)) {
                    $this->deleteDirectory($path);
                } else {
                    @unlink($path);
                }
            }
        }

        return @rmdir($directory);
    }

    /**
     * Nettoie les fichiers temporaires d'un répertoire
     *
     * @param string $directory Chemin du répertoire
     * @param int $age_seconds Âge minimum des fichiers à supprimer (en secondes)
     * @return array Résumé du nettoyage (fichiers supprimés, octets libérés)
     */
    public function cleanOldFiles($directory, $age_seconds = 86400)
    {
        $cleared_files = 0;
        $total_size = 0;

        if (!is_dir($directory)) {
            return [
                'files' => $cleared_files,
                'size' => $total_size
            ];
        }

        $files = @glob($directory . '*');

        if ($files === false) {
            return [
                'files' => $cleared_files,
                'size' => $total_size
            ];
        }

        $cutoff_time = time() - $age_seconds;

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff_time) {
                $file_size = @filesize($file);
                if (@unlink($file)) {
                    $cleared_files++;
                    $total_size += $file_size;
                }
            }
        }

        return [
            'files' => $cleared_files,
            'size' => $total_size
        ];
    }
}



