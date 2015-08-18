<?php
namespace Koklu\Model\Similarity;

class TanimotoCoefficient implements SimilarityInterface
{
    /**
     * @param array $a
     * @param array $b
     * @return float
     */
    public function getSimilarity(array $a, array $b)
    {
        $a = $this->_curate($a);
        $b = $this->_curate($b);
        $ab = array_intersect($a, $b);
        return round(count($ab) / (count($a) + count($b) - count($ab)), 5);
    }

    /**
     * Split comma-separated values to separate values, prefix values with attribute key
     *
     * @param array $data
     * @return array
     */
    protected function _curate(array $data)
    {
        $curated = [];
        foreach ($data as $key => $values) {
            foreach (explode(',', $values) as $value) {
                $curated[] = $key . $value;
            }
        }

        return $curated;
    }
}