<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Logging;

use Monolog\Formatter\LineFormatter as BaseLineFormatter;
use Monolog\LogRecord;

class LineFormatter extends BaseLineFormatter
{
    public function __construct()
    {
        $format = "[%datetime%] %channel%.%level_name%: %message%\n";
        // dateFormat=null, allowInlineLineBreaks=true, ignoreEmptyContextAndExtra=true
        parent::__construct($format, null, true, true);
    }

    public function format(LogRecord $record): string
    {
        // 去掉 context 默认 JSON 输出
        $output = parent::format($record);

        if (empty($record['context'])) {
            return $output;
        }

        $contextLines = [];

        foreach ($record['context'] as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_scalar($value)) {
                $contextLines[] = "$key: \n" . var_export($value, true) . "\n";
            } else {
                $contextLines[] = "$key: \n" . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";  //  | JSON_PRETTY_PRINT
            }
        }

        if (! empty($contextLines)) {
            $output .= "\n" . implode("\n", $contextLines);
        }

        return $output . "\n";
    }
}
