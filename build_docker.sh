#!/usr/bin/env bash
if [[ "$(docker images -q akeinhell/redfoxbot:latest 2> /dev/null)" -ne "" ]]; then
  docker rmi $(docker images -q akeinhell/redfoxbot:latest)
fi
docker build --pull -t akeinhell/redfoxbot:latest . && \
docker push akeinhell/redfoxbot:latest
