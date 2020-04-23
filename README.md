
# [产品概述](#overview)
ManaBOS对象存储服务(Mana Bucket Object Storage)，简称ManaBOS，是ManaPHP社区为开发者提供的一种简单易扩展的存储解决方案。
开发者可以快速的开发出涉及存储业务的程序或服务。

ManaBOS的总体存储容量没有限制，单个`Bucket`的容量无限制，单个文件(`Object`)最大支持32M。

ManaBOS支持多种使用方法，主要有：
> 1. 轻松使用
    能过浏览器访问管理控制台，像网盘一样轻松上传和下载文件。
> 2. 快速开发
    通过ManaBOS提供的组件为您的应用快速添加数据上传和下载功能。
> 3. 灵活使用
    ManaBOS除提供了PHP语言使用的组件外，还提供了一套Restful风格的API接口，能够灵活的满各种开发需求。
>  4. 快速部署
    ManaBOS已经内置可以一键可运行的docker相关服务，方便部署。
    
# [产品部署](#deploy)
 ManaBOS所有涉及的相关信息存储在Mysql中，

# [操作使用](#usage)
## [创建存储空间](#create-bucket)
 安装部署好ManaBOS后，您就可能开始使用ManaBOS的管理控制台或命令行创建存储空间(Bucket)。ManaBOS中的每个文件(Object)都存储在存储空间(Bucket)中。
 必须先创建一个存储空间，然后才能在ManaBOS中存储数据。

### [存储空间限制](#bucket-overview)
* 可以创建的存储空间总数不限
* 存储空间的名称在ManaBOS服务内全局唯一。
* 存储空间一旦创建成功，名称不能修改。
* 单个存储空间的容量无限制。

### [操作步骤](#create-bucket-step)
操作命令是:
```bash
manacli bucket  create --name bucket-name --access-key key
```
我们创建一个名叫`test`，访问密钥是test的的存储空间：
```bash
manacli bucket create --name test --access-key test
```
## [查看存储空间](#list-bucket)
操作命令是:
```bash
manacli bucket list
```

## [导入目录](#import-objects)
操作命令是:
```bash
manacli object import --bucket bucket-name --dir directory --prefix prefix 
```

导入`@app`目录下的所有文件以`app/src`为`key`到`test`存储空间
```bash
manacli object import --bucket test --dir @app --prefix app/src
```

## [导出目录](#export-objects)
操作命令是:
```bash
manacli object export --bucket bucket-name  --prefix prefix --dir directory
```

导出`test存储空间下以app/src/为前缀的所有文件到@tmp/app目录
```bash
manacli object export --bucket test --prefix app/src/ --dir @tmp/app 
```

## [直接上传文件](#direct-upload)
您可以上传任何文件到存储空间中。需要注意的是：默认配置不能上传超过32M的文件。

```php
$policy = [];
$policy['bucket'] = $bucket;
$policy['key'] = "$prefix/$item";
$response = $this->bosClient->putObject($policy, $file);
```
## [Form表单上传文件](#form-upload)
```php
<?php
$token = $this->createUploadToken($params, 86400);

$bucket = $params['bucket'];

$endpoint = str_replace('{bucket}', $bucket, $this->_endpoint);

$curl_file = curl_file_create($file, mime_content_type($file), basename($file));

$body = $this->httpClient->post($endpoint . '/api/objects', ['token' => $token, 'file' => $curl_file])->getJsonBody();

if ($body['code'] !== 0) {
    throw new Exception($body['message'], $body['code']);
}

if (!isset($body['data']['token'])) {
    throw new MissingFieldException('token');
}

return $this->getUploadResult($body['data']['token']);
```