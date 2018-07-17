<?php
namespace App\CMS\Constants;

abstract class EnumType {
    /*
     * @var $val
     */
    private $val;

    /**
     * @return mixed
     */
    public abstract function getFields();

    /**
     * EnumType constructor.
     * @param $str
     * @throws \Exception
     */
    final function __construct( $str ) {
        if ( ! in_array( $str,  $this->getFields() ) ) {
            throw new \Exception("unknown type value: $str");
        }
        $this->val = $str;
    }

    /**
     * @param $func
     * @param $args
     * @return static
     */
    public static function __callStatic( $func, $args ) {
        return new static( $func );
    }

    /**
     * @return mixed
     */
    public function value() {
        return $this->val;
    }

    /**
     * @return mixed
     */
    public function __toString() {
        return $this->value();
    }
}

