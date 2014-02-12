<?php
/*
 * This file is part of the SigmaZ\Metrix package.
 * (c) Steffen Zeidler <sigma_z@sigma-scripts.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SigmaZ\Metrix;

/**
 * Class Condition
 * @author  Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Condition
{

    /** @var string */
    private $operator;

    /** @var number */
    private $value;


    /**
     * @param number $value
     * @param string $op
     */
    public function __construct($value, $op = '=')
    {
        $this->value = $value;
        $this->operator = $op;
    }


    /**
     * @param  number $value
     * @return bool
     */
    public function validate($value)
    {
        switch ($this->operator) {
            case '=':
                return $value == $this->value;
            case '!=':
            case '<>':
                return $value != $this->value;
            case '>=':
                return $value >= $this->value;
            case '>':
                return $value > $this->value;
            case '<=':
                return $value <= $this->value;
            case '<':
                return $value < $this->value;
        }
        return false;
    }

}
