<?php
namespace Koklu\Model\Similarity;

interface SimilarityInterface
{
    /**
     * @param array $a
     * @param array $b
     * @return float
     */
    public function getSimilarity(array $a, array $b);
}