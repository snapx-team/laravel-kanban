<?php

namespace Xguard\LaravelKanban\Entities;

use JsonSerializable;

class ErpUser implements JsonSerializable
{
    private $id;
    private $firstName;
    private $lastName;

    public function __construct(int $id, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'fullName' => $this->getFullName(),
        ];
    }

    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setFirstName(string $firstName) : void
    {
        $this->firstName = $firstName;
    }

    public function getFirstName() : string
    {
        return $this->firstName;
    }

    public function setLastName(string $lastName) : void
    {
        $this->name = $lastName;
    }

    public function getLastName() : string
    {
        return $this->lastName;
    }

    public function getFullName() : string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
