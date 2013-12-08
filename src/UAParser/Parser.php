<?php
/**
 * ua-parser
 *
 * Copyright (c) 2011-2013 Dave Olsen, http://dmolsen.com
 * Copyright (c) 2013-2014 Lars Strojny, http://usrportage.de
 *
 * Released under the MIT license
 */
namespace UAParser;

use UAParser\Exception\FileNotFoundException;
use UAParser\Result\Client;

class Parser extends AbstractParser
{
    /** @var DeviceParser */
    private $deviceParser;

    /** @var OperatingSystemParser */
    private $operatingSystemParser;

    /** @var UserAgentParser */
    private $userAgentParser;

    /**
     * Start up the parser by importing the json file to $this->regexes
     *
     * @param string $customRegexesFile
     * @throws FileNotFoundException
     */
    public function __construct($customRegexesFile = null)
    {
        $regexesFile = $customRegexesFile !== null ? $customRegexesFile : static::getDefaultFile();
        if (file_exists($regexesFile)) {
            $this->regexes = include $regexesFile;
        } elseif ($customRegexesFile !== null) {
            throw FileNotFoundException::customRegexFileNotFound($regexesFile);
        } else {
            throw FileNotFoundException::defaultFileNotFound(static::getDefaultFile());
        }

        $this->deviceParser = new DeviceParser($this->regexes);
        $this->operatingSystemParser = new OperatingSystemParser($this->regexes);
        $this->userAgentParser = new UserAgentParser($this->regexes);
    }

    /**
     * Sets up some standard variables as well as starts the user agent parsing process
     *
     * @param string $userAgent a user agent string to test, defaults to an empty string
     * @param array $jsParseBits
     * @return Client
     */
    public function parse($userAgent, array $jsParseBits = array())
    {
        $client = new Client($userAgent);

        $client->ua = $this->userAgentParser->parseUserAgent($userAgent, $jsParseBits);
        $client->os = $this->operatingSystemParser->parseOperatingSystem($userAgent);
        $client->device = $this->deviceParser->parseDevice($userAgent);

        return $client;
    }
}
