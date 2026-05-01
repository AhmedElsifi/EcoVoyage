<?php
class Validator
{
    public static function noHtml($value)
    {
        return strip_tags((string) $value) === (string) $value;
    }
    public static function required($value)
    {
        return trim((string) $value) !== '';
    }

    public static function email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function minLength($value, $min)
    {
        return mb_strlen((string) $value) >= $min;
    }

    public static function maxLength($value, $max)
    {
        return mb_strlen((string) $value) <= $max;
    }

    public static function numeric($value)
    {
        return is_numeric($value);
    }

    public static function alphaSpaces($value)
    {
        return preg_match('/^[\pL\s]+$/u', (string) $value);
    }

    public static function match($value1, $value2)
    {
        return $value1 === $value2;
    }

    public static function phone($value)
    {
        return preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $value);
    }

    public static function fileRequired($fieldName)
    {
        return isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK;
    }

    public static function fileType($fieldName, array $allowedExtensions)
    {
        if (!self::fileRequired($fieldName))
            return false;
        $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
        return in_array($ext, $allowedExtensions);
    }

    public static function fileMaxSize($fieldName, $maxKB)
    {
        if (!self::fileRequired($fieldName))
            return false;
        return ($_FILES[$fieldName]['size'] / 1024) <= $maxKB;
    }

    public static function date($value)
    {
        return $value === '' || strtotime($value) !== false;
    }

    public static function positiveNumber($value)
    {
        return self::numeric($value) && (float) $value > 0;
    }

    public static function integer($value)
    {
        if (is_int($value))
            return true;
        if (is_string($value) && ctype_digit($value))
            return true;
        return false;
    }

    public static function minValue($value, $min, $inclusive = true)
    {
        if (!self::numeric($value))
            return false;
        $val = (float) $value;
        return $inclusive ? $val >= $min : $val > $min;
    }

    public static function maxValue($value, $max, $inclusive = true)
    {
        if (!self::numeric($value))
            return false;
        $val = (float) $value;
        return $inclusive ? $val <= $max : $val < $max;
    }

    public static function regex($value, $pattern)
    {
        return preg_match($pattern, (string) $value) === 1;
    }

    public static function inArray($value, array $array)
    {
        return in_array($value, $array, true);
    }
}