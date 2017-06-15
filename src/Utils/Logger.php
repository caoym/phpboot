<?php
namespace PhpBoot\Utils;

class Logger
{
    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function emergency($message, array $context = array())
    {
        self::getDefaultLogger()->{__FUNCTION__}($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function alert($message, array $context = array())
    {
        self::getDefaultLogger()->{__FUNCTION__}($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function critical($message, array $context = array())
    {
        self::getDefaultLogger()->{__FUNCTION__}($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function error($message, array $context = array())
    {
        self::getDefaultLogger()->{__FUNCTION__}($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function warning($message, array $context = array())
    {
        self::getDefaultLogger()->{__FUNCTION__}($message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function notice($message, array $context = array())
    {
        self::getDefaultLogger()->{__FUNCTION__}($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info($message, array $context = array())
    {
        self::getDefaultLogger()->{__FUNCTION__}($message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function debug($message, array $context = array())
    {
        self::getDefaultLogger()->{__FUNCTION__}($message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    static public function log($level, $message, array $context = array())
    {
        self::getDefaultLogger()->log($level, $message, $context);
    }

    /**
     * @param \Monolog\Logger $defaultLogger
     */
    static public function setDefaultLogger($defaultLogger)
    {
        self::$defaultLogger = $defaultLogger;
    }

    /**
     * @return \Monolog\Logger
     */
    static public function getDefaultLogger()
    {
        if(!self::$defaultLogger){
            self::$defaultLogger = new \Monolog\Logger('default');
        }
        return self::$defaultLogger;
    }

    /**
     * @var \Monolog\Logger
     */
    static private $defaultLogger;
}