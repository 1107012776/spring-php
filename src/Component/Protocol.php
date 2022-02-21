<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Component;


class Protocol
{
    public static function pack(string $data): string
    {
        return pack('N', strlen($data)) . $data;
    }

    public static function packDataLength(string $head): int
    {
        return unpack('N', $head)[1];
    }

    public static function unpack(string $data): string
    {
        return substr($data, 4);
    }
}