<?php

namespace App\Services;

use ManaPHP\Service;

class ObjectLimitCheckerService extends Service
{
    /**
     * @param string|int|float $value
     * @param string|int|float $limit
     *
     * @return bool
     */
    public function checkNum($value, $limit)
    {
        if (strpos($limit, '~') !== false) {
            $parts = explode('~', $limit);
            return $value >= $parts[0] && $value <= $parts[1];
        } elseif (strpos($limit, '-') !== false) {
            $parts = explode('-', $limit);
            return $value >= $parts[0] && $value <= $parts[1];
        } elseif (is_numeric($limit)) {
            return $limit == $value;
        } elseif (preg_match('#^([=<>!]+)(.*)$#', $limit, $match) === 1) {
            $op = $match[1];
            $op_value = $match[2];
            if ($op === '=') {
                return $value == $op_value;
            } elseif ($op === '>') {
                return $value > $op_value;
            } elseif ($op === '>=') {
                return $value >= $op_value;
            } elseif ($op === '<') {
                return $value < $op_value;
            } elseif ($op === '<=') {
                return $value <= $op_value;
            } elseif ($op === '!=' || $op === '<>') {
                return $value != $op_value;
            } else {
                return false;
            }
        } else {
            $this->logger->info(['`:limit` limit format is invalid', 'limit' => $limit]);
            return false;
        }
    }

    /**
     * @param Object $object
     * @param array  $limits
     *
     * @return string|true
     */
    public function checkLimit($object, $limits)
    {
        foreach ($limits as $name => $limit) {
            if ($name === 'size') {
                if (!$this->checkNum($object->size, $limit)) {
                    return 'size';
                }
            } elseif ($name === 'mime_type') {
                if (!in_array($object->mime_type, explode(',', $limit), true)) {
                    return 'mime_type';
                }
            } elseif ($name === 'width') {
                if (!$this->checkNum($object->width, $limit)) {
                    return 'width';
                }
            } elseif ($name === 'height') {
                if (!$this->checkNum($object->height, $limit)) {
                    return 'height';
                }
            } elseif ($name === 'wh_radio') {
                if (!$object->width || !$object->height || !$this->checkNum($object->width / $object->height, $limit)) {
                    return 'wh_radio';
                }
            } elseif ($name === 'hw_radio') {
                if (!$object->width || !$object->height || !$this->checkNum($object->height / $object->width, $limit)) {
                    return 'hw_radio';
                }
            } elseif ($name === 'radio') {
                if (!$object->width || !$object->height || !$this->checkNum($object->width / $object->height, $limit)) {
                    return 'radio';
                }
            } elseif ($name === 'extension') {
                if (!in_array($object->extension, explode(',', $limit), true)) {
                    return 'extension';
                }
            } else {
                return $name;
            }
        }

        return true;
    }
}