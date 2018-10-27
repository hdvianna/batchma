<?php

namespace hdvianna\Core;

class ArgManager {

    private $description;
    private $argumentsDefinition = [];
    private $parsedOptions = [];

    const TYPE_STRING = "STRING";
    const TYPE_INT = "INT";
    const FIELD_DESCRIPTION = "DESCRIPTION";
    const FIELD_OPTIONAL = "OPTIONAL";

    public function __construct($description) {
        $this->description = $description;
    }

    public function addArgumentDefinition($argument, $description, $optional = false) {
        $this->argumentsDefinition[$argument] = [
            self::FIELD_DESCRIPTION => $description,
            self::FIELD_OPTIONAL => $optional
        ];
        return $this;
    }

    private function getOptions() {
        $options = implode("", array_map(function($item) {
                    return $item . ":";
                }, array_keys($this->argumentsDefinition)));
        return getopt($options);
    }

    public function check() {
        $this->parsedOptions = $this->getOptions();
        foreach ($this->argumentsDefinition as $key => $value) {
            if (!$value[self::FIELD_OPTIONAL] && !array_key_exists($key, $this->parsedOptions)) {
                return false;
            }
        }
        return true;
    }

    public function showDescription() {
        $dashes = str_repeat("-", strlen($this->description) + 2);
        echo $dashes . PHP_EOL;
        echo " {$this->description} " . PHP_EOL;
        echo $dashes . PHP_EOL;
        foreach ($this->argumentsDefinition as $key => $value) {
            echo "   -$key {$value[self::FIELD_DESCRIPTION]}";
            if ($value[self::FIELD_OPTIONAL]) {
                echo " (optional)";
            }
            echo "." . PHP_EOL;
        }
        return $this;
    }

    public function getArguments() {
        return $this->parsedOptions;
    }

}
