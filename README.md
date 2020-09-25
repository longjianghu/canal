## Canal Client

基于Swoft开发的Canal Client客户端

### 项目说明

使用Canal Server监听数据库变动,目前支持使用HTTP POST和方式和NSQ消息队列两种处理方式。

HTTP POST:当程序监听到变化后，使用HTTP POST的方式把数据提交到指定的地址。

NSQ 消息队列：把数据投递到NSQ消息队列里

#### 数据格式：

``

### 安装 Docker

curl -fsSL https://get.docker.com | bash -s docker --mirror Aliyun

usermod -aG docker  root

systemctl start docker

### Canal Server使用说明

https://github.com/alibaba/canal/wiki/Docker-QuickStart

### 配置说明

https://github.com/alibaba/canal/wiki/AdminGuide

### NSQ 消息队列

docker run --name nsqlookupd -p 4160:4160 -p 4161:4161 -d nsqio/nsq /nsqlookupd

docker run --name nsqd -p 4150:4150 -p 4151:4151 -v /data/var/lib/nsqd:/data -d nsqio/nsq /nsqd -broadcast-address=172.17.0.1 -lookupd-tcp-address=172.17.0.1:4160 -max-req-timeout=86400s -data-path=/data

docker run --name nsqadmin -p 4171:4171 -d nsqio/nsq /nsqadmin -lookupd-http-address=172.17.0.1:4161

请注意：如果不是本地请指定内网或者外网IP地址(nsqd和nsqadmin容器), max-req-timeout延迟消息最大值！！！

### 运行方法

/data/var/www/canal请根据你的实际路径进行调整。

step1:

docker run --rm -it -v /data/var/www/canal:/data longjianghu/swoft:4.5.2 sh

setp2:

composer install

step3:

cp .env.example .env 

vi .env // 请根据实际情况修改配置参数

step4:

退出窗口并执行：

docker run --name canal.client -v /data/var/www/canal:/data -d longjianghu/swoft:4.5.2

## License

Canal is an open-source software licensed under the [LICENSE](LICENSE)
