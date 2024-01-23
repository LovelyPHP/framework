<?php
// +----------------------------------------------------------------------
// | LovelyPHP [ DO AND BECOME LOVELY ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024 https://lovelyphp.mojy.xyz All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MoeCinnamo <abcd2890000456@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace lovely;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use lovely\contract\Arrayable;
use lovely\contract\Jsonable;
use lovely\helper\Arr;
use Traversable;

/**
 * 数据集管理类
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @implements ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Arrayable, Jsonable
{
    /**
     * 数据集数据
     * @var array
     */
    protected $items = [];

    public function __construct($items = [])
    {
        $this->items = $this->convertToArray($items);
    }

    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * 是否为空
     * @access public
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }

    public function all()
    {
        return $this->items;
    }

    /**
     * 合并数组
     *
     * @access public
     * @param mixed $items 数据
     * @return static
     */
    public function merge($items)
    {
        return new static(array_merge($this->items, $this->convertToArray($items)));
    }

    /**
     * 按指定键整理数据
     *
     * @access public
     * @param mixed  $items    数据
     * @param string $indexKey 键名
     * @return array
     */
    public function dictionary($items = null, &$indexKey = null)
    {
        if ($items instanceof self) {
            $items = $items->all();
        }

        $items = is_null($items) ? $this->items : $items;

        if ($items && empty($indexKey)) {
            $indexKey = is_array($items[0]) ? 'id' : $items[0]->getPk();
        }

        if (isset($indexKey) && is_string($indexKey)) {
            return array_column($items, null, $indexKey);
        }

        return $items;
    }

    /**
     * Get the primary key for the items.
     *
     * @return string
     */
    public function getPk()
    {
        // Implement this method according to your actual needs
        // For example, return 'id' if the primary key is 'id'
        return 'id';
    }

    /**
     * 比较数组，返回差集
     *
     * @access public
     * @param mixed   $items    数据
     * @param string|null $indexKey 指定比较的键名
     * @return static
     */
    public function diff($items, $indexKey = null)
    {
        if ($this->isEmpty() || is_scalar($this->items[0])) {
            return new static(array_diff($this->items, $this->convertToArray($items)));
        }

        $diff = [];
        $dictionary = $this->dictionary($items, $indexKey);

        if (is_string($indexKey)) {
            foreach ($this->items as $item) {
                if (!isset($dictionary[$item[$indexKey]])) {
                    $diff[] = $item;
                }
            }
        }

        return new static($diff);
    }

    /**
     * 比较数组，返回交集
     *
     * @access public
     * @param mixed   $items    数据
     * @param string|null $indexKey 指定比较的键名
     * @return static
     */
    public function intersect($items, $indexKey = null)
    {
        if ($this->isEmpty() || is_scalar($this->items[0])) {
            return new static(array_intersect($this->items, $this->convertToArray($items)));
        }

        $intersect = [];
        $dictionary = $this->dictionary($items, $indexKey);

        if (is_string($indexKey)) {
            foreach ($this->items as $item) {
                if (isset($dictionary[$item[$indexKey]])) {
                    $intersect[] = $item;
                }
            }
        }

        return new static($intersect);
    }

    /**
     * 交换数组中的键和值
     *
     * @access public
     * @return static
     */
    public function flip()
    {
        return new static(array_flip($this->items));
    }

    /**
     * 返回数组中所有的键名
     *
     * @access public
     * @return static
     */
    public function keys()
    {
        return new static(array_keys($this->items));
    }

    /**
     * 返回数组中所有的值组成的新 Collection 实例
     * @access public
     * @return static
     */
    public function values()
    {
        return new static(array_values($this->items));
    }

    /**
     * 删除数组的最后一个元素（出栈）
     *
     * @access public
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * 通过使用用户自定义函数，以字符串返回数组
     *
     * @access public
     * @param callable $callback 调用方法
     * @param mixed    $initial
     * @return mixed
     */
    public function reduce($callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * 以相反的顺序返回数组。
     *
     * @access public
     * @return static
     */
    public function reverse()
    {
        return new static(array_reverse($this->items));
    }

    /**
     * 删除数组中首个元素，并返回被删除元素的值
     *
     * @access public
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * 在数组结尾插入一个元素
     * @access public
     * @param mixed   $value 元素
     * @param string|null $key   KEY
     * @return $this
     */
    public function push($value, $key = null)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }

        return $this;
    }

    /**
     * 把一个数组分割为新的数组块.
     *
     * @access public
     * @param int  $size 块大小
     * @param bool $preserveKeys
     * @return static
     */
    public function chunk($size, $preserveKeys = false)
    {
        $chunks = [];

        foreach (array_chunk($this->items, $size, $preserveKeys) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return new static($chunks);
    }

    /**
     * 在数组开头插入一个元素
     * @access public
     * @param mixed  $value 元素
     * @param string|null $key   KEY
     * @return $this
     */
    public function unshift($value, $key = null)
    {
        if (is_null($key)) {
            array_unshift($this->items, $value);
        } else {
            $this->items = [$key => $value] + $this->items;
        }

        return $this;
    }

    /**
     * 给每个元素执行个回调
     *
     * @access public
     * @param callable $callback 回调
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            $result = $callback($item, $key);

            if (false === $result) {
                break;
            } elseif (!is_object($item)) {
                $this->items[$key] = $result;
            }
        }

        return $this;
    }

    /**
     * 用回调函数处理数组中的元素
     * @access public
     * @param callable|null $callback 回调
     * @return static
     */
    public function map(callable $callback)
    {
        $mappedItems = array_map($callback, $this->items);

        return new static($mappedItems);
    }

    /**
     * 用回调函数过滤数组中的元素
     * @access public
     * @param callable|null $callback 回调
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            $filteredItems = array_filter($this->items, $callback);
        } else {
            $filteredItems = array_filter($this->items);
        }

        return new static($filteredItems);
    }

    /**
     * 根据字段条件过滤数组中的元素
     * @access public
     * @param string $field    字段名
     * @param mixed  $operator 操作符
     * @param mixed  $value    数据
     * @return static
     */
    public function where($field, $operator, $value = null)
    {
        // ...

        return $this->filter(function ($data) use ($field, $operator, $value) {
            // ...
        });
    }

    /**
     * LIKE过滤
     * @access public
     * @param string $field 字段名
     * @param string $value 数据
     * @return static
     */
    public function whereLike($field, $value)
    {
        return $this->where($field, 'like', $value);
    }

    /**
     * NOT LIKE过滤
     * @access public
     * @param string $field 字段名
     * @param string $value 数据
     * @return static
     */
    public function whereNotLike($field, $value)
    {
        return $this->where($field, 'not like', $value);
    }

    /**
     * IN过滤
     * @access public
     * @param string $field 字段名
     * @param array  $value 数据
     * @return static
     */
    public function whereIn($field, $value)
    {
        return $this->where($field, 'in', $value);
    }

    /**
     * NOT IN过滤
     * @access public
     * @param string $field 字段名
     * @param array  $value 数据
     * @return static
     */
    public function whereNotIn($field, $value)
    {
        return $this->where($field, 'not in', $value);
    }

    /**
     * BETWEEN 过滤
     * @access public
     * @param string $field 字段名
     * @param mixed  $value 数据
     * @return static
     */
    public function whereBetween($field, $value)
    {
        return $this->where($field, 'between', $value);
    }

    /**
     * NOT BETWEEN 过滤
     * @access public
     * @param string $field 字段名
     * @param mixed  $value 数据
     * @return static
     */
    public function whereNotBetween($field, $value)
    {
        return $this->where($field, 'not between', $value);
    }

    /**
     * 返回数据中指定的一列
     * @access public
     * @param string|null $columnKey 键名
     * @param string|null $indexKey  作为索引值的列
     * @return array
     */
    public function column($columnKey = null, $indexKey = null)
    {
        return array_column($this->items, $columnKey, $indexKey);
    }

    /**
     * 对数组排序
     *
     * @access public
     * @param callable|null $callback 回调
     * @return static
     */
    public function sort($callback = null)
    {
        $items = $this->items;

        $callback = $callback ?: function ($a, $b) {
            return $a == $b ? 0 : (($a < $b) ? -1 : 1);
        };

        uasort($items, $callback);

        return new static($items);
    }

    /**
     * 指定字段排序
     * @access public
     * @param string $field 排序字段
     * @param string $order 排序
     * @return $this
     */
    public function order($field, $order = 'asc')
    {
        return $this->sort(function ($a, $b) use ($field, $order) {
            $fieldA = isset($a[$field]) ? $a[$field] : null;
            $fieldB = isset($b[$field]) ? $b[$field] : null;

            return 'desc' == strtolower($order) ? intval($fieldB > $fieldA) : intval($fieldA > $fieldB);
        });
    }

    /**
     * 将数组打乱
     *
     * @access public
     * @return static
     */
    public function shuffle()
    {
        $items = $this->items;

        shuffle($items);

        return new static($items);
    }

    /**
     * 获取第一个单元数据
     *
     * @access public
     * @param callable|null $callback
     * @param null          $default
     * @return mixed
     */
    public function first($callback = null, $default = null)
    {
        return Arr::first($this->items, $callback, $default);
    }

    /**
     * 获取最后一个单元数据
     *
     * @access public
     * @param callable|null $callback
     * @param null          $default
     * @return mixed
     */
    public function last($callback = null, $default = null)
    {
        return Arr::last($this->items, $callback, $default);
    }

    /**
     * 截取数组
     *
     * @access public
     * @param int  $offset       起始位置
     * @param ?int $length       截取长度
     * @param bool $preserveKeys preserveKeys
     * @return static
     */
    public function slice($offset, $length = null, $preserveKeys = false)
    {
        return new static(array_slice($this->items, $offset, $length, $preserveKeys));
    }

    // ArrayAccess
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    //Countable
    public function count()
    {
        return count($this->items);
    }

    //IteratorAggregate
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    //JsonSerializable
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * 转换当前数据集为JSON字符串
     * @access public
     * @param integer $options json参数
     * @return string
     */
    public function toJson(int $options = \lovely\contract\Jsonable::JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * 转换成数组
     *
     * @access public
     * @param mixed $items 数据
     * @return array
     */
    protected function convertToArray($items)
    {
        if ($items instanceof self) {
            return $items->all();
        }

        return (array) $items;
    }
}
?>