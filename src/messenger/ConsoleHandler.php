<?php

namespace Davislar\AMQP\messenger;


use Davislar\AMQP\interfaces\MessengerInterface;

class ConsoleHandler implements MessengerInterface
{
    // foreground color control codes
    const FG_BLACK = 30;
    const FG_RED = 31;
    const FG_GREEN = 32;
    const FG_YELLOW = 33;
    const FG_BLUE = 34;
    const FG_PURPLE = 35;
    const FG_CYAN = 36;
    const FG_GREY = 37;
    // background color control codes
    const BG_BLACK = 40;
    const BG_RED = 41;
    const BG_GREEN = 42;
    const BG_YELLOW = 43;
    const BG_BLUE = 44;
    const BG_PURPLE = 45;
    const BG_CYAN = 46;
    const BG_GREY = 47;
    // fonts style control codes
    const RESET = 0;
    const NORMAL = 0;
    const BOLD = 1;
    const ITALIC = 3;
    const UNDERLINE = 4;
    const BLINK = 5;
    const NEGATIVE = 7;
    const CONCEALED = 8;
    const CROSSED_OUT = 9;
    const FRAMED = 51;
    const ENCIRCLED = 52;
    const OVERLINED = 53;

    /**
     * @var array
     */
    protected $levels;

    /**
     * ConsoleHandler constructor.
     * ['levels' => ['error']]
     * @param $config
     */
    public function __construct($config)
    {
        $this->levels = $config['levels'];
    }

    /**
     * @param $level
     * @return int
     */
    protected function getColorByLevel($level)
    {
        switch ($level) {
            case MassageHandler::VERBOSE_LOG:
                {
                    $color = self::FG_GREEN;
                    break;
                }
            case MassageHandler::VERBOSE_WARNING:
                {
                    $color = self::BG_RED;
                    break;
                }
            case MassageHandler::VERBOSE_ERROR:
                {
                    $color = self::BG_RED;
                    break;
                }
            case MassageHandler::VERBOSE_NOTICE:
                {
                    $color = self::BG_GREY;
                    break;
                }
            case MassageHandler::VERBOSE_DEBUG:
                {
                    $color = self::BG_YELLOW;
                    break;
                }
            default:
                {
                    $color = self::BG_GREY;
                }
        }
        return $color;
    }

    /**
     * @param $level
     * @return bool
     */
    public function verbose($level)
    {
        return in_array($level, $this->levels);
    }

    /**
     * @param $msg
     * @param $code
     * @param $level
     */
    public function send($msg, $code, $level)
    {
        $this->consolePrint($code, $msg, $this->getColorByLevel($level));
    }

    /**
     * Prints a string to STDOUT.
     *
     * @param string $string the string to print
     * @return int|bool Number of bytes printed or false on error
     */
    protected function stdout($string)
    {
        return fwrite(\STDOUT, $string);
    }

    /**
     * Will return a string formatted with the given ANSI style.
     *
     * @param string $string the string to be formatted
     * @param array $format An array containing formatting values.
     * You can pass any of the `FG_*`, `BG_*` and `TEXT_*` constants
     * and also [[xtermFgColor]] and [[xtermBgColor]] to specify a format.
     * @return string
     */
    protected function ansiFormat($string, $format = [])
    {
        $code = implode(';', $format);

        return "\033[0m" . ($code !== '' ? "\033[" . $code . 'm' : '') . $string . "\033[0m";
    }

    /**
     * @param $code int -1|0|1
     * @param $message string
     * @param int $color
     */
    protected function consolePrint($code, $message = null, $color = ConsoleHandler::FG_GREEN)
    {
        $message = $this->ansiFormat('Code: ' . $code, [$color]) . "\n" . $this->ansiFormat($message, [$color]) . "\n";
        $this->stdout($message);
    }


}