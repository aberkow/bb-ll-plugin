#!/bin/bash

echo "begin composer install"
composer install --no-dev
echo "end composer install"

echo "begin npm install"
npm install
echo "end npm install"

# set the env after installing 
# otherwise, npm only installs deps and not devDeps
echo "set node env"
export NODE_ENV=production
echo "end node env $NODE_ENV"

echo "begin gulp"
gulp
echo "end gulp"