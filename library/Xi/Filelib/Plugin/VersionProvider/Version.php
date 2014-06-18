<?php

namespace Xi\Filelib\Plugin\VersionProvider;

class Version
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var
     */
    private $params;

    public function __construct($version, array $params = array())
    {
        $this->version = $version;
        $this->params = $params;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getParams()
    {
        return $this->params;
    }


    /**
     * @param $rawVersion
     * @return Version
     */
    public static function get($version)
    {
        if ($version instanceof Version) {
            return $version;
        }

        return self::parse($version);
    }

    private static function parse($rawVersion)
    {
        $split = explode(':', $rawVersion);

        if (!isset($split[1])) {
            return new Version($split[0]);
        }

        $params = array();
        $splitParams = explode(';', $split[1]);
        foreach ($splitParams as $splitParam) {
            list ($key, $value) = explode('=', $splitParam);
            $params[$key] = $value;
        }

        return new Version($split[0], $params);
    }


    public function toString()
    {
        if (!$this->params) {
            return $this->version;
        }

        $paramsStr = array();
        foreach ($this->params as $key => $value) {
            $paramsStr[] = $key . '=' . $value;
        }

        return $this->version . ':' . implode(';', $paramsStr);
    }

    public function __toString()
    {
        return $this->toString();
    }


}
