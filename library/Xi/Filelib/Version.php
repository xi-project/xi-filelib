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
    private static $modifierSeparator = '@';

    /**
     * @var string
     */
    private $version;

    /**
     * @var array
     */
    private $params = array();

    /**
     * @var array
     */
    private $modifiers = array();

    /**
     * @param string $version
     * @param array $params
     */
    public function __construct($version, array $params = array(), $modifiers = array())
    {
        $this->doSetVersion($version);

        foreach ($params as $key => $value) {
            $this->doSetParam($key, $value);
        }

        foreach ($modifiers as $modifier) {
            $this->doAddModifier($modifier);
        }
    }

    /**
     * @param string $modifier
     * @return bool
     */
    public function hasModifier($modifier)
    {
        return in_array($modifier, $this->modifiers);
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
     * @return array
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * @param string $version
     * @return Version
     */
    public function setVersion($version)
    {
        $self = clone $this;
        $self->doSetVersion($version);
        return $self;
    }

    /**
     * @param string $key
     * @param string $value
     * @return Version
     */
    public function setParam($key, $value)
    {
        $self = clone $this;
        $self->doSetParam($key, $value);
        return $self;
    }

    /**
     * @param string $key
     * @return Version
     */
    public function removeParam($key)
    {
        $self = clone $this;
        $self->doRemoveParam($key);
        return $self;
    }

    /**
     * @param string $modifier
     * @return Version
     */
    public function addModifier($modifier)
    {
        $self = clone $this;
        $self->doAddModifier($modifier);
        return $self;
    }

    /**
     * @param string $modifier
     * @return Version
     */
    public function removeModifier($modifier)
    {
        $self = clone $this;
        $self->doRemoveModifier($modifier);
        return $self;
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
        $modifierSplit = explode(self::$modifierSeparator, $rawVersion);

        $modifiers = (isset($modifierSplit[1])) ? explode(self::$paramSeparator, $modifierSplit[1]) : array();

        $split = explode(self::$separator, $modifierSplit[0]);

        if (!isset($split[1])) {
            return new Version($split[0], array(), $modifiers);
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
        return new Version($split[0], $params, $modifiers);
    }

    /**
     * @return string
     */
    public function toString()
    {
        if (!$this->params && !$this->modifiers) {
            return $this->version;
        }

        ksort($this->params);
        sort($this->modifiers);

        $paramsStr = array();
        foreach ($this->params as $key => $value) {
            $paramsStr[] = $key . self::$paramKeyValueSeparator . $value;
        }

        $ret = $this->version;

        if ($paramsStr) {
            $ret .= self::$separator . implode(self::$paramSeparator, $paramsStr);
        }

        if ($modifiers = $this->getModifiers()) {
            $ret .= self::$modifierSeparator . implode(self::$paramSeparator, $modifiers);
        };

        return $ret;
    }

    /**
     * @param string $version
     * @return Version
     * @throws InvalidVersionException
     */
    private function doSetVersion($version)
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

        return $this;
    }


    /**
     * @param string $key
     * @param string $value
     * @throws InvalidVersionException
     */
    private function doSetParam($key, $value)
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

    /**
     * @param string $key
     */
    private function doRemoveParam($key)
    {
        unset($this->params[$key]);
    }

    /**
     * @param string $modifier
     */
    private function doRemoveModifier($modifier)
    {
        $key = array_search($modifier, $this->modifiers, true);
        if ($key !== false) {
            unset($this->modifiers[$key]);
        }
        $this->modifiers = array_values($this->modifiers);
    }

    /**
     * @param string $modifier
     * @throws InvalidVersionException
     */
    private function doAddModifier($modifier)
    {
        if (!preg_match(self::PATTERN_VALUE, $modifier)) {
            throw new InvalidVersionException(
                sprintf(
                    "Parameter value '%s' does not match the pattern '%s'",
                    $modifier,
                    self::PATTERN_VALUE
                )
            );
        }

        if (!in_array($modifier, $this->modifiers)) {
            $this->modifiers[] = $modifier;
        }
    }
}
