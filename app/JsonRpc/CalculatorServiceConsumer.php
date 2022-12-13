<?php

namespace App\JsonRpc;

use Hyperf\RpcClient\AbstractServiceClient;

class CalculatorServiceConsumer extends AbstractServiceClient implements CalculatorServiceInterface {
    protected $serviceName = 'CalculatorService';
    protected $protocol = 'jsonrpc-http';

    public function add(int $a, int $b): int {
        return $this->__request(__FUNCTION__, compact('a', 'b'));
    }
}