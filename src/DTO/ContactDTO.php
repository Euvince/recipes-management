<?php 

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ContactDTO
{
    #[Assert\NotBlank()]
    #[Assert\Length(min: 5)]
    private ?string $name = null;

    #[Assert\NotBlank()]
    #[Assert\Email()]
    #[Assert\Length(min: 5)]
    private ?string $email = null;

    #[Assert\NotBlank()]
    #[Assert\Length(min: 10, max: 100)]
    private ?string $message = null;

    private ?string $service = null;

    /**
     * Get the value of name
     */ 
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName(?string $name) : ?static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail(?string $email) : ?static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of message
     */ 
    public function getMessage() : ?string
    {
        return $this->message;
    }

    /**
     * Set the value of message
     *
     * @return  self
     */ 
    public function setMessage(?string $message) : ?static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get the value of service
     */ 
    public function getService() : ?string
    {
        return $this->service;
    }

    /**
     * Set the value of service
     *
     * @return  self
     */ 
    public function setService(?string $service) : ?static
    {
        $this->service = $service;

        return $this;
    }
}