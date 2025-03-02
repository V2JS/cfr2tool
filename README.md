# cloudflare R2操作工具集

# r2_uploader_gui.py 

### 项目源地址 [https://github.com/tysonair/Cloudfare-R2-FIle-Uploader](https://github.com/tysonair/Cloudfare-R2-FIle-Uploader)
 已打包 exe，方便使用

### 配置文件设置

1. 打开 `config.env` 文件
2. 填入以下配置信息：

````
R2_ACCESS_KEY_ID=你的Access_Key_ID
R2_ACCESS_KEY_SECRET=你的Access_Key_Secret
R2_BUCKET_NAME=你的存储桶名称
R2_ENDPOINT_URL=你的Endpoint_URL
R2_CUSTOM_DOMAIN=你的自定义域名(可选)
R2_PUBLIC_DOMAIN=你的R2.dev域名(可选)
````


# cfrimg.class.php

## 一个文件搞定 Cloudfare-R2-FIle 操作类

### 使用方法

````
$cfrimg = new cfrimg();

$cfrimg->upfile('本地文件路径', '存储路径');

$cfrimg->delfile('本地文件路径', '存储路径');

$cfrimg->getfile('本地文件路径', '存储路径');
````
