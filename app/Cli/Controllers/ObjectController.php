<?php
namespace App\Cli\Controllers;

use ManaPHP\Cli\Controller;

/**
 * Class ObjectController
 * @package App\Cli\Controllers
 */
class ObjectController extends Controller
{
    /**
     * list all objects of one bucket
     *
     * @param string $bucket    the bucket name of objects
     * @param string $key       the key of object
     * @param string $prefix    the prefix of keys
     * @param string $mime_type the mime-type of object
     * @param string $extension the extension of object
     */
    public function listCommand($bucket, $key = '', $prefix = '', $mime_type = '', $extension = '')
    {
        $data = [];

        $data['bucket'] = $bucket;
        $data['key'] = $key;
        $data['prefix'] = $prefix;
        $data['mime_type'] = $mime_type;
        $data['extension'] = $extension;

        $data = array_trim($data);

        $response = $this->bosClient->listObjects($data);
        $this->console->write($response);
    }

    /**
     * @param string $bucket
     * @param string $dir
     * @param string $prefix
     */
    public function importCommand($bucket, $dir, $prefix)
    {
        if (!$this->filesystem->dirExists($dir)) {
            return $this->console->error(['`:dir` directory is not exists', 'dir' => $dir]);
        }

        $this->_recursiveImport($bucket, $dir, $prefix);
    }

    /**
     * @param string $bucket
     * @param string $dir
     * @param string $prefix
     */
    protected function _recursiveImport($bucket, $dir, $prefix)
    {
        $prefix = rtrim($prefix, '/');

        foreach ($this->filesystem->scandir($dir) as $item) {
            $file = "$dir/$item";
            if ($this->filesystem->fileExists($file)) {
                $policy = [];
                $policy['bucket'] = $bucket;
                $policy['key'] = "$prefix/$item";
                $response = $this->bosClient->putObject($policy, $file);

                $this->console->writeLn($response);
            } else {
                $this->_recursiveImport($bucket, $file, "$prefix/$item");
            }
        }
    }

    /**
     * @param string $bucket
     * @param string $dir
     * @param string $prefix
     * @param string $key
     */
    public function exportCommand($bucket, $dir = '', $prefix = '', $key = '')
    {
        $data = [];
        $data['bucket'] = $bucket;
        $data['prefix'] = $prefix;
        $data['key'] = $key;

        if (!$dir) {
            $dir = "@tmp/bos_export/$bucket";
        }

        $dir = rtrim($dir, '/');

        $files = [];
        foreach ($this->bosClient->listObjects($data) as $object) {
            $files[$object['url']] = $dir . '/' . $object['key'];
        }

        $this->httpClient->download($files);

        $this->console->writeLn(['download files to `:dir` directory', 'dir' => $dir]);
    }
}