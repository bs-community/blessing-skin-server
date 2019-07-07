#!/bin/bash
set -e
command -v docker-compose >/dev/null 2>&1 || { echo >&2 "请先安装 docker-compose。 Please install docker-compose first."; exit 1; }
cp .env.laradock laradock/.env
if [ -f ".env" ]
then
  mv .env .env.bak
fi
cp .env.laradock.example .env
docker-compose up -d nginx mariadb
