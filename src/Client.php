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
    
    public function async(string $function, array $data): string
    {
        return $this->client->doBackground($function, json_encode($data));
    }
    
    public function sync(string $function, array $data): mixed
    {
        return json_decode($this->client->doNormal($function, json_encode($data)), true);
    }
    
    public function high(string $function, array $data): string
    {
        return $this->client->doHighBackground($function, json_encode($data));
    }
    
    public function low(string $function, array $data): string
    {
        return $this->client->doLowBackground($function, json_encode($data));
    }
}