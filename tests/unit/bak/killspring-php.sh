#!/bin/bash
ps -ef|grep spring-php|grep -v grep|cut -c 9-15|xargs kill -9