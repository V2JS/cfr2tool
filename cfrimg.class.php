<?php
class cfrimg
{
    private $accessKey;
    private $secretKey;
    private $bucket;
    private $region;
    private $r2AccountId;
    private $r2Dm;

    public function __construct()
    {
        $this->accessKey = '3680db2ce38bf70658fg620d51245eec';
        $this->secretKey = '91b8f4bafb17967c7b16fd7c8984of87r4hyb765abfe5486bd262b759ac56c26';
        $this->bucket = 'imgjob';
        $this->region = 'auto';
        $this->r2AccountId = '89gert8cb65003e141850f956bfc63ed';
        $this->r2Dm = 'https://img.dm.com';
    }

    private function createSignature($key, $stringToSign)
    {
        return hash_hmac('sha256', $stringToSign, $key, true);
    }

    private function getSignatureKey($key, $date, $region, $service)
    {
        $kDate = $this->createSignature("AWS4" . $key, $date);
        $kRegion = $this->createSignature($kDate, $region);
        $kService = $this->createSignature($kRegion, $service);
        $kSigning = $this->createSignature($kService, "aws4_request");
        return $kSigning;
    }

    private function getAuthorizationHeader($method, $uri, $payloadHash)
    {
        $service = 's3';
        $host = "{$this->bucket}.{$this->r2AccountId}.r2.cloudflarestorage.com";
        $currentDate = gmdate('Ymd');
        $currentTimestamp = gmdate('Ymd\THis\Z');
        $credentialScope = "$currentDate/{$this->region}/$service/aws4_request";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';

        $canonicalRequest = "$method\n$uri\n\nhost:$host\nx-amz-content-sha256:$payloadHash\nx-amz-date:$currentTimestamp\n\n$signedHeaders\n$payloadHash";
        $stringToSign = "AWS4-HMAC-SHA256\n$currentTimestamp\n$credentialScope\n" . hash('sha256', $canonicalRequest);

        $signingKey = $this->getSignatureKey($this->secretKey, $currentDate, $this->region, $service);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        return [
            "Authorization: AWS4-HMAC-SHA256 Credential={$this->accessKey}/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature",
            "x-amz-content-sha256: $payloadHash",
            "x-amz-date: $currentTimestamp"
        ];
    }

    private function comCurl($filePath, $dir,$lx)
    {
        $uri = "$dir/" . basename($filePath);
        $endpoint = "https://{$this->bucket}.{$this->r2AccountId}.r2.cloudflarestorage.com$uri";
        $payloadHash = ($lx=='PUT') ? hash_file('sha256', $filePath) : hash('sha256', '');

        $headers = $this->getAuthorizationHeader($lx, $uri, $payloadHash);

        $ch = curl_init($endpoint);
        if($lx=='PUT') {
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_INFILE, fopen($filePath, 'r'));
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filePath));
        }
        if($lx=='DELETE'){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpCode == 200) ? 'success' : ('error'. $response);
    }

    public function upfile($filePath, $dir = 'reginfo')
    {
        $re = $this->comCurl($filePath, $dir, 'PUT');
        if ($re =='success') {
            @unlink($filePath); //删除本地文件，请根据实际情况修改
            return $this->r2Dm . "$dir/" . basename($filePath);
        } else {
            return $re;
        }
    }

    public function getfile($filePath, $dir = 'reginfo')
    {
        return $this->comCurl($filePath, $dir, 'GET');
    }

    public function delfile($filePath, $dir = 'reginfo')
    {
        return $this->comCurl($filePath, $dir, 'DELETE');
    }
}