<?php

namespace Xguard\LaravelKanban\Entities;

use JsonSerializable;

class ErpUser implements JsonSerializable
{
    private int $id;
    private string $firstName;
    private string $lastName;

    public function __construct(int $id, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->getFullName(),
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
