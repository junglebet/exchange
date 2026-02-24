<?php

// Plain Math PHP Functions

/*
 * Safe math sum operation
 */
if (!function_exists('math_sum')) {
    function math_sum($num1, $num2)
    {
        return bcadd($num1, $num2, math_scale());
    }
}

/*
 * Safe math sub operation
 */
if (!function_exists('math_sub')) {
    function math_sub($num1, $num2)
    {
        return bcsub($num1, $num2, math_scale());
    }
}

/*
 * Safe math multiply operation
 */
if (!function_exists('math_multiply')) {
    function math_multiply($num1, $num2)
    {
        return bcmul($num1, $num2, math_scale());
    }
}

/*
 * Safe math divide operation
 */
if (!function_exists('math_divide')) {
    function math_divide($num1, $num2, $scale = false)
    {
        if(!$scale) {
            $scale = math_scale();
        }

        return bcdiv((string)$num1, (string)$num2, $scale);
    }
}

/*
 * Safe math pow
 */
if (!function_exists('math_pow')) {
    function math_pow($num1, $num2, $pow = 0.1)
    {
        return bcpow($pow, $num1, $num2);
    }
}

/*
 * Safe math percentage
 */
if (!function_exists('math_percentage')) {
    function math_percentage($quantity, $percentage = 100)
    {
        return math_divide(math_multiply($quantity, $percentage), 100);
    }
}

/*
 * Safe math compare
 */
if (!function_exists('math_compare')) {
    function math_compare($number, $number2)
    {
        return bccomp($number, $number2, math_scale());
    }
}

/*
 * Safe math formatter
 */
if (!function_exists('math_formatter')) {
    function math_formatter($number, $digits)
    {
        if($digits > 8) $digits = 8;

        $coefficient = pow(10, $digits);
        return number_format((($number < 0) ? (-1) : 1) * floor((string)(abs($number) * $coefficient)) / $coefficient, $digits, '.', '');
    }
}

/*
 * Safe math get scale
 */
if (!function_exists('math_scale')) {
    function math_scale()
    {
        return MATH_SCALE_FULL;
    }
}

/*
 * Safe math percentage between two numbers
 */
if (!function_exists('math_percentage_between')) {
    function math_percentage_between($new, $old)
    {
        if($new == 0 && $old == 0) return 0.00;

        if($old == 0) return 100.00;

        return math_formatter((($new - $old) / ($old) * 100), 2);
    }
}

/*
 * Safe math percentage progress
 */
if (!function_exists('math_percentage_progress')) {
    function math_percentage_progress($min, $max)
    {
        if($min == 0) return 0.00;

        return math_formatter(($min / $max) * 100, 2);
    }
}


/*
 * Safe math get decimal scale
 */
if (!function_exists('math_scale_decimal')) {
    function math_scale_decimal()
    {
        return MATH_SCALE_DECIMALS;
    }
}

/*
 * Safe math get decimal scale
 */
if (!function_exists('math_decimal_validation')) {
    function math_decimal_validation($value, $decimals)
    {
        return preg_match("/^[0-9]+(\.[0-9]{1,$decimals})?$/", $value);
    }
}

/*
 * Safe math get decimal scale
 */
if (!function_exists('math_decimal_scale_count')) {
    function math_decimal_scale_count($value)
    {
        return pow(10, strlen(substr(strrchr((double)$value, "."), 1)));
    }
}
