#!/bin/bash
DIR=$(dirname $0)

ls $DIR | grep '-' |xargs -I {} php {}
