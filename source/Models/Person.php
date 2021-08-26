<?php

namespace CD\Models;

trait Person
{
    protected int $UserID;
    protected string $UserFirstName = '';
    protected string $UserLastName = '';
    protected string $UserGender = '';
    protected string $UserEmail = '';
    protected string $UserAddress = '';
    protected string $UserDateAdded = '';
    protected string $UserDateLastModified = '';
    protected string $UserPhone = '';
    protected ?string $UserMiddleName = null;
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

    public function getUserAddress(): string
    {
        return $this->UserAddress;
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

    public function getAvatarInitials(): string
    {
        // return strtoupper(join('', array_map(fn($m) => $m[0], explode(' ', $this->getUserFirstName()))) . $this->getUserLastName()[0]);
        return strtoupper($this->getUserFirstName()[0] . $this->getUserLastName()[0]);
    }
}