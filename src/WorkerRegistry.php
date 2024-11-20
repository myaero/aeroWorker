<?php

namespace aeroWorker;

class WorkerRegistry
{
    private static array $workers = [];
    
    public static function register(string $workerClass): void
    {
        if (!class_exists($workerClass)) {
            throw new \RuntimeException("Worker class {$workerClass} does not exist");
        }
        
        if (!in_array(WorkerInterface::class, class_implements($workerClass))) {
            throw new \RuntimeException("Worker class must implement WorkerInterface");
        }
        
        self::$workers[] = $workerClass;
    }
    
    public static function getWorkers(): array
    {
        return self::$workers;
    }
}