<?php

namespace CD\Models;

trait Person
{
    protected int $UserID;
    protected string $UserFirstName = '';
    protected string $UserLastName = '';
    protected string $UserGender = '';
    protected string $UserEmail = '';
    protected string $UserAddress1 = '';
    protected string $UserDateAdded = '';
    protected string $UserDateLastModified = '';
    protected string $UserPhone = '';
    protected ?string $UserMiddleName = null;
    protected ?string $UserAddress2 = null;
    protected ?string $UserPhoto = null;

    public function getUserID(): int
    {
        return $this->UserID;
    }

    public function getUserDateLastModified(): string
    {
        return $this->UserDateLastModified;
    }

    public function getUserDateAdded(): string
    {
        return $this->UserDateAdded;
    }

    public function getUserPhoto(): ?string
    {
        return $this->UserPhoto;
    }

    public function getUserAddress2(): ?string
    {
        return $this->UserAddress2;
    }

    public function getUserAddress1(): string
    {
        return $this->UserAddress1;
    }

    public function getUserGender(): string
    {
        return $this->UserGender;
    }

    public function getUserEmail(): string
    {
        return $this->UserEmail;
    }

    public function getUserPhone(): string
    {
        return $this->UserPhone;
    }

    public function getUserFullName(): string
    {
        return $this->getUserFirstName() . ' ' . $this->getUserMiddleName() . ' ' . $this->getUserLastName();
    }

    public function getUserName(): string
    {
        return $this->getUserFirstName() . ' ' . $this->getUserLastName();
    }

    public function getUserFirstName(): string
    {
        return $this->UserFirstName;
    }

    public function getUSerLastName(): string
    {
        return $this->UserLastName;
    }

    public function getUserMiddleName(): ?string
    {
        return $this->UserMiddleName;
    }
}