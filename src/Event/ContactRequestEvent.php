<?php 

namespace App\Event;

use App\DTO\ContactDTO;

class ContactRequestEvent
{
    function __construct(
        private readonly ContactDTO $data
    )
    {
    }

    /**
     * Get the value of data
     */ 
    public function getData() : ?ContactDTO
    {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @return  self
     */ 
    public function setData(?ContactDTO $data) : static
    {
        $this->data = $data;

        return $this;
    }
}