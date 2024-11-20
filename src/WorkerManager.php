<?php

namespace aeroWorker;

class WorkerManager
{
    private \GearmanWorker $worker;
    private array $config;
    private bool $shutdown = false;
    
    public function __construct(array $config = [])
    {
        $this->worker = new \GearmanWorker();
        $this->config = $config;
        
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 4730;
        
        if (!$this->worker->addServer($host, $port)) {
            throw new \RuntimeException("Failed to connect to Gearman server at {$host}:{$port}");
        }
        
        // Set timeout to prevent blocking forever
        $this->worker->setTimeout(1000);
    }
    
    public function start(): void
    {
        foreach (WorkerRegistry::getWorkers() as $workerClass) {
            /** @var WorkerInterface $worker */
            $worker = new $workerClass();
            $worker->register($this->worker);
            echo "Registered worker: " . $worker->getName() . "\n";
        }
        
        while (!$this->shutdown) {
            try {
                $ret = $this->worker->work();
                
                if ($ret === false) {
                    $code = $this->worker->returnCode();
                    
                    // Timeout is normal, other errors should be logged
                    if ($code !== GEARMAN_TIMEOUT) {
                        throw new \RuntimeException("Failed with code {$code}: " . $this->worker->error());
                    }
                }
            } catch (\Exception $e) {
                error_log(sprintf(
                    "[%s] Worker error: %s\n",
                    date('Y-m-d H:i:s'),
                    $e->getMessage()
                ));
                
                // Sleep briefly before retrying
                sleep(1);
            }
        }
    }
    
    public function shutdown(): void
    {
        echo "Shutting down worker..." . PHP_EOL;
        $this->shutdown = true;
    }
}