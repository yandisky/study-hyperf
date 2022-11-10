接着框架执行#5

### Hyperf\Server\CoroutineServer执行

1，Hyperf\Server\Command\StartServer#execute

- 获取ServerFactory，配置server的Event，Log，Config
- Hyperf\Server\ServerConfig的type，默认Hyperf\Server\Server，这个参数确定了ServerFactory定义server类
- config\server#type使用Hyperf\Server\CoroutineServer
- Hyperf\Server\Command\StartServer#configure触发Hyperf\Server\CoroutineServer#init
- Hyperf\Server\ServerFactory#start等于Hyperf\Server\CoroutineServer#start

2，Hyperf\Server\CoroutineServer#start

- 使用协程容器
- 执行Hyperf\Server\CoroutineServer#initServers=>3
- 循环$servers启动协程处理
- mainServerStarted 触发事件 MainCoroutineServerStart
- 触发事件 CoroutineServerStart
- $server->start();
- 触发事件 CoroutineServerStop

3，Hyperf\Server\CoroutineServer#initServers

- 根据server的type创建server
- ServerInterface::SERVER_HTTP=>Swoole\Coroutine\Http\Server
- ServerInterface::SERVER_WEBSOCKET=>Swoole\Coroutine\Server
- ServerInterface::SERVER_BASE=>Swoole\Server
- 执行Hyperf\Server\CoroutineServer#bindServerCallbacks=>4

4，Hyperf\Server\CoroutineServer#bindServerCallbacks

- 根据server的type进行不同事件处理
- ServerInterface::SERVER_HTTP处理Event::ON_REQUEST
- ServerInterface::SERVER_WEBSOCKET处理Event::ON_HAND_SHAKE
- ServerInterface::SERVER_BASE处理Event::ON_CONNECT，Event::ON_RECEIVE，Event::ON_CLOSE
- \Swoole\Coroutine\Http\Server#handle注册事件（启动协程处理请求，后续流程接着框架执行#9）
