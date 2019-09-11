#!/bin/bash

composer install --no-dev
export NODE_ENV=production
npm install
gulp