<?php

namespace Xi\Filelib;

class Version
{
    const PATTERN_NAME = '#^[a-z]([a-z0-9-_])*$#';

    const PATTERN_VALUE = '#^([a-z0-9-_])+$#';

    /**
     * @var string
     */
    private static $separator = '::';

    /**
     * @var string
     */
    private static $paramSeparator = ';';

    /**
     * @var string
     */
    private static $paramKeyValueSeparator = ':';

    /**
     * @var string
     */
    private $version;

    /**
     * @var array
     */
    private $params = array();

    /**
     * @param string $version
     * @param array $params
     */
    public function __construct($version, array $params = array())
    {
        $this->setVersion($version);
        $this->setParams($params);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param $rawVersion Version|string
     * @return Version
     */
    public static function get($version)
    {
        if ($version instanceof Version) {
            return $version;
        }

        return self::parse($version);
    }

    /**
     * @param string $rawVersion
     * @return Version
     * @throws InvalidVersionException
     */
    private static function parse($rawVersion)
    {
        $split = explode(self::$separator, $rawVersion);

        if (!isset($split[1])) {
            return new Version($split[0]);
        }

        $params = array();
        $splitParams = explode(self::$paramSeparator, $split[1]);
        foreach ($splitParams as $splitParam) {

            $splitKeyValue = explode(self::$paramKeyValueSeparator, $splitParam);
            if (!isset($splitKeyValue[1])) {
                throw new InvalidVersionException(
                    sprintf(
                        "Could not parse parameters from version string '%s'",
                        $rawVersion
                    )
                );
            }
            list ($key, $value) = $splitKeyValue;
            $params[$key] = $value;
        }
        return new Version($split[0], $params);
    }

    /**
     * @return string
     */
    public function toString()
    {
        if (!$this->params) {
            return $this->version;
        }

        ksort($this->params);

        $paramsStr = array();
        foreach ($this->params as $key => $value) {
            $paramsStr[] = $key . self::$paramKeyValueSeparator . $value;
        }

        return $this->version . self::$separator . implode(self::$paramSeparator, $paramsStr);
    }

    /**
     * @param string $version
     * @throws InvalidVersionException
     */
    private function setVersion($version)
    {
        if (!preg_match(self::PATTERN_NAME, $version)) {
            throw new InvalidVersionException(
                sprintf(
                    "Version identifier '%s' does not match the pattern '%s'",
                    $version,
                    self::PATTERN_NAME
                )
            );
        }
        $this->version = $version;
    }

    /**
     * @param array $params
     */
    private function setParams(array $params)
    {
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @throws InvalidVersionException
     */
    private function setParam($key, $value)
    {
        if (!preg_match(self::PATTERN_NAME, $key)) {
            throw new InvalidVersionException(
                sprintf(
                    "Parameter key '%s' does not match the pattern '%s'",
                    $key,
                    self::PATTERN_NAME
                )
            );
        }

        if (!preg_match(self::PATTERN_VALUE, $value)) {
            throw new InvalidVersionException(
                sprintf(
                    "Parameter value '%s' does not match the pattern '%s'",
                    $value,
                    self::PATTERN_VALUE
                )
            );
        }

        $this->params[$key] = $value;
    }
}
