# Reverb Install

기술사항
- laravel 12 
- livewire
- reverb

1. 현재 Nginx Proxy Manger 안에 웹서버(192.168.0.22)를 사용중
2. https://my-chat.connple.com 을 접속하면 NPM에서 http 192.168.0.22:80 으로 포워딩하는데 포워딩 중 (websocket 켜져있음)
3. https://my-chat-ws.connple.com/ 접속하면 http 192.168.0.22:8080 으로 포워딩 중 (websocket 켜져있음)
4. forge     250305  0.0  4.1 213020 87040 ?        S    01:29   0:02 php8.3 artisan reverb:start --no-interaction --port=8080

REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=my-chat-ws.connple.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

오류 문제 
chrome devtools
app-UpLy0Mdf.js:8 WebSocket connection to 'wss://my-chat-ws.connple.com/app/my-app-key?protocol=7&client=js&version=8.4.0&flash=false' failed: 
WebSocket is closed before the connection is established.

오류를 확인할수 있는 방법 위주로 해결할수 있는 방법을 알려줘 

## 결론

서버의 포트가 안열려 있었다.
```bash
forge@images:~/my-chat.connple.com$ sudo netstat -tlnp | grep 8080
tcp        0      0 0.0.0.0:8080            0.0.0.0:*               LISTEN      250305/php8.3       
forge@images:~/my-chat.connple.com$ sudo ufw status
Status: active

To                         Action      From
--                         ------      ----
22                         ALLOW       Anywhere                  
80                         ALLOW       Anywhere                  
443                        ALLOW       Anywhere                  
22 (v6)                    ALLOW       Anywhere (v6)             
80 (v6)                    ALLOW       Anywhere (v6)             
443 (v6)                   ALLOW       Anywhere (v6)             

forge@images:~/my-chat.connple.com$ sudo ufw allow 8080
Rule added
Rule added (v6)
```
