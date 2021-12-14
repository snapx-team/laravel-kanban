<?php

namespace Xguard\LaravelKanban\Entities;

use JsonSerializable;

class ErpContract implements JsonSerializable
{
    private int $id;
    private string $contractIdentifier;

    public function __construct(int $id, string $contractIdentifier)
    {
        $this->id = $id;
        $this->contractIdentifier = $contractIdentifier;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'contract_identifier' => $this->contractIdentifier
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

    public function setContractIdentifier(string $contractIdentifier) : void
    {
        $this->contractIdentifier = $contractIdentifier;
    }

    public function getContractIdentifier() : string
    {
        return $this->contractIdentifier;
    }
}
