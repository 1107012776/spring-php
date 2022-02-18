#!/bin/bash
curl http://127.0.0.1:7999/Index/index
echo -e "\n"
curl http://127.0.0.1:8098/Index/index
echo -e "\n"
curl http://127.0.0.1:8297/Index/index
echo -e "\n"

curl http://127.0.0.1:8297/Index/getContent
echo -e "\n"

curl http://127.0.0.1:8297/Index/rawContent
echo -e "\n"

curl http://127.0.0.1:8297/test
echo -e "\n"

curl http://127.0.0.1:7999/Index/index11
echo -e "\n"

curl http://127.0.0.1:8297/Index/index11
echo -e "\n"

