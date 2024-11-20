<?php
namespace aeroWorker;

class Client 
{
    private \GearmanClient $client;
    
    public function __construct(array $config = [])
    {
        $this->client = new \GearmanClient();
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 4730;
        $this->client->addServer($host, $port);
    }
    
    /**
     * Create async job that will be executed in the background
     * 
     * @param string $function
     * @param array $data
     * 
     * @return string
     */
    public function async(string $function, array $data): string
    {
        return $this->client->doBackground($function, json_encode($data));
    }
    
    /**
     * Create sync job that will be executed in the foreground
     * 
     * @param string $function
     * @param array $data
     * 
     * @return mixed
     */
    public function sync(string $function, array $data): mixed
    {
        return json_decode($this->client->doNormal($function, json_encode($data)), true);
    }
    
    /**
     * Create high priority async job that will be executed in the background
     * 
     * @param string $function
     * @param array $data
     * 
     * @return string
     */
    public function high(string $function, array $data): string
    {
        return $this->client->doHighBackground($function, json_encode($data));
    }
    
    /**
     * Create low priority async job that will be executed in the background
     * 
     * @param string $function
     * @param array $data
     * 
     * @return string
     */
    public function low(string $function, array $data): string
    {
        return $this->client->doLowBackground($function, json_encode($data));
    }

    /**
     * Create a task that will be executed in the background
     * 
     * @param string $function
     * @param array $data
     * 
     * @return string (task id)
     */
    public function asyncTask(string $function, array $data): string
    {
        return $this->client->addTaskBackground($function, json_encode($data));
    }

    /**
     * Create a tasks that will be executed in foreground
     * 
     * @param string $function
     * @param array $data
     * 
     * @return mixed
     */
    public function task(string $function, array $data): mixed
    {
        return json_decode($this->client->addTask($function, json_encode($data)), true);
    }

    /**
     * Run all tasks
     * 
     * @return (bool)
     */
    public function runTasks(): bool
    {
        return $this->client->runTasks();
    }
}