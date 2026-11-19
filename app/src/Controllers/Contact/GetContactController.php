<?php

namespace App\Controllers\Contact;

use App\Contact\ContactStorage;
use App\lib\Controllers\AbstractController;
use App\lib\Http\Request;
use App\lib\Http\Response;

class GetContactController extends AbstractController
{
    use JsonTrait;

    public function process(Request $request): Response
    {
        $storage = new ContactStorage();

        return $this->json(200, $storage->findAll());
    }
}
