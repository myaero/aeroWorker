<?php

namespace aeroWorker;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use ReflectionClass;

class WorkerAutoLoader
{
    public static function loadWorkers(string $directory): void
    {
        if (!is_dir($directory)) {
            throw new \RuntimeException("Directory '$directory' does not exist");
        }

        // Recursively iterate through all PHP files in the given directory
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            // Debug: Print file being processed
            echo "Processing file: " . $file->getPathname() . PHP_EOL;

            // Include the file to load the class definition
            require_once $file->getPathname();

            // Get the full class name including namespace
            $className = self::getClassFullNameFromFile($file->getPathname());

            // Debug: Print found class name
            echo "Found class name: " . ($className ?? 'null') . PHP_EOL;

            if ($className && class_exists($className)) {
                $reflection = new ReflectionClass($className);

                // Debug: Print interface check results
                echo "Class $className implements WorkerInterface: " .
                    ($reflection->implementsInterface(WorkerInterface::class) ? 'yes' : 'no') . PHP_EOL;
                echo "Class $className is abstract: " .
                    ($reflection->isAbstract() ? 'yes' : 'no') . PHP_EOL;

                // Check if the class implements WorkerInterface and is not abstract
                if ($reflection->implementsInterface(WorkerInterface::class) && !$reflection->isAbstract()) {
                    echo "Registering worker: $className" . PHP_EOL;
                    WorkerRegistry::register($className);
                }
            }

            echo "-------------------" . PHP_EOL;
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
                    continue;
                }
                if ($gettingNamespace) {
                    if ($token[0] === T_NAME_QUALIFIED) {
                        $namespace = $token[1];
                        $gettingNamespace = false;
                    } elseif ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR) {
                        $namespace .= $token[1];
                    }
                }
                if ($token[0] === T_CLASS) {
                    $gettingClass = true;
                    continue;
                }
                if ($gettingClass && $token[0] === T_STRING) {
                    $class = $token[1];
                    break;
                }
            } elseif ($gettingNamespace && ($token === ';' || $token === '{')) {
                $gettingNamespace = false;
            }
        }

        // Debug the namespace and class extraction
        echo "Extracted namespace: " . ($namespace ?: 'none') . PHP_EOL;
        echo "Extracted class: " . ($class ?: 'none') . PHP_EOL;

        if ($namespace && $class) {
            return $namespace . '\\' . $class;
        }

        return $class ?: null;
    }
}
