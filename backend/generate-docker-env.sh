#!/bin/bash

# Use envsubst to replace environment variables in the template and save it to .docker-env
envsubst < .docker-env.template > .docker-env
envsubst < .env.template > .env