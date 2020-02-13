<?php
namespace Antavo\LoyaltyApps\Helper;

use Zend\Log\Logger as ZendLogger;
use Zend\Log\Writer\Stream;

/**
 *
 */
class Logger extends ZendLogger
{
    /**
     * @var string
     * @static
     */
    protected static $_fileName = 'antavo.log';

    /**
     * @return string
     * @static
     */
    public static function getFileName()
    {
        return static::$_fileName;
    }

    /**
     * @param string $fileName
     * @static
     */
    public static function setFileName($fileName)
    {
        static::$_fileName = $fileName;
    }

    /**
     * @return string
     * @static
     */
    public static function getFilePath()
    {
        return sprintf('%s/var/log/%s', BP, static::getFileName());
    }

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        // Adding Zend stream writer object.
        $this->addWriter(new Stream(static::getFilePath()));
    }

    /**
     * Returns last *limit* number of log entries.
     *
     * @param int $limit
     * @return string[]
     * @static
     */
    public static function getErrors($limit = 20)
    {
        $errors = [];
        $size = 0;

        foreach (self::getFileIterator() as $line) {
            if ($line) {
                $errors[] = $line;

                if ($size < $limit) {
                    $size++;
                } else {
                    array_shift($errors);
                }
            }
        }

        return array_reverse($errors);
    }

    /**
     * Returns a file iterator instance which would iterate over the log file.
     *
     * @return \Antavo\LoyaltyApps\Helper\FileIterator
     * @static
     */
    public static function getFileIterator()
    {
        return new FileIterator(Logger::getFilePath());
    }

    /**
     * Parses log line.
     *
     * @param string $entry
     * @return array  Parsed log entry containing the following array keys:
     * - *request*: request header & body as sent;
     * - *response*: received response header & body;
     * - *message*: holds log line content in case of any parsing error.
     * @static
     */
    public static function parseLogEntry($entry)
    {
        $pattern = '/^
            (?<date>[^\s]*)\s
            (?<level>\w+)\s
            \(\d\):\s
            (?<content>.+)
        $/x';

        if (preg_match($pattern, $entry, $matches)) {
            $matches['request'] = '';
            $matches['response'] = '';

            if (($data = json_decode($matches['content'])) !== FALSE) {
                if (isset($data->request)) {
                    $request = $data->request;

                    if (isset($request->method, $request->url)) {
                        $matches['request'] .= $request->method . ' ' . $request->url . PHP_EOL;
                    }

                    if (isset($request->header) && $request->header) {
                        $matches['request'] .= $request->header . PHP_EOL . PHP_EOL;
                    }

                    if ($matches['request']) {
                        $matches['request'] .= PHP_EOL;
                    }

                    if (isset($request->body)) {
                        $matches['request'] .= $request->body;
                    }
                }

                if (isset($data->response)) {
                    $response = $data->response;

                    if (isset($response->header)) {
                        $matches['response'] .= $response->header . PHP_EOL . PHP_EOL;
                    }

                    if (isset($response->body)) {
                        $matches['response'] .= $response->body;
                    }

                    if ($matches['request'] || $matches['response']) {
                        $matches['message'] = '';
                    }
                }
            }

            return $matches;
        }

        return [];
    }
}
