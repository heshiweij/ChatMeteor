

# Chat Meteor

## 简介

Chat Meteor 是一款基于 swoole + Async Redis 打造的 WebSocket 聊天引擎，支持单聊、群聊，创建群组、管理群组等。通过 HTTP 和 WS 对外提供服务。

## 特性

- 简单易懂，没有借助第三方框架
- 不需要掌握 swoole 即可构建聊天应用
- 通过 shell 识别 cpu cores，智能开启工作进程
- 协程异步访问各客户端，实现高性能

## TodoList

- Contract
- Code Optimize
- Redis Replication
- 持久化聊天记录到 DB
- 离线聊天记录
- 日志切割
- 故障平滑重启脚本
- 进程健康检查
- Nginx 负载均衡
- Redis 使用率报警
- Web Demo

## 安装

### 环境
	- php7.0+
	- swoole2.1+
	- redis 3.2+
## 配置

	debug: true
    redis:
      host: '127.0.0.1'
      password: ''
      port: 6379
      database: 0
    mysql:
      host: '127.0.0.1'
      port: 3306
      user: 'root'
      password: '123456'
      database: 'chat'

### 部署

	- 克隆代码
	- 执行 bash ./startup.sh <Your IP> <Your Port>

### 日志
	
	- ./storage/logs/chat<date>.log


## 服务接口

### HTTP 协议接口：

#### 创建群组

Request: 
```json
{
	"type": "http",
	"class": "group",
	"method": "create",
	"args": {
		"group_name": "dns交流群",
		"users": [
			1,2,3,4,5,6  // 初始成员列表
		]

	}
}
```

Response:
```json
{
	"code": 200,
	"message": "创建成功",
	"data": {
		"group_name":   "圣诞交流群",
		"group_id": "abcdef" // group_id = substr( md5(group_name), 0, 6 )
	}
}
```

#### 加入群组

Request: 
```json
{
	"type": "http",
	"class": "group",
	"method": "append",
	"args": {
		"users":  [ 11,12,13,14,15],
		"group_id":  "abdedf"
	}
}

```

Response:
```json
{
    "code": 200,
    "message": "加入群组成功",
    "data": [
        10,
        11,
        12
    ]
}
```

#### 获取群组列表

Request:

```json
{
	"type": "http",
	"class": "group",
	"method": "list",
	"args": {
		"is_active": true // true: 只显示成员数 > 2 (活跃)的群组
	}
}
```

Response:
```json
{
	"code": 200,
	"message": "获取群组列表成功",
	"data": [
		{
			"group_id": "fdfsfds",
			"group_name": "dns 交流群"
		},
		   {
			"group_id": "adfrwd",
			"group_name": "日常点赞群"
		}
	]
}
```

#### 获取群组内用户列表

Request:

```json
{
	"type": "http",
	"class": "group",
	"method": "users",
	"args": {
		"group_id": "abcdef"
	}
}
```

Response:

```json
 {
	"code": 200,
	"message": "获取群组列表成功",
	"data": [1,2,3,4]
}
```



### WS 接口协议

#### 绑定用户
 
```json
 {
	"type": "ws",
	"class": "setting",
	"method": "bind",
	"args": {
		"user_id": 1
	}
}
```

#### 单聊

```json
 {
	"type": "ws",
	"class": "single",
	"method": "send",
	"args": {
		"to_user": 10,
		"message": "hello, I am 1?"
	}
}
```

#### 群聊

```json
{
	"type": "ws",
	"class": "group",
	"method": "send",
	"args": {
		"group_id": "b3bf60",
		"message": "hello, this is group message! I am 1"
	}
}
```

## 服务通知


### 收到单聊消息

```json
 {
	"type": "ws",
	"category": "single",
	"args": {
		"from_user": 1,
		"message": "hello, 你好，在吗？"
	}
}
```

### 收到群聊消息

```json
 {
	"type": "ws",
	"categroy": "group",
	"args": {
		"from_group": "barewd", // group_id
		"from_user": 10,
		"message": "hello，大家好！"
	}
}
```


