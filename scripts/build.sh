#!/usr/bin/env bash
set -euo pipefail

TAG="latest"

while getopts ":t:" opt; do
  case ${opt} in
    t ) TAG="$OPTARG" ;;
    * ) echo "Usage: $0 -t <tag>"; exit 1 ;;
  esac
done

docker build -t devops-project:${TAG} .
echo "Built image devops-project:${TAG}"
