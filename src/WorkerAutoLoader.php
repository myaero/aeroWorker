<?php

namespace aeroWorker;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use ReflectionClass;

class WorkerAutoLoader
{
    public static function loadWorkers(string $directory): void
    {
        // Recursively iterate through all PHP files in the given directory
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            // Include the file to load the class definition
            require_once $file->getPathname();

            // Get the full class name including namespace
            $className = self::getClassFullNameFromFile($file->getPathname());

            if ($className && class_exists($className)) {
                $reflection = new ReflectionClass($className);

                // Check if the class implements WorkerInterface and is not abstract
                if ($reflection->implementsInterface(WorkerInterface::class) && !$reflection->isAbstract()) {
                    WorkerRegistry::register($className);
                }
            }
        }
    }

    // Utility function to extract full class name (namespace + class name) from a file
    private static function getClassFullNameFromFile($filePath): ?string
    {
        $content = file_get_contents($filePath);
        $tokens = token_get_all($content);
        $namespace = '';
        $class = '';
        $gettingNamespace = false;
        $gettingClass = false;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] === T_NAMESPACE) {
                    $gettingNamespace = true;
                }
                if ($gettingNamespace && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)) {
                    $namespace .= $token[1];
                }
                if ($token[0] === T_CLASS) {
                    $gettingClass = true;
                }
                if ($gettingClass && $token[0] === T_STRING) {
                    $class = $token[1];
                    break;
                }
            } elseif ($token === ';' || $token === '{') {
                $gettingNamespace = false;
            }
        }

        if (!empty($namespace) && !empty($class)) {
            return $namespace . '\\' . $class;
        }

        return $class ?: null;
    }
}
