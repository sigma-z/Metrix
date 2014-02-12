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
 * Class CodeMetric
 * @author  Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class CodeMetric
{

    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';

    /** @var string */
    private $path = '';

    /** @var Condition[] */
    private $conditions = array();

    /** @var string */
    private $sortField;

    /** @var string */
    private $sortDirection;

    /** @var int */
    private $resultLimit;


    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }


    /**
     * @param Condition $condition
     * @return $this
     */
    public function addCondition(Condition $condition)
    {
        $this->conditions[] = $condition;
        return $this;
    }


    /**
     * @return Condition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }


    /**
     * @return array
     */
    public function getPathParts()
    {
        return explode('.', $this->path);
    }


    /**
     * Sets sort info
     *
     * @param  string $field
     * @param  string $direction
     * @return $this
     */
    public function setSortInfo($field, $direction = self::SORT_ASC)
    {
        $this->sortField = $field;
        $this->sortDirection = strtolower(trim($direction));
        return $this;
    }


    /**
     * @return string
     */
    public function getSortField()
    {
        return $this->sortField;
    }


    /**
     * @return bool
     */
    public function isSortAscending()
    {
        return $this->sortDirection === self::SORT_ASC;
    }


    /**
     * Sorts the result
     *
     * @param array $result
     */
    public function sortResult(array &$result)
    {
        $sortField = $this->sortField;
        if ($sortField) {
            $sortDirection = $this->isSortAscending() ? 1 : -1;
            $cmpCallback = function ($a, $b) use ($sortField, $sortDirection) {
                if ($a[$sortField] > $b[$sortField]) {
                    return 1 * $sortDirection;
                }
                else if ($a[$sortField] < $b[$sortField]) {
                    return -1 * $sortDirection;
                }
                return 0;
            };
            usort($result, $cmpCallback);
        }
    }


    /**
     * Sets the result limit
     *
     * @param  int $limit
     * @return $this
     */
    public function setResultLimit($limit)
    {
        $this->resultLimit = $limit;
        return $this;
    }


    /**
     * Limits the result
     *
     * @param array $result
     */
    public function limitResult(array &$result)
    {
        if ($this->resultLimit) {
            $result = array_slice($result, 0, $this->resultLimit);
        }
    }

}
