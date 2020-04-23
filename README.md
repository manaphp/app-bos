
# [��Ʒ����](#overview)
ManaBOS����洢����(Mana Bucket Object Storage)�����ManaBOS����ManaPHP����Ϊ�������ṩ��һ�ּ�����չ�Ĵ洢���������
�����߿��Կ��ٵĿ������漰�洢ҵ��ĳ�������

ManaBOS������洢����û�����ƣ�����`Bucket`�����������ƣ������ļ�(`Object`)���֧��32M��

ManaBOS֧�ֶ���ʹ�÷�������Ҫ�У�
> 1. ����ʹ��
    �ܹ���������ʹ������̨��������һ�������ϴ��������ļ���
> 2. ���ٿ���
    ͨ��ManaBOS�ṩ�����Ϊ����Ӧ�ÿ�����������ϴ������ع��ܡ�
> 3. ���ʹ��
    ManaBOS���ṩ��PHP����ʹ�õ�����⣬���ṩ��һ��Restful����API�ӿڣ��ܹ����������ֿ�������
>  4. ���ٲ���
    ManaBOS�Ѿ����ÿ���һ�������е�docker��ط��񣬷��㲿��
    
# [��Ʒ����](#deploy)
 ManaBOS�����漰�������Ϣ�洢��Mysql�У�

# [����ʹ��](#usage)
## [�����洢�ռ�](#create-bucket)
 ��װ�����ManaBOS�����Ϳ��ܿ�ʼʹ��ManaBOS�Ĺ������̨�������д����洢�ռ�(Bucket)��ManaBOS�е�ÿ���ļ�(Object)���洢�ڴ洢�ռ�(Bucket)�С�
 �����ȴ���һ���洢�ռ䣬Ȼ�������ManaBOS�д洢���ݡ�

### [�洢�ռ�����](#bucket-overview)
* ���Դ����Ĵ洢�ռ���������
* �洢�ռ��������ManaBOS������ȫ��Ψһ��
* �洢�ռ�һ�������ɹ������Ʋ����޸ġ�
* �����洢�ռ�����������ơ�

### [��������](#create-bucket-step)
����������:
```bash
manacli bucket  create --name bucket-name --access-key key
```
���Ǵ���һ������`test`��������Կ��test�ĵĴ洢�ռ䣺
```bash
manacli bucket create --name test --access-key test
```
## [�鿴�洢�ռ�](#list-bucket)
����������:
```bash
manacli bucket list
```

## [����Ŀ¼](#import-objects)
����������:
```bash
manacli object import --bucket bucket-name --dir directory --prefix prefix 
```

����`@app`Ŀ¼�µ������ļ���`app/src`Ϊ`key`��`test`�洢�ռ�
```bash
manacli object import --bucket test --dir @app --prefix app/src
```

## [����Ŀ¼](#export-objects)
����������:
```bash
manacli object export --bucket bucket-name  --prefix prefix --dir directory
```

����`test�洢�ռ�����app/src/Ϊǰ׺�������ļ���@tmp/appĿ¼
```bash
manacli object export --bucket test --prefix app/src/ --dir @tmp/app 
```

## [ֱ���ϴ��ļ�](#direct-upload)
�������ϴ��κ��ļ����洢�ռ��С���Ҫע����ǣ�Ĭ�����ò����ϴ�����32M���ļ���

```php
$policy = [];
$policy['bucket'] = $bucket;
$policy['key'] = "$prefix/$item";
$response = $this->bosClient->putObject($policy, $file);
```
## [Form���ϴ��ļ�](#form-upload)
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