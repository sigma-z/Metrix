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
 * Class pDependSummary
 * @author  Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class pDependSummary
{

    /**
     * data array loaded from summary.xml
     * @var array
     */
    private $data = array();


    /**
     * @param $file
     */
    public function loadFromFile($file)
    {
        $xml = simplexml_load_file($file);
        $json = json_encode($xml);
        $this->data = json_decode($json, true);
    }


    /**
     * @param CodeMetric $metric
     * @return array
     */
    public function fetchMetric(CodeMetric $metric)
    {
        $path = $metric->getPathParts();
        $result = $this->getDataByPath($this->data, $path, $metric);
        $metric->sortResult($result);
        $metric->limitResult($result);
        return $result;
    }


    /**
     * @param array      $data
     * @param array      $path
     * @param CodeMetric $metric
     * @param array      $additionalData
     * @return array
     */
    private function getDataByPath(
        array $data, array $path, CodeMetric $metric, array $additionalData = array()
    ) {
        $subKey = '';
        $isCollection = false;
        $key = array_shift($path);
        if (preg_match('/\[(.*)?]/', $key, $matches) > 0) {
            $subKey = $matches[1];
            $isCollection = true;
            $key = substr($key, 0, - (strlen($subKey) + 2));
        }

        if (!isset($data[$key])) {
            return array();
        }

        $result = array();
        if ($path && isset($data[$key])) {
            $items = $isCollection
                ? $this->getItemsFromCollection($data[$key], $subKey)
                : array($data[$key]);

            if (isset($items['@attributes'])) {
                $items = array($items);
            }

            foreach ($items as $item) {
                if ($subKey) {
                    $additionalData = array();
                }
                else if ($key !== '@attributes' && isset($item['@attributes']['name'])) {
                    $additionalData[$key] = $item['@attributes']['name'];
                }
                $resultItem = $this->getDataByPath($item, $path, $metric, $additionalData);
                if ($resultItem) {
                    $result = array_merge($result, $resultItem);
                }
            }
        }
        else if ($this->validateMetricConditions($metric->getConditions(), $data[$key])) {
            $itemData = $additionalData;
            $itemData[$key] = $data[$key];
            $result[] = $itemData;
        }
        return $result;
    }


    /**
     * Validates metric conditions
     *
     * @param  Condition[] $conditions
     * @param  number      $value
     * @return bool
     */
    private function validateMetricConditions(array $conditions, $value)
    {
        if (!$conditions) {
            return true;
        }
        foreach ($conditions as $condition) {
            if (!$condition->validate($value)) {
                return false;
            }
        }
        return true;
    }


    /**
     * @param array  $items
     * @param string $subKey
     * @return array
     */
    private function getItemsFromCollection(array $items, $subKey)
    {
        if ($subKey) {
            $subKeyParts = explode('=', $subKey, 2);
            if (count($subKeyParts) > 1) {
                if (isset($items[$subKeyParts[0]]) && $items[$subKeyParts[0]] === $subKeyParts[1]) {
                    return array($items);
                }
                return array();
            }
            return isset($items[$subKey]) ? $items[$subKey] : array();
        }
        return $items;
    }

}
