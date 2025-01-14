#!/bin/bash
# wait-for-it.sh

host="$1"
# port="$2"
shift
cmd="$@"

until pg_isready -h "$host"; do
  >&2 echo "Postgres is unavailable - sleeping"
  sleep 1
done

>&2 echo "Postgres is up - executing command"
exec $cmd
