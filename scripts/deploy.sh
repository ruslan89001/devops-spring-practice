#!/usr/bin/env bash
set -euo pipefail

TAG="latest"

while getopts ":t:" opt; do
  case ${opt} in
    t ) TAG="$OPTARG" ;;
    * ) echo "Usage: $0 -t <tag>"; exit 1 ;;
  esac
done

docker plugin inspect loki >/dev/null 2>&1 || \
docker plugin install grafana/loki-docker-driver:latest --alias loki --grant-all-permissions

TAG="${TAG}" docker compose up -d
echo "Compose stack started with tag ${TAG}"
