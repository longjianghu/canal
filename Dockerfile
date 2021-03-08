FROM longjianghu/hyperf:2.1

MAINTAINER Longjianghu <215241062@qq.com>

RUN set -xe \
    && git clone https://github.com/longjianghu/canal.git /data \
    && cd /data && composer install \
    && cp .env.example .env
