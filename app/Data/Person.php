<?php 

namespace App\Data;

class Person
{
    public function __construct(
        public string $name
    ){}

    public function sayHello(): string
    {
        return "Hello " . $this->name;
    }
}