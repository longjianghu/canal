FROM  longjianghu/swoft:4.5.2

MAINTAINER Longjianghu <215241062@qq.com>

RUN set -xe \
    && git clone https://github.com/longjianghu/swoft.canal.git /data \
    && git checkout develop \
    && cd /data && composer install
