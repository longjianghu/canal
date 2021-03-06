# PHP Canal

基于 Hyperf 开发的Canal Client客户端(使用Docker方式部署，一个服务端只能搭配一个客户端)

## 项目说明

使用[Canal Server](https://github.com/alibaba/canal) 监听数据库变动,目前支持使用API接口、NSQ和RabbitMQ三种处理方式(不保证先后顺序)。

HTTP POST:当程序监听到变化后，使用HTTP POST的方式把数据提交到指定的地址。

> URL必须是一个有效的URL地址,使用data变量接收提交数据(POST提交)。

NSQ和RabbitMQ：把数据投递消息队列，等待客户端进行消费。

> 在runtime/logs目录下会有对应的日志文件可以进行查看。

## 数据格式：

当数据发生变化时rawData和newData会产生对应的数据，创建表等操作会直接输出SQL语句。

> Tips:只有SQL语句通常是对数据表的操作，rawData和newData同时产生表示更新,只有rawData表示删除，反之表示新建。

```
{"filename":"mysql-bin.000073","offset":554812760,"schemaName":"test","tableName":"users","eventType":8,"sql":"TRUNCATE `users`;","rawData":[],"newData":[]}
```

## 安装 Docker

```
curl -fsSL https://get.docker.com | bash -s docker --mirror Aliyun

usermod -aG docker  root

systemctl start docker
```

## Canal Server使用说明

详细介绍请移步：https://github.com/alibaba/canal/wiki/Docker-QuickStart

```
docker run --name canal.server \
			-p 11111:11111 \
			-e canal.destinations=canal-server \
			-e canal.instance.master.address=172.17.0.1:3306 \
			-e canal.instance.dbUsername=username \
			-e canal.instance.dbPassword=password \
			-d canal/canal-server:v1.1.4
```

## NSQ 消息队列

```
docker run --name nsqlookupd -p 4160:4160 -p 4161:4161 -d nsqio/nsq /nsqlookupd

docker run --name nsqd -p 4150:4150 -p 4151:4151 -v /data/var/lib/nsqd:/data -d nsqio/nsq /nsqd -broadcast-address=172.17.0.1 -lookupd-tcp-address=172.17.0.1:4160 -max-req-timeout=86400s -data-path=/data

docker run --name nsqadmin -p 4171:4171 -d nsqio/nsq /nsqadmin -lookupd-http-address=172.17.0.1:4161
```
> 请注意：如果不是本地请指定内网或者外网IP地址(nsqd和nsqadmin容器), max-req-timeout延迟消息最大值！！！

## RabbitMQ

```
docker run --name rabbitmq -p 5672:5672 -p 15672:15672 --restart=always -d rabbitmq:3.8.14-management-alpine
```

## 使用镜像

请根据你的实际路径进行调整

```
docker run --name canal.client -v /data/var/etc/canal.cnf:/data/.env -v /data/var/log/canal:/data/runtime/logs --restart=always -d longjianghu/canal:latest
```

> 请使用 .env.example 生成本地的配置文件。

## 自行部署

首先克隆项目到本地

```
git clone https://github.com/longjianghu/canal.git
```

step1:

> /data/var/www/canal请根据你的实际路径进行调整。

```
docker run --rm -it -v /data/var/www/canal:/data longjianghu/hyperf:2.1 sh
```

setp2:

```
composer install
```

step3:

```
cp .env.example .env 

vi .env // 请根据实际情况修改配置参数
```

step4:

退出窗口并执行

```
docker run --name canal.client -v /data/var/www/canal:/data --restart=always -d longjianghu/hyperf:2.1
```

> 因为不需要外部链接所以不用开放端口

## License

PHP Canal is an open-source software licensed under the [LICENSE](LICENSE)