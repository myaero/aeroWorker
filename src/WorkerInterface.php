<?php

namespace aeroWorker;

interface WorkerInterface
{
    public function register(\GearmanWorker $worker): void;
    public function getName(): string;
}
