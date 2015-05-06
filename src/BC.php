<?php
/******************************************************************************
 * This file is part of the Phactor PHP project. You can always find the latest
 * version of this class and project at: https://github.com/ionux/phactor
 *
 * Copyright (c) 2015 Rich Morgan, rich@bitpay.com
 *
 * The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ******************************************************************************/

namespace Phactor;

/**
 * Binary Calculator math class used by the main math class.
 *
 * @author Rich Morgan <rich@bitpay.com>
 */
final class BC
{
    /**
     * Public constructor method.
     */
    public function __construct()
    {
        bcscale(0);
    }

    /**
     * Adds two arbitrary precision numbers.
     * 
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function add($a, $b)
    {
        return (string)bcadd($this->normalize($a), $this->normalize($b));
    }

    /**
     * Multiplies two arbitrary precision numbers.
     * 
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function mul($a, $b)
    {
        return (string)bcmul($this->normalize($a), $this->normalize($b));
    }

    /**
     * Divides two arbitrary precision numbers.
     * 
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function div($a, $b)
    {
        return (string)bcdiv($this->normalize($a), $this->normalize($b));
    }

    /**
     * Subtracts two arbitrary precision numbers.
     * 
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function sub($a, $b)
    {
        return (string)bcsub($this->normalize($a), $this->normalize($b));
    }

    /**
     * Calculates the modulo 'b' of 'a'.
     * 
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function mod($a, $b)
    {
        return (string)bcmod($this->normalize($a), $this->normalize($b));
    }
    
    /**
     * Compares two arbitrary precision numbers.
     * 
     * @param  string $a  The first number.
     * @param  string $b  The second number.
     * @return string
     */
    public function comp($a, $b)
    {
        return (string)bccomp($this->normalize($a), $this->normalize($b));
    }

    /**
     * Raises $a to the power of $b.
     *
     * @param  string $a
     * @param  string $b
     * @return string
     */
    public function power($a, $b)
    {
        return (string)bcpow($this->normalize($a), $this->normalize($b));
    }

    /**
     * Binary Calculator implementation of GMP's inverse
     * modulo function, where ax = 1(mod p).
     *
     * @param  string $number  The number to inverse modulo.
     * @param  string $modulus The modulus.
     * @return string $a       The result.
     * @throws \Exception
     */
    public function inv($number, $modulus)
    {
        if (false === $this->coprime($number, $modulus)) {
            return '0';
        }

        $a = '1';
        $b = '0';
        $z = '0';
        $c = '0';

        $modulus = $this->normalize($modulus);
        $number  = $this->normalize($number);

        $mod = $modulus;
        $num = $number;

        try {
            do {
                $z = bcmod($num, $mod);
                $c = bcdiv($num, $mod);

                $num = $mod;
                $mod = $z;

                $z = bcsub($a, bcmul($b, $c));

                $a = $b;
                $b = $z;
            } while (bccomp($mod, '0') > 0);

        } catch (\Exception $e) {
            throw $e;
        }

        return (string)(bccomp($a, '0') < 0) ? bcadd($a, $modulus) : $a;
    }

    /**
     * Function to determine if two numbers are
     * co-prime according to the Euclidean algo.
     *
     * @param  string $a  First param to check.
     * @param  string $b  Second param to check.
     * @return bool       Whether the params are cp.
     * @throws \Exception
     */
    private function coprime($a, $b)
    {
        $small = 0;
        $diff  = 0;

        $a = $this->normalize($a);
        $b = $this->normalize($b);

        try {

            while (bccomp($a, '0') > 0 && bccomp($b, '0') > 0) {
                switch (bccomp($a, $b)) {
                    case 0:
                    case -1:
                        $small = $a;
                        $diff  = bcmod($b, $a);
                        break;
                    case 1:
                        $small = $b;
                        $diff  = bcmod($a, $b);
                        break;
                    default:
                        // Nothing
                        break;
                }

                $a = $small;
                $b = $diff;
            }

        } catch (\Exception $e) {
            throw $e;
        }

        return (bccomp($a, '1') == 0) ? true : false;
    }

    /**
     * BC doesn't like the '0x' hex prefix that GMP prefers.
     *
     * @param  string $a The value to normalize.
     * @return string
     */
    private function normalize($a)
    {
        return (string)(substr($a, 0, 2) == '0x') ? substr($a, 2) : $a;
    }
}
