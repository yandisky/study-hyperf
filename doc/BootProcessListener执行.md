Hyperf\Process\Listener\BootProcessListener

### 监听事件BeforeMainServerStart，MainCoroutineServerStart

- 获取所有process，通过server.processes，config.processes，Hyperf\Process\ProcessManager，注解Process
- 循环process，触发Hyperf\Process\AbstractProcess#bind

### Hyperf\Process\AbstractProcess#bind

- 区分 Swoole\Coroutine\Server 和 Swoole\Coroutine\Http\Server，执行Hyperf\Process\AbstractProcess#bindCoroutineServer#1
- 区分 Swoole\Server，执行Hyperf\Process\AbstractProcess#bindServer#2

### 1=>Hyperf\Process\AbstractProcess#bindCoroutineServer

- 根据nums启用多个协程运行function
- function内部执行以下
- 触发事件 BeforeCoroutineHandle
- 运行process->handle()
- 触发事件 AfterCoroutineHandle

### 1=>通过process->handle()找到两个使用实例

- Hyperf\AsyncQueue\Process\ConsumerProcess#handle
- Hyperf\Amqp\ConsumerManager#createProcess

### 1=>Hyperf\AsyncQueue\Process\ConsumerProcess#handle

- App\Process\AsyncQueueConsumer使用注解@Process，所以Hyperf\Process\Listener\BootProcessListener可以获取到，
  并且触发Hyperf\AsyncQueue\Process\ConsumerProcess#handle
- 启动异步队列消费，通过Hyperf\AsyncQueue\Driver\DriverFactory
  获取config.async_queue.default.driver（Hyperf\AsyncQueue\Driver\RedisDriver）
- 执行Hyperf\AsyncQueue\Driver\RedisDriver#consume（while循环ProcessManager::isRunning()来不断消费）

### 1=>Hyperf\Amqp\ConsumerManager#createProcess

- Hyperf\Amqp\Listener\BeforeMainServerStartListener监听BeforeMainServerStart， MainCoroutineServerStart，
  触发Hyperf\Amqp\ConsumerManager#run
- 获取注解Hyperf\Amqp\Annotation\Consumer，将其用AbstractProcess类包装，通过ProcessManager::register，
  所以Hyperf\Process\Listener\BootProcessListener可以获取到
- 执行handle触发Hyperf\Amqp\Consumer#consume

### 2=>Hyperf\Process\AbstractProcess#bindServer

- 根据nums启用多个Swoole\Process类包装function
- function内部执行以下
- 触发事件 BeforeProcessHandle
- 运行process->handle()，其process通过enableCoroutine可以使用协程运行，详细Hyperf\Process\AbstractProcess#listen
- 触发事件 AfterProcessHandle
- 将process添加到$server->addProcess($process)