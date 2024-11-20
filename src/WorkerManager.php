<?php

namespace aeroWorker;

class WorkerManager
{
    private \GearmanWorker $worker;
    private array $config;
    
    public function __construct(array $config = [])
    {
        $this->worker = new \GearmanWorker();
        $this->config = $config;
        
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 4730;
        $this->worker->addServer($host, $port);
    }
    
    public function start(): void
    {
        foreach (WorkerRegistry::getWorkers() as $workerClass) {
            /** @var WorkerInterface $worker */
            $worker = new $workerClass();
            $worker->register($this->worker);
            echo "Registered worker: " . $worker->getName() . "\n";
        }
        
        while ($this->worker->work()) {
            if ($this->worker->returnCode() != GEARMAN_SUCCESS) {
                throw new \RuntimeException("Failed: " . $this->worker->error());
            }
        }
    }
}