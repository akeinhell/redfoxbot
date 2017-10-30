#!/usr/bin/env bash
docker build --pull --no-cache -t akeinhell/redfoxbot:latest . && \
docker push akeinhell/redfoxbot:latest
