<?php

namespace ActiveRecordUtils\Composers;

/**
 * Utility for composing "conditions" blocks for php-activerecord queries.
 */
class Conditions
{
    /**
     * Raw SQL query text, with "?" tokens for parameter injection.
     *
     * @var string
     */
    protected $queryText;

    /**
     * Sequential List of parameters to be injected.
     *
     * @var array
     */
    protected $params;

    /**
     * Flag indicating whether there is an open group of parentheses in the query text being generated.
     *
     * @var bool
     */
    protected $openGroup;

    /**
     * Initialize a blank "conditions" composer.
     */
    public function __construct()
    {
        $this->queryText = "";
        $this->params = [];
        $this->openGroup = false;
    }

    /**
     * Initialize a blank "conditions" composer.
     *
     * @return Conditions
     */
    public static function make(): Conditions
    {
        return new Conditions();
    }

    /**
     * Returns an array containing the "conditions" key with the result value.
     *
     * @return array
     */
    public function entry(): array
    {
        return [
            "conditions" => $this->value()
        ];
    }

    /**
     * Returns the "conditions" value array, or NULL if the expression is empty.
     *
     * @return array|null
     */
    public function value(): ?array
    {
        $statement = $this->queryText;

        if (empty($statement)) {
            return null;
        }

        if ($this->openGroup) {
            $statement .= ")";
        }

        $result = [$statement];
        foreach ($this->params as $param)
            $result[] = $param;
        return $result;
    }

    /**
     * @param string|null $prefix The prefix, usually "AND", "OR", or NULL.
     * @param bool $inline If true, this is an inline AND/OR, and should not produce a new parentheses block.
     * @param string $expression Raw SQL query text, with "?" tokens for parameter injection.
     * @param mixed ...$params Sequential List of parameters to be injected.
     * @return $this|Conditions
     */
    protected function __where(string $prefix, bool $inline, string $expression, ...$params): Conditions
    {
        // ----- [Validation] ------------------------------------------------------------------------------------------

        if (empty($expression)) {
            throw new \InvalidArgumentException("Cannot add an empty SQL expression.");
        }

        $paramCount = count($params);
        $hintedParamCount = substr_count($expression, "?");

        if ($hintedParamCount !== $paramCount) {
            throw new \InvalidArgumentException(
                "Invalid expression ({$expression}): got {$paramCount} params, expected {$hintedParamCount}.");
        }

        // ----- [Parentheses grouping] --------------------------------------------------------------------------------

        $startNewGroup = !$inline || !$this->openGroup;

        if ($startNewGroup) {
            if ($this->openGroup) {
                // Close the group and start a new one with the prefix, e.g. "[..]) AND ([..]"
                $this->queryText .= ") {$prefix} (";
            } else {
                // First group in the query, no prefix regardless of what call the user made
                // TODO Raise a warning of some sort if the prefix is "OR"? Probably unintentional.
                $this->queryText .= "(";
            }

            $this->openGroup = true;
        } else {
            // We are applying another condition within the same block, e.g. "[..] AND [..]"
            $this->queryText .= " {$prefix} ";
        }

        // ----- [Params] ----------------------------------------------------------------------------------------------

        $this->queryText .= $expression;

        foreach ($params as $p)
            $this->params[] = $p;

        return $this;
    }

    /**
     * Alias for andWhere().
     * This is typically used for the first part of the conditions for readability.
     *
     * @param string $expression Raw SQL query text, with "?" tokens for parameter injection.
     * @param mixed ...$params Sequential List of parameters to be injected.
     * @return $this|Conditions
     */
    public function where(string $expression, ...$params): Conditions
    {
        return $this->andWhere($expression, ...$params);
    }

    /**
     * Adds an "OR" condition to the current parentheses block.
     *
     * @param string $expression Raw SQL query text, with "?" tokens for parameter injection.
     * @param mixed ...$params Sequential List of parameters to be injected.
     * @return $this|Conditions
     */
    public function or(string $expression, ...$params): Conditions
    {
        return $this->__where("OR", true, $expression, ...$params);
    }

    /**
     * Adds an "AND" condition to the current parentheses block.
     *
     * @param string $expression Raw SQL query text, with "?" tokens for parameter injection.
     * @param mixed ...$params Sequential List of parameters to be injected.
     * @return $this|Conditions
     */
    public function and(string $expression, ...$params): Conditions
    {
        return $this->__where("AND", true, $expression, ...$params);
    }

    /**
     * Adds a new "OR" condition in a separate parentheses block.
     *
     * @param string $expression Raw SQL query text, with "?" tokens for parameter injection.
     * @param mixed ...$params Sequential List of parameters to be injected.
     * @return $this|Conditions
     */
    public function orWhere(string $expression, ...$params): Conditions
    {
        return $this->__where("OR", false, $expression, ...$params);
    }

    /**
     * Adds a new "AND" condition in a separate parentheses block.
     *
     * @param string $expression Raw SQL query text, with "?" tokens for parameter injection.
     * @param mixed ...$params Sequential List of parameters to be injected.
     * @return $this|Conditions
     */
    public function andWhere(string $expression, ...$params): Conditions
    {
        return $this->__where("AND", false, $expression, ...$params);
    }
}