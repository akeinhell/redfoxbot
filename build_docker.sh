#!/usr/bin/env bash
docker rmi akeinhell/redfoxbot:latest
docker build --pull -f docker/Dockerfile -t akeinhell/redfoxbot:latest . && \
docker push akeinhell/redfoxbot:latest