<?php

namespace lovely\contract;

if (!defined('ARRAY_FILTER_USE_BOTH')) {
    define('ARRAY_FILTER_USE_BOTH', 1);
}

interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}